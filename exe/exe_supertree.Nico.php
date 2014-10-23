<?php
$nwk = '';

foreach($trees as $key => $value) {
        $nwk .= $value->getNewick()."\n";
}

if ($nwk != '') {
    require_once 'classes/Request/Abstract.php';
    require_once 'classes/Request/Post.php';
    require_once 'classes/Request/Get.php';
    require_once 'classes/Request/Conversation.php';

    $_POST['correction'] = str_replace(' ', '', $_POST['correction']);
    $_POST['correction'] = str_replace(',', '.', $_POST['correction']);
    $_POST['correction'] = (float) $_POST['correction'];

    if ((!isset($_POST['bootstrap']) || is_int((int)$_POST['bootstrap']))
     && (!isset($_POST['correction']) || is_float($_POST['correction'])) ) {
        $datafile = $res . 'physic.nwk';
        $email = WEBMASTER;
        $bootstrap = isset($_POST['bootstrap']) ? $_POST['bootstrap']:'0';
        $correction = isset($_POST['correction']) ? $_POST['correction']:'0.9';
        file_put_contents($datafile, $nwk);

        $conversation = new Core_Request_Conversation();
        $pr =$conversation->newPost( ROOT.'physic_ist/execution.php' );

        $pr->setFile( 'userfile', $datafile , 'text/plain' );
        $pr->setData( 'Analysis', 'compphy-supertree' );
        $pr->setData( 'Email', $email );
        $pr->setData( 'Email2', $email );
        $pr->setData( 'bootstrap', $bootstrap );
        $pr->setData( 'correction', $correction );

        $retour = $pr->send();

        if(preg_match('#\?path\=(\d{8}\-\d{6}_[^\/]{4})\/cmd\.txt#', $retour, $matches)) {
            $url = ROOT . 'compphy/exe/';
            $path = $matches[1];
            $mypath = $res;
            
            $result = file_get_contents(ROOT."job_status/index.php?path=$path/cmd.txt");
            $resultvar = false;
            
            while ($resultvar != true) {
                if (preg_match("/done/i", $result)) {
                    $resultvar = true;
                    exec ("cp ".EXECPATH.$path."/* $mypath");
                    exec ("cp ".EXECPATH.$path."/*.tds $mypath");
                    exec ("cp ".EXECPATH.$path."/*.tlf $mypath");
                    
                    $dirname = $mypath;
                    $dir = opendir($dirname); 

                    while($file = readdir($dir)) {
                        if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
                            //echo "$file";
                            if(preg_match("/supertree.*\.nwk/i", $file)) {
                                $basename = basename($file, ".nwk");
                                $newick = "";
                                $script = "";
                                $annotation = "";
                                
                                $newick = file_get_contents($dirname.$file);
                                if(is_file($dirname.$basename.".tds"))
                                    $script = file_get_contents($dirname.$basename.".tds");
                                else
                                    $script = "t -symbol {02 10 10 10 10 blue 01} -interleaf 20\nesn -what x: -box 0 -fg blue -font {arial 5 normal}";
                                if(is_file($dirname.$basename.".tlf"))
                                    $annotation = file_get_contents($dirname.$basename.".tlf");
                                
                                $donnees = array('db' => $db,
                                                 'nom' => "PhySIC_IST",
                                                 'typet' => "2",
                                                 'newick' => $newick,
                                                 'script' => $script,
                                                 'annotation' => $annotation,
                                                 'image' => '',
                                                 'miniature' => '',
                                                 'actif' => 1,
                                                 'proj_id' => $projet->getId());
                                $arbre = new Arbre($donnees);
                                $arbre->add();
                                $arbre->create($res, true);
                                unlink($dirname.$file);
                                if(is_file($dirname.$basename.".tds"))
                                    unlink($dirname.$basename.".tds");
                                if(is_file($dirname.$basename.".tlf"))
                                    unlink($dirname.$basename.".tlf");
                            }
                            elseif(preg_match("/^(\d+).*\.nwk/i", $file, $m)) {
                                $basename = basename($file, ".nwk");
                                $newick = "";
                                $script = "";
                                $annotation = "";
                                
                                $newick = file_get_contents($dirname.$file);
                                if(is_file($dirname.$basename.".tds"))
                                    $script = file_get_contents($dirname.$basename.".tds");
                                if(is_file($dirname.$basename.".tlf"))
                                    $annotation = file_get_contents($dirname.$basename.".tlf");
                                
                                $script = preg_replace("/\-font \{Arial 10 italic\}/i", "", $script);
                                
                                if(!preg_match("/\-symbol \{02 10 10 10 10 green 01\}/i", $script)) {
                                    $donnees = array('db' => $db,
                                                     'nom' => $trees[$m[1]-1]->getNom()."_S",
                                                     'typet' => "1",
                                                     'newick' => $newick,
                                                     'script' => $script,
                                                     'annotation' => $annotation,
                                                     'image' => '',
                                                     'miniature' => '',
                                                     'actif' => 1,
                                                     'proj_id' => $projet->getId());
                                    $arbre = new Arbre($donnees);
                                    $arbre->add();
                                    $arbre->create($res, true);
                                }
                                else {
                                    $arbre = $trees[$m[1]-1];
                                    $script = $arbre->getScript();
                                    if(!preg_match("/\-symbol \{02 10 10 10 10 green 01\}/i", $script))
                                        $script = preg_replace("/^t /", "t -symbol {02 10 10 10 10 green 01} ", $script);
                                    $arbre->setScript($script);
                                    $arbre->update();
                                    $arbre->create($res);
                                }
                                unlink($dirname.$file);
                                if(is_file($dirname.$basename.".tds"))
                                    unlink($dirname.$basename.".tds");
                                if(is_file($dirname.$basename.".tlf"))
                                    unlink($dirname.$basename.".tlf");
                            }
                        }
                    }
                    closedir($dir);
                    // Clean temporary files
                    exec (BINPATH ."cleantmpfiles.sh $res .nwk");
                    exec (BINPATH ."cleantmpfiles.sh $res .tds");
                    exec (BINPATH ."cleantmpfiles.sh $res .tlf");
//                    exec("rm $res*.nwk $res*.tds $res*.tlf");
                }
                else {
                    $result = file_get_contents(ROOT."job_status/index.php?path=$path/cmd.txt");
                }
            }
        }
    }
    else {
        if (isset($_POST['bootstrap']) && !is_int((int) $_POST['bootstrap']))
            $errors .= 'The bootstrap threshold is not an integer.<br>';
        if (isset($_POST['correction']) && !is_numeric($_POST['correction']))
            $errors .= 'The correction threshold is not an integer or a floating.';
    }
}
?>

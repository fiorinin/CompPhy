<?php
    $nwk = '';
    
    foreach($trees as $key => $value) {
        $nwk .= $value->getNewick();
    }

    $tempfile = $res . 'restricted_temp.nwk';
    $newfile = $res . 'restricted_full.nwk';
    $errfile = $res . 'restrict-stderr.txt';
    
    // Suppression longueurs de branches et bootstraps pour la moulinette restrict2common
    /*$nwk = trim(preg_replace('/\)\d*\:/', '):', $nwk));
    $nwk = trim(preg_replace('/\:\d*\.*\d*\)/', ')', $nwk));
    $nwk = trim(preg_replace('/\:\d*\.*\d*\,/', ',', $nwk));*/
    
    file_put_contents($tempfile,$nwk);
    exec("cd ".$res."\nperl ".BINPATH."addFakeLengthAndSupport.pl LS ". $tempfile ." >tempfake");
    //exec("cd ".$res."\nperl ".BINPATH."restrict2commonsDeLuxe.pl tempfake >tempres" ." 2>".$errfile);
    exec("cd ".$res."\nperl ".BINPATH."restrict2commonsCompPhy.pl tempfake >tempres" ." 2>".$errfile);
    exec("cd ".$res."\nperl ".BINPATH."rmvFakeLengthAndSupport.pl LS tempres >". $newfile);
//    echo file_get_contents($errfile);
//    echo file_get_contents($newfile);exit;
    if(file_get_contents($errfile) == '') {

        // On segmente le fichier sorti en arbres
        $back = file_get_contents($newfile);
        $back_split = substr(trim($back),0,-1);
        $backtab = explode(';',$back_split);

        // On crée les arbres correspondants
        foreach($backtab as $key => $value) {
            $donnees = array('db' => $db,
                             'nom' => $trees[$key]->getNom().'_R',
                             'typet' => $trees[$key]->getType(),
                             'newick' => $value.';',
                             'script' => 't -interleaf 20',
                             'annotation' => '',
                             'image' => '',
                             'miniature' => '',
                             'actif' => 1,
                             'proj_id' => $projet->getId());
            $arbre = new Arbre($donnees);
            $arbre->add();
            $arbre->create($res, true);
        }
    }
    else {
        
        require_once 'classes/Request/Abstract.php';
        require_once 'classes/Request/Post.php';
        require_once 'classes/Request/Get.php';
        require_once 'classes/Request/Conversation.php';
        
        $conversation = new Core_Request_Conversation();
        $pr =$conversation->newPost( ROOT . 'compphy/?p=error' );

        $pr->setData( 'm', file_get_contents($errfile) );

        $retour = $pr->send();
        echo $retour."<script type=\"text/javascript\">
                          setTimeout(\"window.location='?p=project&id=".$projet->getId()."'\",5000);
                      </script>";
        
    }
    
    $tempres = $res.'tempres';
    $tempfake = $res.'tempfake';
    $fake = $res.'fake.tmp';
    exec("rm $errfile $newfile $tempfile $tempres $tempfake $fake");
?>

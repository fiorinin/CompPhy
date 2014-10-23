<?php
$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

$projet = Projet::getP($db, $id);
$result_dir = $res = $projet->getRepertoire();

if ($projet->getPublic() == 1 || isset($membre) && $projet->canAccess($membre->getId())) {

    $result_file = $f = '';
    if (isset($_REQUEST['f']))
        $result_file = $f = $_REQUEST['f'];

    $download = '';
    if (isset($_REQUEST['d']))
        $download    = $_REQUEST['d'];

    $tar = '';
    if (isset($_REQUEST['tar']))
        $tar    = $_REQUEST['tar'];

    $col = '';
    if (isset($_REQUEST['col']))
        $col    = $_REQUEST['col'];

    // remove invalid characters
    $result_dir = preg_replace('/[^\w\-]/', '', $result_dir);

    if ('' != $result_dir)
    {
        // "compile" directory
        $result_dir = EXECPATH . $result_dir . "/";
    }

    // check file
    $extension = "";
    if (preg_match('/^(\w{1,64})\.(\w{1,5})$/', $result_file, $matches))
    {
        // file OK, complete path
        $result_file = $result_dir . $result_file;
        $extension = strtolower($matches[2]);
    }
    else
    {
        $result_file = '';
    }
    
    if(preg_match("/^\d+$/", $f)) {
        $file = Document::getD($db, intval($f));
        $f = $file->getAdresse();
        $result_file = $result_dir . 'uploads/' . $f;
        $f = 'uploads/' . $f;
    }

    // check if directory exists
    if ($result_dir
        && $result_file
        && file_exists($result_dir)
        && is_dir($result_dir)
        && is_readable($result_dir)
        && file_exists($result_file)
        && is_readable($result_file))
    {
        // the directory and the file are OK
        if ("png" == $extension)
        {
            header('Content-type: image/png');
        }
        else if ("jpg" == $extension)
        {
            header('Content-type: image/jpeg');
        }
        else if ("gif" == $extension)
        {
            header('Content-type: image/gif');
        }
        else if ("svg" == $extension)
        {
            header('Content-type: image/svg+xml');
        }
        else if ("ps" == $extension)
        {
            header('Content-type: application/postscript');
        }
        else if ("tgf" == $extension)
        {
            header('Content-type: text/plain');
        }
        // check for download
        if ($download)
        {
            // Not a collection
            if (!$col)
            {
                // Source files DL
                if ($tar)
                {
                    $arbre = Arbre::getA($db, $tar);
                    $folder = Utils::filter($arbre->getNom());
                    mkdir($result_dir.$folder, 0774);
                    if($arbre->getNewick() != "") 
                        file_put_contents ($result_dir.$folder."/".$folder.".nwk", $arbre->getNewick());
                    if($arbre->getScript() != "") 
                        file_put_contents ($result_dir.$folder."/".$folder.".tds", $arbre->getScript());
                    if($arbre->getAnnotation() != "") 
                        file_put_contents ($result_dir.$folder."/".$folder.".tlf", $arbre->getAnnotation());
                    exec("cd ".$result_dir."\n".'for i in treepict_'.$tar.'*; do cp $i '.$folder.'/${i/treepict_'.$tar.'/'.$folder.'}; done'."\n");
                    exec("cd ".$result_dir."\n zip -r source_files_".$folder.".zip ".$folder." \n");
                    exec("rm -rf ".$result_dir.$folder."\n");
                    $f = "source_files_".$folder.".zip";
                }
                header("Content-type: application/force-download" );
                header("Content-Disposition: attachment; filename=".$f);
                readfile($result_dir.$f);
            }
            // DL Collection
            else
            {
                $sortedList = Arbre::getSortedList($db, $projet->getId());
                if ($tar == "collection_1" || $tar == "collection_2") {
                    $collection = $tar == "collection_2" ? "collection_2/":"collection_1/";
                    $type = $tar == "collection_2" ? "supertrees":"genetrees";
                    mkdir($result_dir.$collection, 0774);
                    foreach($sortedList[$type] as $key => $arbre) {
                        $folder = Utils::filter($arbre->getNom());
                        mkdir($result_dir.$collection.$folder, 0774);
                        if($arbre->getNewick() != "") 
                            file_put_contents ($result_dir.$collection.$folder."/".$folder.".nwk", $arbre->getNewick());
                        if($arbre->getScript() != "") 
                            file_put_contents ($result_dir.$collection.$folder."/".$folder.".tds", $arbre->getScript());
                        if($arbre->getAnnotation() != "") 
                            file_put_contents ($result_dir.$collection.$folder."/".$folder.".tlf", $arbre->getAnnotation());
                        exec("cd ".$result_dir."\n".'for i in treepict_'.$arbre->getId().'*; do cp $i '.$collection.$folder.'/${i/treepict_'.$arbre->getId().'/'.$folder.'}; done'."\n");
                    }
                    exec("cd ".$result_dir."\n zip -r ".$tar.".zip ".$collection." \n");
                    exec("rm -rf ".$result_dir.$collection."\n");
                    $f = $tar.".zip";
                }
                header("Content-type: application/force-download" );
                header("Content-Disposition: attachment; filename=".$f);
                readfile($result_dir.$f);
            }
        }
        else
        {
            header('Content-Disposition: inline;');
        }
        // output the file
        if (!readfile($result_file))
            echo "<div class=\"important\">Unable to read the file!</div>\n";
    }
    else
    {
        // invalid results! (either removed, unavailable or the user is trying something bad...)
        echo "<div class=\"important\">File not found!</div>\n";
    }
}
?>

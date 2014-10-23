<?php

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<list>";

include('utils.php');
//include('config.php');
set_INC ('compphy/ajax/');

//$db = new PDO('mysql:host='.HOST.';dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

$id1 = 0;
if (isset($_GET['id1']))
    $id1 = intval($_GET['id1']);
$id2 = 0;
if (isset($_GET['id2']))
    $id2 = intval($_GET['id2']);

if($id1 == $id2) {
    echo "<item id='error'>You are trying to compare the same tree.</item>";
} else {
    $arbre1 = Arbre::getA($db, $id1);
    $arbre2 = Arbre::getA($db, $id2);
    $projet = Projet::getP($db, $arbre1->getProj_id());
    $verif = Projet::getP($db, $arbre2->getProj_id());
    $res = EXECPATH . $projet->getRepertoire() . '/';

    if($verif && $projet && $verif->getId() == $projet->getId() && $arbre1 && $arbre2) {

        // Restrict
        $tempfile = $res . 'restricted_temp.nwk';
        $newfile = $res . 'restricted_full.nwk';
        $errfile = $res . 'restrict-stderr.txt';

        $nwk = $arbre1->getNewick()."\n".$arbre2->getNewick();
        $error = false;
        file_put_contents($tempfile,$nwk);
        exec("cd ".$res."\nperl ".BINPATH."compphy/addFakeLengthAndSupport.pl LS ". $tempfile ." >tempfake");
        //exec("cd ".$res."\nperl ".BINPATH."compphy/restrict2commonsDeLuxe.pl tempfake >tempres" ." 2>".$errfile);
        exec("cd ".$res."\nperl ".BINPATH."compphy/restrict2commonsCompPhy.pl tempfake >$newfile" ." 2>".$errfile);
        //exec("cd ".$res."\nperl ".BINPATH."compphy/rmvFakeLengthAndSupport.pl LS tempres >". $newfile);

        $nwk = "";
        if(file_get_contents($errfile) == '') {

            // On segmente le fichier sorti en arbres
            $back = file_get_contents($newfile);
            $back_split = substr(trim($back),0,-1);
            $backtab = explode(';',$back_split);

            // On crée les arbres correspondants
            foreach($backtab as $key => $value) {
                $nwk .= $value.';\n';
            }
        }
        else { $error = file_get_contents($errfile); }

        $tempres = $res.'tempres';
        $tempfake = $res.'tempfake';
        $fake = $res.'fake.tmp';
        exec("rm $errfile $newfile $tempfile $tempres $tempfake $fake");

        file_put_contents($res . "tree_dist.nwk", $nwk);
        file_put_contents($res . "cmd.txt", "tree_dist.nwk\nD\nY\n");
        exec("cd " . $res ."\n" . BINPATH . "compphy/phylip-3.69/src/treedist < cmd.txt >dist.log");
        if (is_file($res . "dist.log"))
            $result = file_get_contents($res . "dist.log");
        if (is_file($res . "outfile") && file_get_contents($res . "outfile") != '')
            $result = file_get_contents($res . "outfile");
        exec("rm -rf ". $res . "tree_dist.nwk " . $res . "cmd.txt " . $res . "dist.log " . $res . "outfile");
        if (preg_match("/(error.*)\:(.*)/i", $result, $m) || $error != false) {
            if(isset($m)) {
                echo "<item id='title'>".$m[1]."</item>";
                echo "<item id='content'>".$m[2]."</item>";
            }
            if($error != false) {
                echo "<item id='title'> during the restriction preceding the distance calculation</item>";
                echo "<item id='content'>".$error."</item>";
            }
        }
        else if(preg_match("/(trees[^:]+):\s*(\d+)/i", $result, $m)){
            echo "<item id='title'>Operation succeded</item>";
            echo "<item id='content'>Distance between trees of the two workbenches is </item>";
            echo "<item id='value'><![CDATA[<span style='color:#006699;'>".$m[2]."</span>]]></item>";
        }
        echo "<item id='note'><![CDATA[Trees were restricted to their common taxa before computing the distance between them with the 
                <a href='http://evolution.genetics.washington.edu/phylip/doc/treedist.html' target='blank'>Treedist</a> tool (\"symmetric 
                difference\" option).Felsenstein, J. 1993. PHYLIP (Phylogeny Inference Package) version 3.5c. Distributed by 
                the author. Department of Genetics, University of Washington, Seattle.]]></item>";
    }
}
echo "</list>";
    
?>

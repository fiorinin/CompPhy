<?php
$nwk = '';

foreach($trees as $key => $value) {
    $raw = $value->getNewick();
    $final = preg_replace("/\)[^\)]+;/", ");" , $raw);
    $nwk .= $final."\n";
}

if ($nwk != '') {
    mkdir($res."mrp");
    $resm = $res."mrp/";
    $mode = $_POST["mode"];
    $datafile = $resm . 'mrp.nwk';
    file_put_contents($datafile, $nwk);
    exec("python ".BINPATH."MRP/spruce/bin/makeRatchetFile.py -i ".$datafile." -o ".$resm."mrp.cmd");
    $cmd = file_get_contents($resm."mrp.cmd");
    exec("cd ".$resm." \n ".BINPATH."/paup-linux ".$resm."mrp.cmd");

    $output = file_get_contents($resm."ratchet.".$mode[0]."mrp");
    if(preg_match("/\[\&U\]\s([^;]+);/", $output, $match)) {
        $newick = $match[1].";";
        $script = "t -interleaf 20\nesn -what x: -box 0 -fg blue -font {arial 5 normal}";
        $donnees = array('db' => $db,
                        'nom' => "MRP",
                        'typet' => "2",
                        'newick' => $newick,
                        'script' => $script,
                        'annotation' => '',
                        'image' => '',
                        'miniature' => '',
                        'actif' => 1,
                        'proj_id' => $projet->getId());
       $arbre = new Arbre($donnees);
       $arbre->add();
       $arbre->create($res, true);
    } else {
        $errors = "No supertree has been generated. Please check your trees and try again.";
    }
    
    rmdir($resm);
} else {
    $errors = "You did not select any tree. Therefore, the supertree computing could not run properly.";
}
?>

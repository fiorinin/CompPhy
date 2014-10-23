<?php
// Variables d'entrée
$scale = false;
$bootstrap = false;
$branches = false;
$letbootstrap = true;
$letbranches = true;
$font = false;
$interleaf = false;

if(isset($_POST['displaybootstrap']) && $_POST['displaybootstrap'] == "1") {
    $letbootstrap = false;
    if (isset($_POST["addBootstrap"]) && $_POST["addBootstrap"] == "1") 
        $bootstrap = true;
}
if(isset($_POST['displayscale']) && $_POST['displayscale'] == "1") {
    $letscale = false;
    if (isset($_POST["addScale"]) && $_POST["addScale"] == "1") 
        $scale = true;
}
if(isset($_POST['displaybranches']) && $_POST['displaybranches'] == "1") {
    $letbranches = false;
    if (isset($_POST["addBranches"]) && $_POST["addBranches"] == "1") 
        $branches = true;
}
if(isset($_POST['changeSizeT']) && $_POST['changeSizeT'] == "1") {
    $font = true;
}
if(isset($_POST['changeInterleafT']) && $_POST['changeInterleafT'] == "1") {
    $interleaf = true;
}

$boscript = 'esn -what x: -box 0 -fg blue -font {arial 5 normal}';
$brscript = 'esn -what :x -box 0 -fg blue -font {arial 5 normal} -leaf 1';

foreach($trees as $key => $value) {
    if(!preg_match("/^t.*/", $value->getScript()))
        $treescript = "t -x 20 -y 20\n";
    else
        $treescript = $value->getScript();

    if (preg_match("#(.*\n*)(esn [^\n]*)(\n*.*)#", $treescript, $matches)) {
        // Gestion de bootstrap
        if($letbootstrap == false) {
            if($bootstrap != true) {
                if(preg_match("#x:#", $matches[2]))
                    $treescript = trim($matches[1]) . "\n" . trim($matches[3]);
            }
            else {
                if(!preg_match("#x:#", $matches[2]))
                    $treescript = trim($matches[1]) . "\n" . preg_replace ("#\:x#", "x:", $matches[2]) . "\n" . trim($matches[3]);
            }
        }
        // Gestion de longueurs de branches
        if($letbranches == false) {
            if($branches != true) {
                if(preg_match("#:x#", $matches[2]))
                    $treescript = trim($matches[1]) . "\n" . trim($matches[3]);
            }
            else {
                if(!preg_match("#:x#", $matches[2]) && !preg_match("#-leaf#", $matches[2]))
                    $treescript = trim($matches[1]) . "\n" . preg_replace ("#x\:#", ":x", $matches[2]) . " -leaf 1\n" . trim($matches[3]);
                elseif(!preg_match("#:x#", $matches[2]) && preg_match("#-leaf#", $matches[2]))
                    $treescript = trim($matches[1]) . "\n" . preg_replace ("#x\:#", ":x", preg_replace("#\-leaf\s\d#", "-leaf 1", $matches[2])) . trim($matches[3]);
            }
        }
    }
    else {
        if ($bootstrap == true)
                $treescript = trim($treescript) . "\n" . $boscript;
        elseif ($branches == true)
                $treescript = trim($treescript) . "\n" . $brscript;
    }
    
    // Gestion d'échelle
    $scalescript = "";
    if($scale)
        $scalescript = " -scale {0 10 black}";
    
    // Gestion de fonte
    $fontscript = "";
    if($font)
        $fontscript = " -font {Arial ".$_POST['changeSize']." italic}";
    
    // Gestion d'interleaf
    $interleafscript = "";
    if($interleaf)
        $interleafscript = " -interleaf ".$_POST['changeInterleaf'];
    
    $oldscale = "";
    $oldfont = "";
    $oldfontbp = "";
    $oldleafs = "";
    if(preg_match("/(.*)( \-scale \{\d{1,} \d{1,} \w{1,}\})(.*)/s", $treescript, $matches)) {
        $oldscale = $matches[2];
        $treescript = $matches[1] . $matches[3];
    }
    
    if(preg_match("/(.*)( \-font \{\w{1,} \d{1,} \w{1,}\})(.*)/s", $treescript, $matches)) {
        if(!preg_match("/-fg blue/", $matches[1])) {
            $oldfont = $matches[2];
            $treescript = $matches[1] . $matches[3];
        }
    }
    if(preg_match("/(.*)( \-interleaf \d{1,})(.*)/s", $treescript, $matches)) {
        $oldleafs = $matches[2];
        $treescript = $matches[1] . $matches[3];
    }
    if (preg_match("/^(t [^\n]*)(.*)/s", $treescript, $matches)) {
        $treescript = trim($matches[1]) . ($scale ? $scalescript:$oldscale) . ($font ? $fontscript:$oldfont) . ($interleaf ? $interleafscript:$oldleafs) . $matches[2];
    }
    
    $value->setScript($treescript);
    $value->update();
    $value->create($res);
}
?>

<?php
include('utils.php');
//include('config.php');
set_INC ('compphy/ajax/');

//$db = new PDO('mysql:host=localhost;dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

$treeId = 0;
if (isset($_POST['tree']))
    $treeId = intval($_POST['tree']);


$arbre = Arbre::getA($db, $treeId);
$projet = Projet::getP($db, $arbre->getProj_id());


$reverse = false;
if($_POST['reverse'] == '1') {
    $reverse = true;
}
$params = $_POST["params"];
$side = $_POST["side"];
$width = $_POST["width"];
$height = $_POST["height"];

if($side == "left" && $reverse == false || $side == "right" && $reverse == 1) {
    $image = EXECPATH . $projet->getRepertoire()."/".$arbre->getImage();
    $svg = new SimpleXMLElement(file_get_contents($image));
    $svg->g["transform"] = $params;
    $svg->g["width"] = $width;
    $svg->g["height"] = $height;
    $svg->asXML($image);
    $projet->update();
} else {
    $imageR = EXECPATH . $projet->getRepertoire()."/".$arbre->getImageR();
    $svgR = new SimpleXMLElement(file_get_contents($imageR));
    $svgR->g["transform"] = $params;
    $svgR->g["width"] = $width;
    $svgR->g["height"] = $height;
    $svgR->asXML($imageR);
    echo $svgR->asXML();
    $projet->update();
}
?>

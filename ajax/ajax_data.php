<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<list>";

include('utils.php');
//include('config.php');
set_INC ('compphy/ajax/');

//$db = new PDO('mysql:host=localhost;dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

$arbre = Arbre::getA($db, $id);
$projet = Projet::getP($db, $arbre->getProj_id());

$nwk = '';
$script = '';
$annotation = '';

if ($arbre && $projet) {
    $nwk = $arbre->getNewick();
    $script = $arbre->getScript();
    $annotation = $arbre->getAnnotation();


    echo "<item id=\"newick\">".$nwk."</item>";
    echo "<item id=\"script\">".$script."</item>";
    echo "<item id=\"annotation\">".$annotation."</item>";
}

echo "</list>";

?>

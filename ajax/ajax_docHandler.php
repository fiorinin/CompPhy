<?php
session_start();

// TODO : ajouter la sécurité sur les pages AJAX
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

if (isset($_SESSION['membre'])) {
    $membre = Utilisateur::getM($db, $_SESSION['membre']);
}

$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);
$r = '';
if (isset($_GET['r']))
    $r = $_GET['r'];
$idd = 0;
if (isset($_GET['idd']))
    $idd = intval($_GET['idd']);

$projet = Projet::getP($db, $id);
$doc = Document::getD($db, $idd);

if($projet->isLead($membre->getId()) || $doc->isSender($membre->getId())) {
    if($r=="1")
        $doc->delete();
    else {
        $doc->setTitre($r);
        $doc->update();
    }
}

echo "</list>";

if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

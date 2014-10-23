<?php
session_start();

// TODO : ajouter la sécurité sur les pages AJAX
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

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

$projet = Projet::getP($db, $id);

if($projet->isLead($membre->getId())) {
    $projet->delete();
}

if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

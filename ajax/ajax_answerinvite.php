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

$projet = Projet::getP($db, $id);

$accept = null;
if(isset($_POST['answer']) && $_POST['answer'] == "true")
    $accept=true;
else
    $accept=false;

if(isset($accept)) {
    $projet->acceptInvitation($membre, $accept);
    echo "<item id='title'>".$projet->getTitre()."</item>";
    echo "<item id='date'>".$projet->getCreationDate()."</item>";
    echo "<item id='desc'>".$projet->getDescription()."</item>";
}

echo "</list>";
if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

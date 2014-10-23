<?php
session_start();

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

$fname = isset($_POST['fname']) ? $_POST['fname']:null;
$lname = isset($_POST['lname']) ? $_POST['lname']:null;
$email = isset($_POST['email']) ? $_POST['email']:null;
$idm = isset($_POST['id']) ? $_POST['id']:null;
$delete = isset($_POST['del']) ? true:false;

if($lname && $email && !$delete) {
    $validtarget = true;
    foreach($projet->getUsers() as $key => $user) {
        if(preg_match("/^".$user->getMail()."$/i", $email))
            $validtarget = false;
    }
    if(preg_match("/^".$projet->getLead()->getMail()."$/i", $email))
        $validtarget = false;
    if($validtarget == true)
        $projet->invite($membre, $email, $lname, $fname != null ? $fname:'' );
}
elseif ($idm && $delete) {
    $projet->delFromProject(intval($idm));
}

if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

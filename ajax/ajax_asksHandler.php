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

$membre= null;
if (isset($_SESSION['membre'])) {
    $membre = Utilisateur::getM($db, $_SESSION['membre']);
    $membre->save();
}

$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

$projet = null;

if(!Projet::existsS($id, $db)) {
    echo "<return>err</return></list>";
    exit;
}

$projet = Projet::getP($db, $id);

$a = isset($_POST['a']) ? $_POST['a']:null;
$idu = isset($_POST['idu']) && $_POST['idu'] != '' ? $_POST['idu']:null;
$idl = isset($_POST['idl']) && $_POST['idl'] != '' ? $_POST['idl']:null;

if($a && $a == 'check') {
    $demandeurs = $projet->listerDemandes();
    foreach($demandeurs as $key => $user)
        echo "<asker id='".$user->getId()."'>".$user->getPrenom(). " " .$user->getNom() . "</asker>";
}
if($a && $a == 'refreshusers') {
    $usersonline = array();
    foreach ($projet->getUsers() as $key => $user) {
        if ($user->isOnline())
            array_push($usersonline, $user);
    }
    foreach ($usersonline as $user) {
        echo "<user id='".$user->getId()."' main='".($projet->getMain() == $user->getId() ? "1":"0")."'
               lead='".($projet->getLead()->getId() == $user->getId() ? "1":"0")."' 
               me='".($membre->getId() == $user->getId() ? "1":"0")."'>" . 
               $user->getPrenom() . " " . $user->getNom() . "</user>";
    }
}
if($a && $a == 'checkhand')
    echo "<hand id='".$projet->hasHand()->getId()."'>".$projet->hasHand()->getPrenom(). " " .$projet->hasHand()->getNom() . "</hand>";

if($a && $a == "askhand" && $membre && $projet->canAccess($membre->getId()))
    $projet->demanderMain($membre->getId());

if($a && $a == "gethand" && $membre && $projet->isLead($membre->getId()))
    $projet->donnerMain($membre->getId());

if($a && $a == "validate" && $membre && $projet->getMain() == $membre->getId())
    $projet->donnerMain(intval($idu));

if($a && $a == "refuse" && $membre && $projet->getMain() == $membre->getId())
    $projet->deleteDemande(intval($idu));

if($a && $a == "gettrees") {
    $sortedList = Arbre::getSortedList($db, $projet->getId());
    $g_max = Arbre::CountTrees($db, $projet->getId(), 1);
    $s_max = Arbre::CountTrees($db, $projet->getId(), 2);
    $maing = Arbre::getA($db, $projet->getMaing());
    $maind = Arbre::getA($db, $projet->getMaind());
    echo "<timestamp>".$projet->getLast_update()."</timestamp>";
    echo "<captions left='".$maing->getId()."' right='".$maind->getId()."' nleft='".$maing->getNom()."' nright='".$maind->getNom()."' tleft='".$maing->getType()."' tright='".$maind->getType()."'></captions>";
    if($g_max >= 1) {
        foreach($sortedList['genetrees'] as $key => $tree) {
            echo "<tree nom='".$tree->getNom()."' id='".$tree->getId()."' type='".$tree->getType()."'>" . $tree->getMiniature() ."</tree>";
        }
    }
    if($s_max >= 1) {
        foreach($sortedList['supertrees'] as $key => $tree) {
            echo "<tree nom='".$tree->getNom()."' id='".$tree->getId()."' type='".$tree->getType()."'>" . $tree->getMiniature() ."</tree>";
        }
    }
}

if($a && $a == "sort" && $membre && $projet->getMain() == $membre->getId()) {
    foreach ($_GET['v'] as $position => $item) {
        $arbre = Arbre::getA($db, intval($item));
        $arbre->setOrder(intval($position));
        $arbre->update();
    }
}

if($a && $a == "switchcaptions" && $membre && $projet->getMain() == $membre->getId()) {
    $projet->setMaind (intval($idu));
    $projet->setMaing (intval($idl));
    $projet->update();
}

echo "</list>";
if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

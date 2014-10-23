<?php
session_start();

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";

include('utils.php');
//include('config.php');
set_INC ('compphy/ajax/');
echo "<list>";

//$db = new PDO('mysql:host=localhost;dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

if (isset($_SESSION['membre'])) {
    $membre = Utilisateur::getM($db, $_SESSION['membre']);
}

$requete = isset($_POST['requete']) ? $_POST['requete']:null;
$idproj = isset($_POST['idproj']) ? intval($_POST['idproj']):null;
$id = isset($_POST['id']) ? intval($_POST['id']):null;
$content = isset($_POST['content']) ? $_POST['content']:null;
$save = isset($_POST['save']) && $_POST['save'] == 'true' ? true:false;

if(isset($requete) && $requete != "addspec")
    $projet = Projet::getP($db, $idproj);
else {
    $todo = ToDo::getT($db, $idproj);
    $projet = Projet::getP($db, $todo->getProj_id());
    $idproj = $projet->getId();
}

if ($membre && $projet->getMain() == $membre->getId() || $projet->isLead($membre->getId())) {
    if($requete == "addspec") {
        $requete = "add";
    }
    
    if($requete == 'add' && $content != "") {
        $message = new Historique(array('db' => $db, 'user_id' => $membre->getId(), 'proj_id' => $idproj, 'description' => $content));
        $message->add();
        $id = $message->getId();
        $message = Historique::getH($db, $id);
        
        $author = Utilisateur::getM($db, $message->getUser_id());
        echo "<item id='".$id."' prenom='".$author->getPrenom()."' nom='".$author->getNom()."' date='".date('d-m-Y H:i', strtotime($message->getDate()))."'";
    
        if($save) {
            $idsave = $projet->saveNow($id);
            echo " idsave='".$idsave."' ";
            $message->setSave($idsave);
            $message->update();
        }
        else
            echo " idsave='no' ";
        echo ">".$message->getDescription()."</item>";
    }
    
    if($requete == "remove" && $id != 0) {
        $message = Historique::getH($db, $id);
        $message->delete();
    }
}

echo "</list>";
if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

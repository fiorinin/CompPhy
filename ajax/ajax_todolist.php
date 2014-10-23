<?php
session_start();

header("Content-Type: text/xml");

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

$idproj = isset($_GET['idproj']) ? (int)$_GET['idproj']:null;
$id = isset($_GET['id']) ? (int)$_GET['id']:null;
if(isset($id)) {
    $todo = ToDo::getT($db, $id);
    $projet = Projet::getP($db, $todo->getProj_id());
}
else
    $projet = Projet::getP($db, $idproj);

if ($membre && $projet->getMain() == $membre->getId() || $projet->isLead($membre->getId())) {

    try{
        switch($_GET['action']) {
            case 'delete':
                $todo->delete();
                break;

            case 'rearrange':
                foreach ($_GET['positions'] as $position => $item) {
                    $todo = ToDo::getT($db, intval($item));
                    $todo->setOrdernb(intval($position));
                    $todo->update();
                }
                break;

            case 'edit':
                $todo->setContent($_GET['text']);
                $todo->update();
                break;

            case 'new':
                $todo = new ToDo(array("db" => $db, "content" => $_GET['text'], "status" => 1, "proj_id" => $projet->getId()));
                $todo->calcOrdernb();
                $todo->add();
                echo $todo;
                exit;
                break;

            case 'validate':
                $todo ->setStatus(0);
                $todo->update();
                echo $todo->toStringOld();
                exit;
                break;
        }
    }
    catch(Exception $e){
            die("0");
    }

    echo "1";
}

if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();
?>

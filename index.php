<?php

include ('utils.php');
set_INC ('compphy/');

/**
* Démarrage de la session
*/
ob_start();
session_start();

/**
* Connexion à la base de données
*/    
//$db = new PDO('mysql:host='.HOST.';dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);


/**
* Restauration de $membre s'il existe en session
*/
if (isset($_SESSION['membre'])) {
    $membre = Utilisateur::getM($db, $_SESSION['membre']);
    $membre->save();
}

if(Navigate::displayDesign()) {
    include (HEADERC);

    echo "<body>";
    
    echo "<div id=\"page\">";
//    if(!isset($_GET['p']) || isset($_GET['p']) && $_GET['p'] != "project")
//        include (BANNER);

    //if (Navigate::projectTemplate())
      echo "<div id=\"projectcontent\">";
    /*else {
      include (MENU);
      echo "<div id=\"paragraphe\">";
    }*/
      

    //include (TITLE);

    include ('includes/menu.inc.php');
}

//echo Navigate::pagination((isset($membre)));
include (Navigate::pagination((isset($membre))));

/**
* Stockage des données en session
*/
if (isset($membre)) 
    $_SESSION['membre'] = $membre->getMail();

if(Navigate::displayDesign()) {
/*
    echo '<div class="important">
       This tool is on development stage, some bugs could remain.
       <a href="?p=contact">Comments much welcome.</a></div>';
*/
    if(!isset($_GET['p']))
      echo "</div>";
    
//    include (FOOTER);
//    if(isset($_GET['p']) && $_GET['p'] == "project")
//        include (BANNER);

    include(FOOTERC);
}
ob_end_flush();
?>
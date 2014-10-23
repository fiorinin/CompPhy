<?php
    /**
    * Deconnexion du site
    */
    unset($_SESSION['membre']);
    $membre = null;
    Navigate::redirectMessage("home", "You are now disconnected from CompPhy.", 1);
?>

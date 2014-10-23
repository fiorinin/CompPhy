<?php
if($_GET['exe'] == "restore")
    $projet->restore($idsave);
elseif($_GET['exe'] == "removesvg")
    $projet->removeSave($idsave);
?>
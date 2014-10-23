<?php

$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

if(isset($membre)) {
    $omembre = Utilisateur::getM($db, intval($id));
?>

<div class="memberblock">
    <? if($omembre->getAvatar() == '') { ?>
        <img src="img/noavatar.gif" alt="Avatar" class="avatar" />
    <? } else { ?>
        <img src="avatars/<?=$omembre->getAvatar();?>" alt="Avatar" class="avatar" />
    <? } ?>
    <div class="rightavatar">
        <span class="name"><?=$omembre->getPrenom().' '.$omembre->getNom();?></span>
        <span class="lastaction">Last action : <?=date('d-m-Y H:i', strtotime($omembre->getLast_action()));?></span>
        <div class="<?=$omembre->isOnline() ? 'online':'offline';?>"><?=$omembre->isOnline() ? 'Online':'Offline';?></div>
    </div>
    
</div>
<?
}
?>

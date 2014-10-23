<?php
$mail = isset($_POST['mail']) ? $_POST['mail'] : null;
$s = isset($_POST['submit']) ? $_POST['submit'] : null;

$valid = '';
$erreur = '';
if ($mail != '' && isset($s)) {
    if (Utilisateur::exists($mail, $db)) {
        $lostpass = Utilisateur::getM($db, $mail);
        $lostpass->passRecovery();
        Navigate::redirectMessage("home", "An e-mail has been sent with your new password.",1);
    }
    else
        Navigate::redirectMessage("forgot","This mail adress is not a member.", 2);
}
elseif (isset($s))
    Navigate::redirectMessage("forgot", "You did not fill the email field.",2);
?>
<script>
    $(document).ready(function(){
        $("#reminder").validationEngine();
        changeTitle("CompPhy - Password recovery");
    })
</script>
<div class="row">
    <div id="content">
        <div id="lefts">
            <div class="homeblock">
                <h4>Forgotten password</h4><hr>
                <form id="reminder" class="formular" method="post" action="">
                    <label>E-mail</label>
                    <input name="mail" type="text" class="text validate[required,custom[email]]" id="mail">
                    <div class="row text-center">
                        <button type="submit" class="button" name="submit">Generate a new password</button>
                    </div>
                </form>
            </div>
            <small>Passwords are stored encrypted in CompPhy, so even our team does not have access to them.
                But CompPhy can generate a new password for you and send it to you by email. 
                Once logged in with this new password you will be able to change this password by going to the <i>Account settings</i> page.</small>
        </div>
    </div>
</div>
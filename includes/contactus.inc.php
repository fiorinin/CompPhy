<?php
$s = isset($_POST['submit']) ? $_POST['submit'] : null;

if (!isset($_REQUEST['honeypot']) && !$_REQUEST['honeypot'] && $_REQUEST['honeypot'] == ''
        && isset($_POST['mail'])
        && $_POST['mail'] != ''
        && isset($_POST['comment'])
        && $_POST['comment'] != ''
        && isset($s)) {

    $email = $_POST['mail'];
    $nom = isset($_POST['lname']) ? $_POST['lname'] : '';
    $prenom = isset($_POST['fname']) ? $_POST['fname'] : '';
    $comment = $_POST['comment'];

    $mail = new phpmailer;

    $mail->IsMail();
    $mail->From = $email;
    $mail->FromName = $prenom . " " . $nom;
    $mail->AddAddress("contact@creatox.com");
    $mail->AddAddress("vberry@lirmm.fr");
    $mail->AddAddress("lefort@lirmm.fr");
    $mail->WordWrap = 100;
    $mail->IsHTML(true);
    $mail->CharSet = 'utf-8';
    $mail->Subject = "CompPhy contact";
    $mail->Body = "Bonjour,<br><br>
        " . $prenom . ' ' . $nom . ($prenom != '' || $nom != '' ? ', ' : '') . $email . " a envoyé un message via la prise de contact de CompPhy.<br>
            Voici le contenu du message :<br><br>
            " . $comment;
    $mail->Send();

    Navigate::redirectMessage("contact", "Your comment/question has been sent, thank you. We will come back to you as soon as possible.", 1);
} elseif (isset($s)) {
    Navigate::redirectMessage("contact", "The mail has not been sent. Please fill all required fields (marked with *).", 2);
}
?>
<div class="row">
    <h4>Contact us</h4><hr>
    <form action="" method="POST">
        <div class="row">
            <div class="small-6 columns">
                <label>First name</label>
                <input type="text" name="fname" <?= isset($membre) ? "value='" . $membre->getPrenom() . "'" : ""; ?>>
                <label>Last name</label>
                <input type="text" name="lname" <?= isset($membre) ? "value='" . $membre->getNom() . "'" : ""; ?>>
                <label>E-mail*</label>
                <input type="text" name="mail" <?= isset($membre) ? "value='" . $membre->getMail() . "'" : ""; ?>>
                <input type="text" name="phone" class="phone" alt="Please do not fill">
            </div>
            <div class="small-6 columns">
                <label>Comment*</label>
                <textarea name="comment" rows="9"></textarea>
            </div>
        </div>

        <div class="row text-center">
            <button type="submit" class="button" name="submit">Send comment</button>
        </div>
        <small>(*): required fields.</small>
    </form>
</div>

<?php
$prenom = (!isset($_POST['prenom'])) ? "" : $_POST['prenom'];
$nom = (!isset($_POST['nom'])) ? "" : $_POST['nom'];
$mail = (!isset($_POST['mail'])) ? "" : $_POST['mail'];
$mailconf = (!isset($_POST['mailconf'])) ? "" : $_POST['mailconf'];
$password = (!isset($_POST['password'])) ? "" : $_POST['password'];
$passwordconf = (!isset($_POST['passwordconf'])) ? "" : $_POST['passwordconf'];

$valid = '';
$erreur = '';
if ($_POST['submitted'] == "1") {
    if ($nom != '' && $prenom != '' && $mail != '' && $mailconf != '' && $password != '' && $passwordconf != '') {
        if ($mail == $mailconf || $password == $passwordconf) {
            $toCreate = new Utilisateur(Array('db' => $db, 'prenom' => $prenom, 'nom' => $nom, 'mail' => $mail, 'password' => $password));
            if (!Utilisateur::exists($toCreate->getMail(), $db)) {
                $toCreate->save();
                $membre = $toCreate;
                Navigate::redirectMessage("?p=projects", "You have created an account on CompPhy and you're now connected, thank you.", 1);
            }
            else
                $erreur = "This mail address is already used.";
        }
        else
            $erreur = "You did not type the same password or email in the re-type fields.";
    }
    else
        $erreur = "You did not fill every needed field.";
}

if ($erreur != '') {
    Navigate::redirectMessage("register", $erreur, 2);
}

if (!isset($membre)) {
    ?>


    <div class="row">
        <h4>Register on CompPhy</h4><hr>
        <div id="content">
            <div id="lefts">
                <form id="register" method="post" action="">
                    <div class="row">
                        <div class="small-4 columns">
                            <div class="homeblock">
                                <h3 class="subheader">About you</h3>
                                <input name="prenom" type="text" id="prenom" placeholder="Enter your name"/>
                                <input name="nom" type="text" id="nom" placeholder="Enter your surname"/>
                            </div>
                        </div>
                        <div class="small-4 columns">
                            <div class="homeblock">
                                <h3 class="subheader">Your email (used to log in)</h3>
                                <input name="mail" type="text" id="mail" placeholder="Enter your email address"/>
                                <input name="mailconf" type="text" id="mailconf" placeholder="Confirm your email address"/>
                            </div>
                        </div>
                        <div class="small-4 columns">
                            <div class="homeblock">
                                <h3 class="subheader">Your password</h3>
                                <input name="password" type="password" id="password" placeholder="Enter your password"/>
                                <input name="passwordconf" type="password" id="passwordconf" placeholder="Confirm your password"/>
                            </div>
                        </div>
                        <input type="hidden" name="submitted" value="1">
                    </div>
                    <div class="row text-center">
                        <button type="submit" class="button" name="submit"> Register now </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $("#register").validate({
                rules: {
                    prenom: {
                        required: true
                    },
                    nom: {
                        required: true
                    },
                    password: {
                        required : true,
                        rangelength: [4, 12]
                    },
                    passwordconf: {
                        required : true,
                        equalTo : "#password"
                    },
                    mail: {
                        required : true,
                        email : true
                    },
                    mailconf: {
                        required : true,
                        equalTo : "#mail"
                    }
                }
            });
            $(function () {
                $.each($.validator.methods, function (key, value) {
                    $.validator.methods[key] = function () {
                        var el = $(arguments[1]);
                        if (el.is('[placeholder]') && arguments[0] == el.attr('placeholder'))
                            arguments[0] = '';

                        return value.apply(this, arguments);
                    };
                });
            });
            changeTitle("CompPhy - Register");
        })
    </script>
<? } ?>

<div class="row">
    <?php
    $sent = (!isset($_POST['sent'])) ? "" : $_POST['sent'];
    $nom = (!isset($_POST['nom'])) ? "" : $_POST['nom'];
    $prenom = (!isset($_POST['prenom'])) ? "" : $_POST['prenom'];
    $mail = (!isset($_POST['mail'])) ? "" : $_POST['mail'];
    $mailconf = (!isset($_POST['mailconf'])) ? "" : $_POST['mailconf'];
    $password = (!isset($_POST['oldpassword'])) ? "" : $_POST['oldpassword'];
    $newpassword = (!isset($_POST['newpassword'])) ? "" : $_POST['newpassword'];
    $delavatar = (!isset($_POST['delavatar'])) ? "" : $_POST['delavatar'];
    $erreur = '';
    $valid = '';

    if ($sent == '1') {
        if ($prenom != '')
            $membre->setPrenom($prenom);
        else
            $erreur .= 'You cannot have an empty name.<br>';
        if ($nom != '')
            $membre->setNom($nom);
        else
            $erreur .= 'You cannot have an empty surname.<br>';
        if ($mail == $mailconf && $mail != $membre->getMail() && $mail != '')
            $membre->setMail($mail);
        elseif ($mail != $mailconf)
            $erreur .= 'You did not enter the same E-mail in each E-mail field.<br>';
        if ($password != '' && md5($password) == $membre->getPassword()) {
            if ($newpassword != '')
                $membre->setPassword(md5($newpassword));
        }
        elseif ($password != '' && md5($password) != $membre->getPassword())
            $erreur .= 'You did not enter the right password.';
        if ($delavatar == "1")
            $membre->delAvatar();

        $handle = new upload($_FILES['avatar']);
        if ($handle->uploaded) {
            $handle->allowed = array('image/*');
            if ($handle->uploaded) {
                $membre->delAvatar();
                $handle->image_resize = true;
                $handle->image_ratio_crop = true;
                $handle->image_x = 80;
                $handle->image_y = 80;
                $handle->image_convert = 'png';
                $handle->file_auto_rename = false;
                $handle->file_overwrite = true;
                if ($membre->getAvatar() == '')
                    $str = $membre->createAvatarName();
                $handle->file_new_name_body = $str;
                $handle->Process(ROOTPATH . 'compphy/avatars');
                if ($handle->processed) {
                    $handle->clean();
                    // Image uploadée avec succès !
                } else {
                    $erreur .= $handle->error;
                    $handle->clean();
                }
            }
        }

        $membre->save();
        if ($erreur != '') {
            Navigate::redirectMessage("settings", $erreur, 2);
        } else {
            Navigate::redirectMessage("settings", "Your profile has been successfully updated.", 1);
        }
    }
    ?>
    <h4>My account</h4><hr>
    <div id="content">
        <div id="lefts">
            <form id="modify" class="custom" method="post" action="" enctype="multipart/form-data">
                <h4 class="subheader">Overview</h4>
                <div class="row">
                    <div class="small-6 columns">
                        <ul class="vcard">
                            <li class="photo">
                                <?php if ($membre->getAvatar() != '') { ?>
                                    <img src='avatars/<?= $membre->getAvatar(); ?>' alt='Avatar' class="avatar" style='float:left;'/>
                                <? } ?>
                            </li>
                            <li class="fn"><?= $membre->getPrenom() ?> <?= $membre->getNom() ?></li>
                            <li class="email"><a href="#"><?= $membre->getMail() ?></a></li>
                        </ul>
                    </div>

                    <div class="small-6 columns">
                        <?php if ($membre->getAvatar() != '') { ?>
                            <label for="delavatar"><input type="checkbox" name='delavatar' value="1" id="delavatar" style="display: none;"><span class="custom checkbox"></span> Delete this picture</label><br>
                            <label>Change the picture</label>
                            <input type="file" name="avatar" />
                        <?php } else { ?>
                            <label>Add a picture</label>
                            <input type="file" name="avatar" />
                        <?php } ?>
                    </div>
                </div>
                <hr>
                <h4 class="subheader">Modify your profile</h4>
                <div class="homeblock">
                    <div class="row">
                        <div class="small-4 columns">
                            <div class="homeblock">
                                <h5 class="subheader">About you</h5>
                                <input name="prenom" type="text" id="prenom" value="<?= $membre->getPrenom() ?>"/>
                                <input name="nom" type="text" id="nom" value="<?= $membre->getNom() ?>"/>
                            </div>
                        </div>
                        <div class="small-4 columns">
                            <div class="homeblock">
                                <h5 class="subheader">Your mail login</h5>
                                <input name="mail" type="text" id="mail" value="<?= $membre->getMail() ?>"/>
                                <input name="mailconf" type="text" id="mailconf" value="<?= $membre->getMail() ?>"/>
                            </div>
                        </div>
                        <div class="small-4 columns">
                            <div class="homeblock">
                                <h5 class="subheader">Your password</h5>
                                <input name="oldpassword" type="password" id="oldpassword" placeholder="Your old password"/>
                                <input name="newpassword" type="password" id="newpassword" placeholder="Your new password"/>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" style="display:none;" value="1" name="sent"/>
                    <center><button type="submit" class="button" name="submit"> Save changes </button></center>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $("#modify").validate({
            rules: {
                prenom: {
                    required: true
                },
                nom: {
                    required: true
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
        changeTitle("CompPhy - My account");
    })
</script>
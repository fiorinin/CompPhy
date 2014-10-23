<?php
$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

$projet = Projet::getP($db, $id);
$result_dir = $res = $projet->getRepertoire();

if ($projet->getPublic() == 1 || isset($membre) && $projet->canAccess($membre->getId())) {

    // Traitement des informations de modification
    if ($projet->isLead($membre->getId())) {
        $ptitle = isset($_POST['title']) ? $_POST['title'] : '';
        $pdesc = isset($_POST['desc']) ? $_POST['desc'] : '';
        $pnextlead = isset($_POST['nextlead']) ? $_POST['nextlead'] : '';
        $pprivacy = isset($_POST['privacy']) ? $_POST['privacy'] : '';
        if ($ptitle != '')
            $projet->setTitre($ptitle);
        if ($pdesc != '')
            $projet->setDescription($pdesc);
        if ($pnextlead != '')
            $projet->setChef_id(intval($pnextlead));
        if ($pprivacy != '')
            $projet->setPublic(intval($pprivacy));
        if ($ptitle != '' | $pdesc != '' | $pnextlead != '' | $pprivacy != '')
            $projet->update();
    }
    ?>
    <div class="row">
        <?
        echo "<h4 class='subheader'>Project: " . $projet->getTitre() . "</h4><hr>";
        include (HEREPATH . 'includes/project_menu.php');
        if ($projet->isLead($membre->getId())) {
            ?>
            <script>
                $(document).ready(function() {
                    $("#invitor").click(function(evt) {
                        evt.preventDefault();
                        var fname = $( "#fname" ),
                        email = $( "#email" ),
                        lname = $( "#lname" ),
                        allFields = $( [] ).add( fname ).add( email ).add( lname );
                        var bValid1 = true; var bValid2 = true; var bValid3 = true;
                        allFields.removeClass( "error" );
                        $("small").remove(".error");

                        bValid1 = checkRegexp( fname, /^([a-z\-])*$/i, "First name may consist of a-z and hyphenes." );
                        bValid2 = checkRegexp( lname, /^[a-z]([a-z\-])+$/i, "Last name may consist of a-z and hyphenes." );
                        bValid3 = checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "Wrong email." );
                        var bValid = bValid1 && bValid2 && bValid3;

                        if ( bValid ) {
                            $("#inviteuser form").ajaxSubmit({
                                url: 'ajax/ajax_adduser.php?id='+<?= $projet->getId(); ?>,
                                type: "post",
                                error: function(jqXHR, textStatus, errorThrown){
                                    alert("There is an error with AJAX."+jqXHR+textStatus+errorThrown);
                                },
                                beforeSubmit:function(){},
                                complete:function(e){
                                    updateTips("The invitation has been sent.");
                                    $( "#fname" ).val("");
                                    $( "#email" ).val("");
                                    $( "#lname" ).val("");
                                }
                            });
                            return false;
                        }
                    });
                    $(".validateTips").hide();         
                                                    
                    //loadInviteDialog("<?= $projet->getId(); ?>");
                    $(".delp").click(function() {
                        $("#delpconf").attr('data-project',$(this).attr('id'));
                    });
                    $("#projectremover").click(function() {
                        $.ajax({
                            type: "GET",
                            url: "ajax/ajax_delproject.php?id="+$("#delpconf").attr('data-project'),
                            dataType : "xml",
                            complete:function(){
                                $("#delpconf").text("The project has successfully been deleted.");
                                setTimeout("window.location='?p=projects'",2000);
                            }
                        });
                    })
                    /*$( "#invitebutton" ).click(function() {
                        $( "#inviteuser" ).dialog( "open" ); return false;
                    });*/
                    $( ".rmuser" ).click(function() {
                        $(this).delUser("<?= $projet->getId(); ?>");
                        return false;
                    });
                })
            </script>
            <div class="small-6 columns">
                <form action="?p=settingsp&id=<?= $projet->getId(); ?>" method="POST" id="modifyp" class="custom">
                    <h5 class="subheader">About the project</h5><hr/>
                    <label>Title</label>
                    <input type="text" name="title" value="<?= $projet->getTitre(); ?>">
                    <label>Description</label>
                    <textarea name="desc"><?= $projet->getDescription(); ?></textarea>

                    <?
                    if ($projet->getPublic())
                        echo "<label for='privacy'><input type='checkbox' name='privacy' id='privacy' value='0' style='display: none;'><span class='custom checkbox'></span> Make this project private</label>";
                    else
                        echo "<label for='privacy'><input type='checkbox' name='privacy' id='privacy' value='1'style='display: none;'><span class='custom checkbox'></span> Make this project public</label>";
                    ?>
                    <br>
                    <div class="button-group">
                        <button class="small button" type="submit">Apply modifications</button><button class="alert small button delp" id="<?= $projet->getId(); ?>" data-reveal-id="delpconf">Delete</button>
                    </div>
                </form>
            </div>
            <div class="small-6 columns">
                <h5 class="subheader">Members</h5><hr>
                <form action="?p=settingsp&id=<?= $projet->getId(); ?>" method="POST" id="modifyp" class="custom">
                    <label for="nextlead">Administrator</label>
                    <select name="nextlead" id="nextlead" class="medium">
                        <?
                        echo "<option value='" . $projet->getLead()->getId() . "'>" . $projet->getLead()->getPrenom() . ' ' . $projet->getLead()->getNom() . "</option>";
                        $users = $projet->getUsers();
                        foreach ($users as $key => $user) {
                            if ($user->getId() != $projet->getLead()->getId())
                                echo "<option value='" . $user->getId() . "'>" . $user->getPrenom() . ' ' . $user->getNom() . "</option>";
                        }
                        ?>
                    </select>

                    <button class="small button" type="submit" title="Transfer the administrator role to the above member">Replace administrator</button>
                </form>

                <label>Users</label>
                <ul class="pricing-table">
                    <?
                    foreach ($projet->getUsers() as $key => $user) {
                        echo '<li class="bullet-item"><a href="?p=member&id=' . $user->getId() . '" id="uid' . $user->getId() . '">' . $user->getPrenom() . ' ' . $user->getNom() . '</a>' . ($projet->isLead($user->getId()) ? "" : "<a href='" . $user->getId() . "' class='rmuser' id='del" . $user->getId() . "'><img src='img/delete.png' class='removeuser' alt='Delete' border='0' align='absmiddle' style='margin-left:5px;' title='Remove this person from this project'/></a> ") . "</li>";
                    }
                    ?>
                    <li class="cta-button"><button class="small button" id="invitebutton" data-reveal-id="inviteuser">Invite another person to join this project</button></li>
                </ul>
            </div>

            <div id="inviteuser" class="reveal-modal medium">
                <h4 class="subheader">Invite someone to the project</h4>
                <span class="success label validateTips"></span>
                <br><br>
                <form>
                    <div class="cf">
                        <div class="small-4 columns">
                            <label for="fname">First name</label>
                            <input type="text" name="fname" id="fname"/>
                        </div>
                        <div class="small-4 columns">
                            <label for="lname">Last name*</label>
                            <input type="text" name="lname" id="lname"/>
                        </div>
                        <div class="small-4 columns">
                            <label for="email">Email*</label>
                            <input type="text" name="email" id="email"/>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="small button" id="invitor">Invite</button>
                    </div>
                </form>
                <small>(*): required fields.</small>
                <a class="close-reveal-modal">&#215;</a>
            </div>
            <div id="delpconf" class="reveal-modal small text-center" data-project="">
                <div class="small-8 columns">
                    All data of this project will be lost.<br>Are you sure you want to delete this project?
                </div>
                <div class="small-4 columns">
                    <button class="alert small button" id="projectremover">Delete</button>
                </div>
                <a class="close-reveal-modal">&#215;</a>
            </div>
            <?
        }
        else
            echo "You cannot acces this page as you are not the administrator.";
    }
    else
        echo "<table class='warning'><tr><td class='warning'>Ce projet n'est pas public et vous n'y avez pas accès.</td></tr></table>";
    ?>
</div>

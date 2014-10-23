<div class="row">
    <?php
    $id = 0;
    if (isset($_GET['id']))
        $id = intval($_GET['id']);

    $projet = Projet::getP($db, $id);
    $result_dir = $res = $projet->getRepertoire();

    if ($projet->getPublic() != 1 && (!isset($membre) || !$projet->canAccess($membre->getId())))
        Navigate::redirectMessage("login", "You do not have access to this project. Maybe your account has been disconnected.", 2);

    echo "<h4 class='subheader'>Project: " . $projet->getTitre() . "</h4><hr>";
    include (HEREPATH . 'includes/project_menu.php');
    
    if (isset($membre) && $projet->canAccess($membre->getId())) {

        $message = isset($_POST['message']) ? $_POST['message'] : null;
        if ($message != '') {
            $tosend = new Historique(array('db' => $db, 'user_id' => $membre->getId(), 'proj_id' => $projet->getId(), 'description' => $message));
            $tosend->add("Messages");
            Navigate::redirect("forump", $projet->getId());
        }

        ?>
        <script>
            $(document).ready(function() {
                /*$( 'textarea.editor' ).ckeditor(function() { }, {
                    toolbar:
                        [
                        ['Source','-','Preview','-','Templates'],
                        ['Styles'],
                        ['Bold', 'Italic', 'Underline', '-', 'NumberedList', 'BulletedList'],
                        ['Link', 'Unlink'], 
                        ['Undo', 'Redo', '-', 'SelectAll'],
                        ['Maximize', 'ShowBlocks']
                    ],
                    coreStyles_bold: { element : 'b', overrides : 'strong' }
                });*/
            });
        </script>

        <?
        }
        if ($projet->getPublic() == 1 || (isset($membre) && $projet->canAccess($membre->getId()))) {
            $forum = Historique::getList($db, $projet->getId(), "Messages");
            if (count($forum) == 0) {
                echo "<p>There is no message in the forum</p>";
            } else {
                ?>
            <table width="100%" class="forum">
            <?
                foreach ($forum as $key => $value) {
                    $author = Utilisateur::getM($db, $value->getUser_id());
                    ?>
                    <tr>
                        <td width="150" style="border-right:1px solid #dddddd;" align="center"><? if ($author->getAvatar() != '') { ?><div class="th"><img src="avatars/<?= $author->getAvatar(); ?>" alt="Profile pic"></div><? } ?><br><br>
                        <? echo $author->getPrenom() . ' ' . $author->getNom(); ?><br><small><? echo date('d-m-Y H:i', strtotime($value->getDate())); ?></small></td>
                        <td><? echo $value->getDescription(); ?></td>
                    </tr>
                    <?
                }
            }
            ?>
            </table>
        <? 
        }
        if (isset($membre) && $projet->canAccess($membre->getId())) {
        ?>
        <form method="post" action ="?p=forump&id=<?= $projet->getId(); ?>">
            <textarea class="editor ckeditor" name="message"></textarea>
            <button class="button"><span class="icon comment"></span>Send</button>
        </form>
        <?
        }
    ?>
</div>

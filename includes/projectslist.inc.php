<?php
$projects = Projet::getList($db, $membre->getId());
?>
<script>
    $(document).ready(function() {
        changeTitle("CompPhy - My projects");
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
        });
        $(".invited").click(function() {
            var a;
            if($(this).hasClass('success'))
                a = "true";
            else 
                a = "false";
            $(this).answerInvite(a);
            return false;
        });
    })
</script>
<div class="row">
    <div id="content">
        <div id="lefts">
            <div id="invitedin">
            <?php
            $listinvite = $membre->getInvitations();
            if (count($listinvite) != 0) {
                echo "<h4>I am invited in</h4><hr>";
                foreach ($listinvite as $key => $iproject) {
                    ?>
                    <div class="panel" id="iproject<?=$iproject->getId();?>">
                        <div class="row">
                            <div class="small-4 columns">
                                <h5><? echo $iproject->getTitre(); ?></h5>
                            </div>
                            <div class="small-2 columns">
                                <small><?= date('d-m-Y H:i', strtotime($iproject->getCreationDate())); ?></small>
                            </div>
                            <div class="small-4 columns">
                                <ul class="button-group even-2">
                                    <li><a href="index.php?id=<?php echo $iproject->getId(); ?>&p=project" class="small success button invited" title="<?= $iproject->getId(); ?>">Accept</a></li>
                                    <li><a class="small alert button invited" href="#" title="<?= $iproject->getId(); ?>">Decline</a></li>
                                </ul>
                            </div>
                        </div>
                            <p><?php echo $iproject->getDescription(); ?></p>
                    </div>
                    <?php
                }
            }
            ?>
            </div>
            <h4>My projects</h4>
            <hr>
            <div id="projectlist">
                <?php
                if (sizeof($projects) == 0)
                    echo "<div class='noP'>You have no project yet. You can create one by clicking <a href='?p=new'>here</a></div>";
                foreach ($projects as $project) {
                    ?>
                    <div class="panel">
                        <div class="row">
                            <div class="small-4 columns">
                                <h5><a href="index.php?id=<? echo $project->getId(); ?>&p=project" class="projectlink"><? echo $project->getTitre(); ?></a></h5>
                            </div>
                            <div class="small-2 columns">
                                <small><?= date('d-m-Y H:i', strtotime($project->getCreationDate())); ?></small>
                            </div>
                            <div class="small-4 columns">
                                <ul class="button-group even-3">
                                    <li><a href="index.php?id=<? echo $project->getId(); ?>&p=project" class="tiny success secondary button">Access</a></li>
                                    <? if ($project->isLead($membre->getId())) { ?>
                                        <li><a href="index.php?id=<? echo $project->getId(); ?>&p=settingsp" class="tiny secondary button">Settings</a></li>
                                        <li><a class="tiny alert button delp" href="#" id="<?= $project->getId(); ?>"  data-reveal-id="delpconf">Delete</a></li>
                                    <? } ?>
                                </ul>
                            </div>
                        </div>
                            <p><?php echo $project->getDescription(); ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
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
    </div>
</div>
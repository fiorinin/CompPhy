<script>
$(document).ready(function() {
    $('.disabled').tipsy({gravity : "sw", fade : true});
    $('.toggling').click(function() { 
        $(this).parent().find('.toToggle').slideToggle('slow');
    });
    $("#blue").click(function() { $(this).updateColor('#26a2f1') });
    $("#green").click(function() { $(this).updateColor('#60cd3a') });
    $("#yellow").click(function() { $(this).updateColor('#f1db26') });
    $("#orange").click(function() { $(this).updateColor('#f1ac26') });
    $("#red").click(function() { $(this).updateColor('#cd1919') });
    $("#brown").click(function() { $(this).updateColor('#7c3c19') });
    $("#purple").click(function() { $(this).updateColor('#c036e7') });
    $("#grey").click(function() { $(this).updateColor('#b0b0b0') });
})
</script>
<? $p = $_GET['p']; ?>
<div style="height:43px;">
    <ul id="menu">
        <li>
            <?php
             if ($projet)
              echo '<a href="' . HERE . '?p=project&id=' . $projet->getId() .'" '.($p == "project" ? "class='current'":"").'>Trees</a>';
             else
              echo '<a href="' . HERE .'" '.($p == "project" ? "class='current'":"").'>Trees</a>';
            ?>
        </li>
        <?
        if (isset($membre) && $projet && $projet->canAccess($membre->getId())) {
            echo "<li><a href='?p=forump&id=" . $projet->getId() . "' " . ($p == "forump" ? "class='current'":"") . ">Forum</a></li>";
            echo "<li><a href='?p=documents&id=" . $projet->getId() . "' " . ($p == "documents" ? "class='current'":"") . ">Documents</a></li>";
            if($projet->isLead($membre->getId()))
                echo "<li><a href='?p=settingsp&id=" . $projet->getId() . "' " . ($p == "settingsp" ? "class='current'":"") . ">Settings</a></li>";
            echo "<li class='last'><span>".$projet->getTitre() ."<span></li>";
        } 
        ?>
    </ul>
</div>
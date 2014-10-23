<? $p = $_GET['p']; ?>
<ul class="button-group">
    <li>
        <?php
        if ($projet)
            echo '<a href="' . HERE . '?p=project&id=' . $projet->getId() . '" ' . ($p == "project" ? "class='button active'" : "class='button secondary'") . '>Trees</a>';
        else
            echo '<a href="' . HERE . '" ' . ($p == "project" ? "class='button active'" : "class='button secondary'") . '>Trees</a>';
        ?>
    </li>
    <?
    //UPD if (isset($membre) && $projet && $projet->canAccess($membre->getId())) {
        echo "<li><a href='?p=forump&id=" . $projet->getId() . "' " . ($p == "forump" ? "class='button active'" : "class='button secondary'") . ">Forum</a></li>";
        echo "<li><a href='?p=documents&id=" . $projet->getId() . "' " . ($p == "documents" ? "class='button active'" : "class='button secondary'") . ">Documents</a></li>";
        if (isset($membre) && $projet->isLead($membre->getId()))
            echo "<li><a href='?p=settingsp&id=" . $projet->getId() . "' " . ($p == "settingsp" ? "class='button active'" : "class='button secondary'") . ">Settings</a></li>";
    //UPD }
    if($p == "project" && isset($membre)) {
    ?>
    <li><a href="#" class='button secondary' id="help">Help</a></li>
    <?php } ?>
</ul>
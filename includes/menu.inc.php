<?
$p = "";
$p = (!isset($_GET['p'])) ? "" : $_GET['p'];
?>
<nav class="top-bar">
    <ul class="title-area">
        <!-- Title Area -->
        <li class="name vertical-align">
            <h1>
                <a href="?p=home">
                    <!--<img src="img/compphy.png" width="130">-->
                    Comp<span>Phy</span>
                </a>
            </h1>
        </li>
        <!-- Remove the class "menu-icon" to get rid of menu icon. Take out "Menu" to just have icon alone -->
        <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
    </ul>

    <section class="top-bar-section">
        <!-- Left Nav Section -->
        <ul class="left">
            <li class="divider"></li>
            <?
            if (isset($membre)) {
                ?>
                <li class="divider"></li>

                <?
                echo '<li><a href="?p=projects" ' . ($p == "projects" ? "class='active'" : "") . '>My projects</a></li><li class="divider"></li>';
                echo '<li><a href="?p=new" ' . ($p == "new" ? "class='active'" : "") . '>New project</a></li><li class="divider"></li>';
                echo '<li><a href="?p=settings" ' . ($p == "settings" ? "class='active'" : "") . '>Account settings</a></li><li class="divider"></li>';
                echo '<li><a href="?p=disconnect">Log out</a></li><li class="divider"></li>';
            } else {
                echo '<li><a href="?p=home" ' . ($p == "" || $p == "home" ? "class='active'" : "") . '>Home</a></li><li class="divider"></li>';
                echo '<li class="has-dropdown"><a href="?p=login" ' . ($p == "login" ? "class='active'" : "") . '>Log in</a>';
                ?>
                <ul class="dropdown" id="loginDrop">
                    <li class="has-form">
                        <form action="?p=login" method="post">
                            <div class="row">
                                <input name="mail" type="text" placeholder="Enter your email address">
                            </div>
                            <div class="row">
                                <input name="password" type="password" placeholder="Enter your password">
                            </div>
                            <div class="row">
                                <button type="submit" class="success button postfix">Log in </button>
                            </div>
                            <div class="row collapse">&nbsp;</div>
                        </form>
                    </li>
                </ul>
            </li><li class="divider"></li>

        <?
        echo '<li><a href="?p=register" ' . ($p == "register" ? "class='active'" : "") . '>Register</a></li><li class="divider"></li>';
    }
    ?>
</ul>

<!-- Right Nav Section -->
<ul class="right">
    <?
    $allProjects = Projet::getList($db);
    $nbProjects = count($allProjects);
    if (!isset($membre)) {
        ?>
        <li class="li-text"><span class="radius secondary label"><?= $nbProjects; ?> projects are hosted on CompPhy.</span></li><li class="divider"></li>

        <?
    }
    echo '<li><a href="?p=userguide" ' . ($p == "userguide" ? "class='active'" : "") . '>User Guide</a></li><li class="divider"></li>';
    echo "<li><a href='?p=faq' " . ($p == "faq" ? "class='active'" : "") . ">FAQ</a></li><li class='divider'></li>";
    echo "<li><a href='?p=links' " . ($p == "links" ? "class='active'" : "") . ">References</a></li><li class='divider'></li>";
    echo "<li><a href='?p=contact' " . ($p == "contact" ? "class='active'" : "") . ">Contact us</a></li><li class='divider'></li>";
    if (isset($_SESSION['authAdminValue']) && $_SESSION['authAdminValue'] == APASS)
        echo "<li><a href='?p=adminATGC' " . ($p == "adminATGC" ? "class='active'" : "") . ">Admin</a></li><li class='divider'></li>";
    ?>
</ul>
</section>
</nav>

<?
if (isset($_SESSION['message']) && $_SESSION['message'] != '') {
    $message_level = $_SESSION['level'];
    $message_content = $_SESSION['message'];
    if ($message_content != "") {
        $class = 'alert-box ';
        if ($message_level == 0) {
            $class .= "secondary";
        }
        if ($message_level == 1) {
            $class .= "success";
        } elseif ($message_level == 2) {
            $class .= "alert";
        }
        unset($_SESSION['level']);
        unset($_SESSION['message']);
        if (strpos($message_content, "Welcome, ") === 0 || strpos($message_content, "You are now disconnected") === 0) {
            $class.=" vanish";
        }
        ?>

        <div class="container">
            <? if (!Navigate::fullWidth()) { ?>
                <div class="row">
                <? } ?>
                <div data-alert class="<?= $class; ?>"><?= $message_content; ?><a href="#" class="close">&times;</a></div>
                <? if (!Navigate::fullWidth()) { ?>
                </div>
                <?
            }
        }
    }
    ?>
</div>
<script>
    function assignVanish() {
        $(".vanish").fadeOut('slow');
    }
    $(function() {
        setTimeout(assignVanish, 3000);
    });
</script>
<?
?>

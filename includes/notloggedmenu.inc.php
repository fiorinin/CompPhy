<?php
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
            <?php
            echo '<li><a href="?p=home" ' . ($p == "" || $p == "home" ? "class='active'" : "") . '>Home</a></li><li class="divider"></li>';
            echo '<li class="has-dropdown"><a href="?p=login" ' . ($p == "login" ? "class='active'" : "") . '>Login</a>';
            ?>
            <ul class="dropdown" id="loginDrop">
                <li class="has-form">
                    <form action="?p=login" method="post">
                        <div class="row collapse">
                            <div class="small-5 columns">
                                <input name="mail" type="text" placeholder="Enter your email address">
                            </div>
                            <div class="small-offset-1 small-4 columns">
                                <input name="password" type="password" placeholder="Enter your password">
                            </div>
                            <div class="small-2 columns">
                                <button type="submit" class="success button postfix">Log in </button>
                            </div>
                        </div>
                        <div class="row collapse">&nbsp;</div>
                    </form>
                </li>
            </ul>
        </li><li class="divider"></li>

    <?php
    echo '<li><a href="?p=register" ' . ($p == "register" ? "class='active'" : "") . '>Register</a></li><li class="divider"></li>';
    ?>
</ul>

<!-- Right Nav Section -->
<ul class="right">
    <?
    $allProjects = Projet::getList($db);
    $nbProjects = count($allProjects);
    ?>
    <li class="li-text"><span class="radius secondary label"><?= $nbProjects; ?> projects are hosted on CompPhy.</span></li><li class="divider"></li>

    <?php
    echo '<li><a href="?p=userguide" ' . ($p == "userguide" ? "class='active'" : "") . '>User Guide</a></li><li class="divider"></li>';
    ?>

    <?php
    echo "<li><a href='?p=faq' " . ($p == "faq" ? "class='active'" : "") . ">FAQ</a></li><li class='divider'></li>";
    ?>

    <?php
    echo "<li><a href='?p=links' " . ($p == "links" ? "class='active'" : "") . ">References</a></li><li class='divider'></li>";
    ?>

    <?php
    echo "<li><a href='?p=contact' " . ($p == "contact" ? "class='active'" : "") . ">Contact us</a></li><li class='divider'></li>";
    ?>

    <?php
    if (isset($_SESSION['authAdminValue']) && $_SESSION['authAdminValue'] == APASS)
        echo "<li><a href='?p=admin' " . ($p == "admin" ? "class='active'" : "") . ">Admin</a></li><li class='divider'></li>";
    ?>
</ul>
</section>
</nav>

<?
if(isset($_GET['key']) && $_GET['key'] != '') {
    $randomkey = $_GET['key'];
    $message_level = $_SESSION['level'.$randomkey];
    $message_content = $_SESSION['message'.$randomkey];
    $class = 'alert-box ';
    if($message_level == 0) {
        $class .= "secondary";
    }
    if($message_level == 1) {
        $class .= "success";
    }
    elseif($message_level == 2) {
        $class .= "alert";
    }
    unset($_SESSION['level'.$randomkey]);
    unset($_SESSION['message'.$randomkey]);
?>
<div class="row">
    <div class="<?=$class;?>"><?=$message_content;?></div>
</div>
<?
}
?>
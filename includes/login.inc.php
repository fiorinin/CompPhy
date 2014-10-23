<?php
if (!isset($membre)) {
    if (isset($_POST['mail']) && isset($_POST['password'])) {
        $toConnect = new Utilisateur(array('db' => $db, 'mail' => $_POST['mail'], "password" => md5($_POST['password'])));
        if (Utilisateur::exists($toConnect->getMail(), $db)) {
            $membre = $toConnect->connect();
            if ($membre != null) {
                $membre->save();
                Navigate::redirectMessage("projects", "Welcome, ".$membre->getPrenom(). " " . $membre->getNom(), 1);
            }
            else
                Navigate::redirectMessage("login", "The login and the password do not match.", 2);
        }
        else
            Navigate::redirectMessage("login", "This member does not exist.", 2);
    }
    if (!isset($membre) || $membre == null) {
        ?>
        <div class="row">
            <div class="small-12">
                <h4>Log in</h4><hr>
                <div id="content">
                    <div id="lefts">
                        <div class="homeblock">
                            <h4 class="subheader">I have an account</h4>
                            <div class="panel">
                                <div class="row collapse">
                                    <form action="?p=login" method="post">
                                        <div class="small-5 columns">
                                            <input name="mail" type="text" class="text" placeholder="Enter your email adress"/>
                                        </div>
                                        <div class="small-4 small-offset-1 columns">
                                            <input name="password" type="password" class="text" placeholder="Enter your password"/>
                                        </div>
                                        <div class="small-2 columns">
                                            <button type="submit" class="success postfix">Log in </button>
                                        </div>
                                        <a href="?p=forgot" class="link">I forgot my password</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="homeblock">
                            <h4 class="subheader">I do not have an account</h4>
                            <div class="panel">
                                <a href="?p=register" class="button">Click here to register</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
	    <div class="small-12">
		<h6 class="subheader">Thanks for citing CompPhy:</h6>
	        <p><blockquote><i>"CompPhy: a web-based Collaborative Platform for Comparing Phylogenies"</i>, N. Fiorini, V. Lefort, F. Chevenet, V. Berry and A.-M. Arigon Chifolleau, submitted.</blockquote></p>
	    </div>
        </div>

        <?php
    }
}
?>

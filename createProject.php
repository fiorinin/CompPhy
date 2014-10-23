<?php
/**
 * Command line php script for creating a project on compphy with an email
 * //You must launch this script from the directory of the future project
 * Don't forget that the name of this directory must be compphy_date-time_random
 * 
 * Usage : php BINARIES_PATH/createProject -e "foo@bar.tld" -n "Analysis name"
 *         -p "project directory"
 *
 * Author : Nicolas Fiorini
 */


/********************************************************************************
* Initializing
********************************************************************************/

include_once '/data/http/www/html/atgc/compphy/utils.php';
set_INC ('');
//$db = new PDO('mysql:host='.HOST.';dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

function verbose($str) {
 //echo $str;
}

/********************************************************************************
* EOF Initializing
********************************************************************************/



/********************************************************************************
* Starting web-service client
********************************************************************************/


$params = getopt('e:n:p:');
if (isset($params['e']) && isset($params['n']) && isset($params['p'])) {
    $dirname = $params['p'];
    $mail = $params['e'];
    $analysis = $params['n'];
    $clearpass = null;
    verbose("$mail $analysis\n");

    if (preg_match('#^([\w.-]+)@[\w.-]+\.[a-zA-Z]{2,6}$#', $mail, $m)) {
        $nom = $m[1];
    } else { die("This email is not valid\n"); }

    if(Utilisateur::exists($mail, $db)) {
        $membre = Utilisateur::getM($db, $mail);
    }
    else {
        $data = array(
            "db" => $db,
            "nom" => $nom,
            "mail" => $mail
        );
        $membre = new Utilisateur($data);
        $membre->setPassword("");
        $membre->save();
    }

    //$dir = basename(getcwd());
    $dir = basename($dirname);
    verbose($dir."\n");
    
    $projet = new Projet(array("db" => $db,
                "titre" => $analysis,
                "description" => "",
                "statut" => 1,
                "main" => $membre->getId(),
                "repertoire" => $dir,
                "publict" => "0",
                "chef_id" => $membre->getId()
                    )
    );
    $projet->add();
    
//    $dirname = $res = EXECPATH.$dir."/";
    $dir = opendir($dirname); 
    while($file = readdir($dir)) {
        if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
            if(preg_match("/supertree.*\.nwk/i", $file)) {
                $basename = basename($file);
                $newick = "";
                $script = "";
                $annotation = "";

                $newick = file_get_contents($dirname.$file);
                if(is_file($dirname.$basename.".tds"))
                    $script = file_get_contents($dirname.$basename.".tds");
                if(is_file($dirname.$basename.".tdf"))
                    $annotation = file_get_contents($dirname.$basename.".tdf");

                $donnees = array('db' => $db,
                                 'nom' => "Supertree",
                                 'typet' => "2",
                                 'newick' => $newick,
                                 'script' => $script,
                                 'annotation' => $annotation,
                                 'image' => '',
                                 'miniature' => '',
                                 'actif' => 1,
                                 'proj_id' => $projet->getId());
                $arbre = new Arbre($donnees);
                $arbre->add();
                //$arbre->create($res, true);
                $arbre->create($dirname, true);
                unlink($dirname.$file);
                if(is_file($dirname.$basename.".tds"))
                    unlink($dirname.$basename.".tds");
                if(is_file($dirname.$basename.".tdf"))
                    unlink($dirname.$basename.".tdf");
            }
            elseif(preg_match("/^(\d+).*\.nwk/i", $file, $m)) {
                $basename = basename($file);
                $newick = "";
                $script = "";
                $annotation = "";

                $newick = file_get_contents($dirname.$file);
                if(is_file($dirname.$basename.".tds"))
                    $script = file_get_contents($dirname.$basename.".tds");
                if(is_file($dirname.$basename.".tdf"))
                    $annotation = file_get_contents($dirname.$basename.".tdf");

                $donnees = array('db' => $db,
                                 'nom' => "Unnamed",
                                 'typet' => "1",
                                 'newick' => $newick,
                                 'script' => $script,
                                 'annotation' => $annotation,
                                 'image' => '',
                                 'miniature' => '',
                                 'actif' => 1,
                                 'proj_id' => $projet->getId());
                $arbre = new Arbre($donnees);
                $arbre->add();
                //$arbre->create($res, true);
                $arbre->create($dirname, true);
                unlink($dirname.$file);
                if(is_file($dirname.$basename.".tds"))
                    unlink($dirname.$basename.".tds");
                if(is_file($dirname.$basename.".tdf"))
                    unlink($dirname.$basename.".tdf");
            }
        }
    }
    closedir($dir);
    // TODO : care about the rights of new files
    if($clearpass)
        echo "$mail::$clearpass";
    else
        echo "OK";
}

elseif ($argc > 0)
    die("Please enter '-e', '-n' and '-p' parameters to execute the script.\n");
else
    die("Invalid method. Please call this script with command line and args.\n");
?>

<?php
$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

if($id != 0) {
    $projet = Projet::getP($db, $id);
    $result_dir = $res = $projet->getRepertoire();
}

if(isset($projet) && isset($_GET['exe']) && $projet->getMain() == $membre->getId()) {
    /*
     * Kind of backend controller
     * Handles access for execution files : security, DB connexion, ... 
     * Get data from the form to dispatch it to exe files
     */

    $errors = "";
    $info = "";
    $valid = "";
    if ($_GET['exe'] == 'taxanames') {
        $taxanames = array();
        foreach ($_POST as $key => $value) {
            if(preg_match("#names_([\w\d\s]+)#", $key, $matched))
            if($value != "") {
                $taxanames[$matched[1]] = preg_replace("#[\s\'\"\[\)\;\.\:\,\’]#", "_", $value);
            }
        }
    }
    if ($_GET['exe'] == 'colorize') {
        $taxatocolor = array();
        if(!empty($_POST['taxa'])) {
            foreach ($_POST['taxa'] as $key => $value) {
                if($value != "") {
                    $color = $_POST['c_'.$value];
                    // Nom taxon => couleur
                    $taxatocolor[$value] = $color;
                }
            }
        }
    }
    if ($_GET['exe'] != 'treenames' && $_GET['exe'] != 'treenamesf' && $_GET['exe'] != 'swap' && $_GET['exe'] != 'autoswap') {
        $data = $_POST['trees'];
        if($data != null)
            $trees = Arbre::getListA($db, $data);
    }
    elseif($_GET['exe'] == 'treenames' || $_GET['exe'] == 'treenamesf')  {
        $wantednames = array();
        $data = array();
        if($_GET['exe'] == 'treenames') {
            foreach ($_POST as $key => $value) {
                if(preg_match("#trees_(\d{1,})#", $key, $matched)) {
                    if($value != "") {
                        $wantednames[$matched[1]] = $value;
                        $data[] = $matched[1];
                    }
                }
            }
            if(!empty($data))
                $trees = Arbre::getListA($db, $data);
        }
        else {
            if(isset($_FILES['names']['tmp_name']) && $_FILES['names']['tmp_name'] != '') {
                $names = file_get_contents($_FILES['names']['tmp_name']);

                $names_cat = explode("\n",$names);
                $genetrees = $names_cat[0];
                $supertrees = $names_cat[1];

                $gene_name = explode(',',$genetrees);
                $super_name = explode(',',$supertrees);

                $trees = Arbre::getSortedList($db, $projet->getId());
            }
        }
    }
    elseif ($_GET['exe'] == 'swap') {
        $rleaf = $_POST["taxa1"];
        $lleaf = $_POST["taxa2"];
        $side = $_GET["treeswap"];
        
        // Parsing des feuilles
        if (preg_match("/T1 EUL([^\s]+) L/i", $rleaf, $m1))
            $rleaf = $m1[1];
        if (preg_match("/T1 EUL([^\s]+) L/i", $lleaf, $m2))
            $lleaf = $m2[1];
    }
    $res = EXECPATH . $result_dir . '/';
}
else {
    $idsave = 0;
    if (isset($_GET['idsave']))
        $idsave = intval($_GET['idsave']);
    $message = Historique::getBySave($db, $idsave);
    $projet = Projet::getP($db, $message->getProj_id());
    //$result_dir = $res = $projet->getRepertoire();
}

if(isset($_GET['exe']) && $projet->getMain() == $membre->getId()) {
/* 
// Some tests...
foreach($taxatocolor as $key => $value)
    echo "$key ----> $value ";


foreach ($trees as $key => $value) 
    echo "Arbre : ".$value->getNom()."ID : ".$value->getId()."";
*/

    switch($_GET['exe']) {
        case 'treenames' :
            include 'exe/exe_name.php';
            break;
        case 'treenamesf' :
            include 'exe/exe_name.php';
            break;
        case 'taxanames' :
            include 'exe/exe_nametaxa.php';
            break;
        case 'restrict' :
            include 'exe/exe_restrict.php';
            break;
        case 'reroot' :
            include 'exe/exe_reroot.php';
            break;
        case 'remove' :
            include 'exe/exe_remove.php';
            break;
        case 'colorize' :
            include 'exe/exe_colorise.php';
            break;
        case 'physicist' :
            include 'exe/exe_physicist.php';
            break;
        case 'mrp' :
            include 'exe/exe_mrp.php';
            break;
        case 'display' :
            include 'exe/exe_display.php';
            break;
        case 'restore' :
            include 'exe/exe_restore.php';
            break;
        case 'removesvg' :
            include 'exe/exe_restore.php';
            break;
        case 'swap' :
            include 'exe/exe_swap.php';
            break;
        case 'autoswap' :
            include 'exe/exe_autoswap.php';
            break;
        case 'mast' :
            include 'exe/exe_mast.php';
            break;
    }
}
if($errors != "") 
    Navigate::redirectMessage("project", $errors, 2, $projet->getId());
if ($info != "") 
    Navigate::redirectMessage("project", $info, 3, $projet->getId());
if ($valid != "") 
    Navigate::redirectMessage("project", $valid, 1, $projet->getId());
if($errors == "" && $valid == "" && $info == "") 
    Navigate::redirect('project', $projet->getId());
?>

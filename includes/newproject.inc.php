<?php
$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

$projet = Projet::getP($db, $id);
$result_dir = $res = $projet->getRepertoire();

// remove invalid characters
$result_dir = preg_replace('/[^\w\-]/', '', $result_dir);

if ('' != $result_dir) {
    // "compile" directory
    $result_dir = EXECPATH . $result_dir . "/";
} elseif ($result_dir == '' && isset($_GET['folder']) == "true") {
    $errorCreation = false;
    $erreur = "";
    $now = date('Ymd-His');
    $res = "compphy_" . $now . "_" . genStr();
    $result_dir = EXECPATH . $res . "/";
    while (is_dir($result_dir)) {
        $res = $now . "_" . genStr();
        $result_dir = EXECPATH . $res . "/";
    }
    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $public = isset($_POST['public']) ? 1 : 0;

    if ($title != '' && $desc != '') {
        if ($_FILES['supertree_nwk']['tmp_name'] != '')
            exec("file -bi -- " . escapeshellarg($_FILES['supertree_nwk']['tmp_name']), $fileinfo1);
        if ($_FILES['tree_nwk']['tmp_name'] != '')
            exec("file -bi -- " . escapeshellarg($_FILES['tree_nwk']['tmp_name']), $fileinfo2);
        if (isset($fileinfo1) && !is_null($fileinfo1) && $fileinfo1[0] && substr($fileinfo1[0], 0, 10) != "text/plain") {
            $erreur .= "The collection 2 file is not a text file. Please check your file.";
            $errorCreation = true;
        }
        if (isset($fileinfo2) && $fileinfo2[0] && substr($fileinfo2[0], 0, 10) != "text/plain") {
            $erreur .= "The collection 1 file is not a text file. Please check your file.";
            $errorCreation = true;
        }
        if (!mkdir($result_dir)) {
            $erreur .= "Unable to create the temporary execution directory. $result_dir";
            $erreur .= "Please contact the server administrator. Operation aborted.";
            $errorCreation = true;
        } else {
            chmod($result_dir, 0774);
        }
        if (!mkdir($result_dir . 'uploads')) {
            $erreur .= "Unable to create the uploads directory in $result_dir";
            $erreur .= "Please contact the server administrator. Operation aborted.";
            $errorCreation = true;
        } else {
            chmod($result_dir . 'uploads', 0774);
        }
    } else {
        $erreur .= "You did not fill each field : fields marked by *.";
        $errorCreation = true;
    }
    if ($errorCreation === true)
        Navigate::redirectMessage("new", $erreur, 2);
    else {
        $projet = new Projet(array("db" => $db,
                    "titre" => $title,
                    "description" => $desc,
                    "statut" => 1,
                    "main" => $membre->getId(),
                    "repertoire" => $res,
                    "publict" => $public,
                    "chef_id" => $membre->getId()
                        )
        );
        $projet->add();
    }
}

// check if directory exists
if (('' != $result_dir)
        && file_exists($result_dir)
        && is_dir($result_dir)
        && is_readable($result_dir)) {

    $uploads = FALSE;

    // On a bien recu un arbre
    $supertree_sent = false;
    $tree_sent = false;
    if ((isset($_FILES['supertree_nwk']['tmp_name']) && $_FILES['supertree_nwk']['tmp_name'] != '')
            || (isset($_POST['left_tree']) && $_POST['left_tree'] != '' && isset($_POST['left_supertree']) && $_POST['left_supertree'] == '2' && $projet->getMain() == $membre->getId())
            || (isset($_POST['right_tree']) && $_POST['right_tree'] != '' && isset($_POST['right_supertree']) && $_POST['right_supertree'] == '2' && $projet->getMain() == $membre->getId())) {
        $supertree_sent = true;
    }
    if ((isset($_FILES['tree_nwk']['tmp_name']) && $_FILES['tree_nwk']['tmp_name'] != '')
            || (isset($_POST['left_tree']) && $_POST['left_tree'] != '' && isset($_POST['left_supertree']) && $_POST['left_supertree'] == '1' && $projet->getMain() == $membre->getId())
            || (isset($_POST['right_tree']) && $_POST['right_tree'] != '' && isset($_POST['right_supertree']) && $_POST['right_supertree'] == '1' && $projet->getMain() == $membre->getId())) {
        $tree_sent = true;
    }
    if(isset($_POST["tree_nwk_txt"]) && $_POST["tree_nwk_txt"] != '') {
        $tree_sent = true;
    }
    if(isset($_POST["supertree_nwk_txt"]) && $_POST["supertree_nwk_txt"] != '') {
        $supertree_sent = true;
    }
    
    $info_out = "";
    $errors_out = "";
    
    if ($tree_sent || $supertree_sent) {
        if ($tree_sent && $supertree_sent)
            $loop_nb = 2;
        else {
            $loop_nb = 1;
            $typevalue = $supertree_sent ? 2 : 1;
        }
        for ($i = 1; $i <= $loop_nb; $i++) {

            if ($loop_nb == 2) {
                $typevalue = $i == 1 ? 1 : 2;
                $hack = $i == 1 ? "" : "super";
            }
            else
                $hack = $supertree_sent ? "super" : "";

            $script = false;
            $annotation = false;

            $n_data = '';
            // Fichier d'arbre reçu
            if (isset($_FILES[$hack . 'tree_nwk']['tmp_name']) && $_FILES[$hack . 'tree_nwk']['tmp_name'] != '')
                $n_data = file_get_contents($_FILES[$hack . 'tree_nwk']['tmp_name']);

            // Arbre envoyé à gauche
            elseif (isset($_POST['left_tree']) && $_POST['left_tree'] != '')
                $n_data = $_POST['left_tree'];

            // Arbre envoyé à droite
            elseif (isset($_POST['right_tree']) && $_POST['right_tree'] != '')
                $n_data = $_POST['right_tree'];
            
            // Convert text inputs into files
            $newickFile = $result_dir . "input.nwk";
            $write = false;
            if ((isset($_POST['left_tree']) && $_POST['left_tree'] != '') || (isset($_POST['right_tree']) && $_POST['right_tree'] != '')) {
                file_put_contents($newickFile, $n_data); $write = true;
            }
            if(isset($_POST["tree_nwk_txt"]) && $_POST["tree_nwk_txt"] != '' && $typevalue == 1) {
                file_put_contents($newickFile, $_POST["tree_nwk_txt"]); $write = true;
            }
            if(isset($_POST["supertree_nwk_txt"]) && $_POST["supertree_nwk_txt"] != '' && $typevalue == 2) {
                file_put_contents($newickFile, $_POST["supertree_nwk_txt"]); $write = true;
            }

            // Check envoi
            $filetotest = $_FILES[$hack . 'tree_nwk']['tmp_name'];
            if ($write == true) {
                $filetotest = $newickFile;
            }
            if ((isset($_FILES[$hack . 'tree_nwk']['tmp_name']) && $_FILES[$hack . 'tree_nwk']['tmp_name'] != '') || (isset($write) && $write != false)) {
                exec("file -bi -- " . escapeshellarg($filetotest), $fileinfo);
                //exec("cd ".$result_dir."\nperl ".COMPPHYROOT."exe/TreeChecker.pl " . $filetotest . " > check.out");
                exec("cd " . $result_dir . "\nperl " . BINPATH . "TreeChecker.pl " . $filetotest . " > check.out");
                $file_out = file_get_contents($result_dir . "check.out");

                $errors_res = explode("||", $file_out);
                $type = $typevalue == 1 ? "Genetree" : "Supertree";
                if (sizeof($errors_res) > 0) {
                    if(preg_replace("/\r\n|\r|\n/", '<br>', $errors_res[0])  != "") {
                        $errors_out .= $type . " file:<br>" . preg_replace("/\r\n|\r|\n/", '<br>', $errors_res[0]);
                    }
                    if (sizeof($errors_res) > 1) {
                        if(preg_replace("/\r\n|\r|\n/", '<br>', $errors_res[1])  != "") {
                            $info_out .= $type . " file:<br>" .preg_replace("/\r\n|\r|\n/", '<br>', $errors_res[1]);
                        }
                        if ($errors_out == "" && sizeof($errors_res) > 2) {
                            $n_data = $errors_res[2];
                        }
                    }
                }
            }
            if ((isset($fileinfo) && !is_null($fileinfo) && $fileinfo[0] && substr($fileinfo[0], 0, 10) == "text/plain"
                    || isset($_POST['left_tree']) && $_POST['left_tree'] != ''
                    || isset($_POST['right_tree']) && $_POST['right_tree'] != '' )
                    && $errors_out == "") {

                // Création des objets selon le(s) arbre(s) recu(s)
                $treeobjects = array();
                $newtreedefaultscript = false;
                if ((isset($_FILES[$hack . 'tree_nwk']['tmp_name']) && $_FILES[$hack . 'tree_nwk']['tmp_name'] != '')||((isset($_POST["tree_nwk_txt"]) || $_POST["supertree_nwk_txt"]) && $write == true) && $errors_out == "") {
                    $nwkTrees = explode(";", $n_data);
                    // Suppression de la derniere case contenant ""
                    unset($nwkTrees[sizeof($nwkTrees) - 1]);
                    for ($j = 0; $j < sizeof($nwkTrees); $j++) {
                        $newtree = new Arbre(array("db" => $db, "nom" => 'Unnamed', "typet" => $typevalue, "newick" => trim($nwkTrees[$j]) . ';', "actif" => 1, "proj_id" => $projet->getId(), "script" => "t -x 20 -y 20 -interleaf 20\nesn -what x: -box 0 -fg blue -font {arial 5 normal}", "annotation" => '', "image" => '', "miniature" => ''));
                        $newtreedefaultscript = true;
                        $treeobjects[] = $newtree;
                    }
                } else if (!isset($_FILES[$hack . 'tree_nwk']['tmp_name']) || $_FILES[$hack . 'tree_nwk']['tmp_name'] == '') {
                    $newtree = Arbre::getA($db, $_POST['treenb']);
                    $newtree->setNewick($n_data);
                    $treeobjects[] = $newtree;
                }

                if(sizeof($treeobjects) <= MAXTREEPOST) {
                    // Récupération du script
                    $s_data = '';
                    if (isset($_FILES[$hack . 'tree_tds']['tmp_name']) && $_FILES[$hack . 'tree_tds']['tmp_name'] != '')
                        $s_data = file_get_contents($_FILES[$hack . 'tree_tds']['tmp_name']);

                    elseif (isset($_POST['left_script']) && $_POST['left_script'] != '' && $_POST['left_script'] != 'undefinded')
                        $s_data = $_POST['left_script'];

                    elseif (isset($_POST['right_script']) && $_POST['right_script'] != '' && $_POST['right_script'] != 'undefinded')
                        $s_data = $_POST['right_script'];

                    // Récupération de l'annotation
                    $a_data = '';
                    if (isset($_FILES[$hack . 'tree_tlf']['tmp_name']) && $_FILES[$hack . 'tree_tlf']['tmp_name'] != '')
                        $a_data = file_get_contents($_FILES[$hack . 'tree_tlf']['tmp_name']);

                    elseif (isset($_POST['left_annotation']) && $_POST['left_annotation'] != '' && $_POST['left_annotation'] != 'undefinded')
                        $a_data = $_POST['left_annotation'];

                    elseif (isset($_POST['right_annotation']) && $_POST['right_annotation'] != '' && $_POST['right_annotation'] != 'undefinded')
                        $a_data = $_POST['right_annotation'];


                    // Application du script et annotation aux objets
                    foreach ($treeobjects as $key => $value) {
                        if ($newtreedefaultscript == false || $s_data != '')
                            $value->setScript($s_data);
                    }

                    foreach ($treeobjects as $key => $value) {
                        $value->setAnnotation($a_data);
                    } 

                    $annotation = $a_data != '' ? true : false;
                    $script = $s_data != '' ? true : false;

                    // Envoi des données
                    foreach ($treeobjects as $key => $value) {
                        if (!isset($_POST['treenb']))
                            $value->add();
                        else
                            $value->update();
                        $value->create(EXECPATH . $projet->getRepertoire() . "/", !isset($_POST['treenb']));
                    }
                    $uploads = TRUE;
                } else {
                Navigate::redirectMessage("project", "You cannot add more than ".MAXTREEPOST." trees at once per collection.", 2);
                }
            } else {
                Navigate::redirectMessage("project", "An error happened. Please try again.", 2);
            }
        }
    }
    if ($uploads == TRUE && $projet) {
        if (!isset($info_out) || $info_out == "") {
            Navigate::redirect('project', $projet->getId());
        } else {
            Navigate::redirectMessage("project", str_replace("\n", "", $info_out), 3, $projet->getId());
        }
    } elseif ($errors_out != "") {
        Navigate::redirectMessage("project", str_replace("\n", "", $errors_out), 2, $projet->getId());
    } elseif ($projet) {
        Navigate::redirect('project', $projet->getId());
    }
}
?>
<script>
    $(document).ready(function(){
        $("#g_1a").click(function() { $("#g_1").toggle('slow'); return false; });
        $("#s_1a").click(function() { $("#s_1").toggle('slow'); return false; });
        changeTitle("CompPhy - New project");
    });
</script>
<div class="row">
    <h4>New project</h4><hr>
    <div id="content">
        <div id="lefts">
            <form method="POST" action="?p=new&folder=true" enctype="multipart/form-data" id="new" class="custom">
                <div class="panel">
                    <div class="row collapse">
                        <h3>About the project</h3>
                        <div class="small-5 columns">
                            <label>Project's title*</label>
                            <input type="text" name="title" id="title">
                        </div>
                        <div class="small-5 columns small-offset-2">
                            <label>Project's description*</label>
                            <input type="text" name="desc" id="desc">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="small-6 columns">
                        <div class="panel">
                            <h3>Collection 1</h3>
                            <label><a href="http://evolution.genetics.washington.edu/phylip/newicktree.html">Newick</a> file</label><input type="file" name="tree_nwk"/>
                            Manual tuning of the tree images  <a href='http://www.scriptree.org' target="_blank" data-tooltip class="icon-16 has-tip tip-top noradius" data-width="500" title="You can tune the tree images thanks to the Scriptree language. If you are not familiar with this tool you can just ignore it for the moment and come back to it later to personalize the display of some trees of your collection."><img align="absmiddle" alt="Help" src="img/help.png" border="0" width="16" /></a>
                            <a href='#' id='g_1a' class='foundicon foundicon-down-arrow'></a>
                            <div id="g_1" style="display:none;">
                                <hr>
                                <label>Script file</label><input type="file" name="tree_tds"/>
                                <label>Annotation file</label><input type="file" name="tree_tlf"/>
                            </div>
                        </div>
                    </div>

                    <div class="small-6 columns">
                        <div class="panel">
                            <h3>Collection 2 <img src="img/interrogation.png" alt="Help" data-tooltip data-width="500" class="has-tip" align="absmiddle" title="These trees will go in a different list than collection 1 trees, they are first meant to be a collection of more comprehensive trees, obtained by supermatrix or supertree analyses from gene trees of the first list. Of course you can indicate here instead a set of consensus trees, or another set of trees."></h3>
                            <label><a href="http://evolution.genetics.washington.edu/phylip/newicktree.html">Newick</a> file</label><input type="file" name="supertree_nwk">
                            Manual tuning of the tree images <a href='http://www.scriptree.org' target="_blank" data-tooltip class="icon-16 has-tip tip-top noradius" data-width="500" title="You can tune the tree images thanks to the Scriptree language. If you are not familiar with this tool you can just ignore it for the moment and come back to it later to personalize the display of some trees of your collection."><img align="absmiddle" alt="Help" src="img/help.png" border="0" width="16" /></a>
                            <a href='#' id='s_1a' class='foundicon foundicon-down-arrow'></a>
                            <div id="s_1" style="display:none;">
                                <hr>
                                <label>Script file</label><input type="file" name="supertree_tds">
                                <label>Annotation file</label><input type="file" name="supertree_tlf">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="small-12">
                    <p><a href="http://phylogeny.lirmm.fr" target="_blank"><img src="img/logo_phylogeny-fr.png" border="0" style="float:left;" alt="Phylogeny.fr" width="10%" heigt="10%"/></a>Do you happen to just have sequences and no phylogeny yet? Then go straight to the <a href="http://phylogeny.lirmm.fr" target="_blank">phylogeny.fr</a> website that will generate alignments and phylogenies from your sequences, then save these phylogenies on your computer and come back to CompPhy to create a project uploading these trees.</p>
                </div>
                <div class="panel">
                    <h3>Privacy <img src="img/interrogation.png" alt="Help" data-tooltip data-width="500" class="has-tip" align="absmiddle" title="A public project can be accessed from anyone without the need to be logged in. However, people still need the specific link to the project (permalink) which you will provide them. This greatly reduces the chance that non-informed people happen to visit your project." /></h3>

                    <label for="public"><input type="checkbox" name="public" value="1" id='public' style="display: none;"><span class="custom checkbox"></span> I want my project to be public</label>

                </div>
                <div class="row text-center">
                    <button type="submit" class="button">Create the project</button>
                </div>
                <small>(*): required fields.</small>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $("#new").validate({
            rules: {
                title: {
                    required: true,
                    rangelength: [1, 50]
                },
                desc: {
                    required: true,
                    rangelength: [1, 200]
                }
            },
            submitHandler: function(form) {
                $('#submitProject').foundation('reveal', 'open');
                form.submit();
            }
        });
    });
</script>
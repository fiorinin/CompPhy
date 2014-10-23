<?php
    $tds = "t -x 20 -y 20 \n";
    
    $leaf_c = !isset($_GET['k']) || $_POST['k'] == "leaf";
    $subtree_c = isset($_GET['k']) || $_POST['k'] == "subtree";
    
    $bgcol = false;
    if(isset($_POST['colorBackground']) && $_POST['colorBackground'] == "1") {
        $bgcol = true;
    }
    foreach($trees as $key => $value) {
        if(!preg_match("/^t.*/", $value->getScript()))
            $treescript = $tds;
        else {
            $treescript = $value->getScript();
            // Cas de la coloration de feuille
            if($leaf_c) {
                $treescript = preg_replace("/query\_newick -ql[^\n]+\n/", "", $treescript);
                $treescript = trim($treescript) . "\n";
            }
            // Cas de la coloration de subtree
            if($subtree_c && isset($_POST["opname"]) && isset($_POST["tname"])) {
                $treescript = preg_replace("/ssa -what[^\n]+\n/", "", $treescript);
                $treescript = trim($treescript) . "\n";
            }
        }
        $colorscript = '';
        $colorann = $value->getAnnotation();
        if($subtree_c && isset($_POST["opname"]) && isset($_POST["tname"])) {
            $op_name = preg_replace("#[\s\'\"\[\)\;\.\:\,\’]#", "_", $_POST['opname']);
            $colorscript .= "ssa -what {".$op_name."} -bg green -shape 06 -legend 1\n";
        }
        $indexcolors;
        foreach ($taxatocolor as $taxaname => $color) {
            if ($leaf_c) {
                $indexcolors[$color] .= $taxaname." ";
            }
            elseif ($subtree_c && isset($_POST["opname"]) && isset($_POST["tname"])) {
                $type_name = preg_replace("#[\s\'\"\[\)\;\.\:\,\’]#", "_", $_POST['tname']);
                $colorann .= $taxaname. " " . $op_name . " " . $type_name . "\n";
            }
            
        }
        if($leaf_c && !empty($indexcolors)) {
            foreach ($indexcolors as $color => $taxaname)
                // suppression des doublons
                $uniqueTaxa = join(" ", array_unique(explode(" ",$taxaname)));
              
                # Nico : $colorscript .= "query_newick -ql {".$taxaname."} -hi {-o {lfg ".($bgcol===true?"ss1":"sfg")."} -c ".$color." ".($bgcol===true?"-shrink 04":"")."}\n";
                # Vincent :
 		$colorscript .= "query_newick -ql {".$uniqueTaxa."} -hi {-o {".($bgcol===true?"sbg":"lfg sfg")."} -c ".$color." ".($bgcol===true?"-shape 01":"")."}\n";
        }
        $value->setAnnotation($colorann);
        $value->setScript($treescript.$colorscript);
        $value->update();
        $value->create($res);
    }
?>

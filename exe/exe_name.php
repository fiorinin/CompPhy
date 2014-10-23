<?php
if($_GET['exe'] == 'treenames') {
    foreach ($trees as $key => $value) {
        $value->setNom($wantednames[$value->getId()]);
        $value->update();
    }
}
else {
    foreach($trees['genetrees'] as $key => $tree) {
        if($gene_name[$key] != "") {
            $tree->setNom($gene_name[$key]);
            $tree->update();
        }
    }
    foreach($trees['supertrees'] as $key => $tree) {
        if($super_name[$key] != "") {
            $tree->setNom($super_name[$key]);
            $tree->update();
        }
    }
}
?>

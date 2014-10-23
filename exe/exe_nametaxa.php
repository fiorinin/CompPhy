<?php
foreach($trees as $key => $value) {
    foreach($taxanames as $actuel => $futur) {
        $newnewick = preg_replace("#".$actuel."#", $futur, $value->getNewick());
        $value->setNewick($newnewick);
    }
    $value->update();
    $value->create($res);
}
?>
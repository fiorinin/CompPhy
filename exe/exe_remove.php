<?php
    foreach($trees as $key => $value) {
        if(intval($projet->getMaing()) == intval($value->getId()))
            $projet->setMaing(0);
        if(intval($projet->getMaind()) == intval($value->getId()))
            $projet->setMaind(0);
        $projet->update();
        $value->trueDelete();
    }
?>

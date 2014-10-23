<?php
    echo "<div id='divgene' style='height:150px;overflow-x:auto;white-space:nowrap;overflow-y:hidden;width:100%;'>";

    if ($g_max >= 1 && $t == "gene") {
        $init = 0;
        foreach ($sortedList['genetrees'] as $key => $value) {
            if ($init==0) {
                $style_str = "style=\"border: 3px solid #7e9adb;\"";
                $img_class = "left";
            }
            elseif($init ==1){ 
                $style_str = "style=\"border: 3px solid #FF9900;\"";
                $img_class = "right";
            }
            else {
                $style_str = "style=\"border: 1px solid #000000;\"";
                $img_class = "default";
            }
            
	    echo "<div class=\"vignette\">";
            echo "<span style=\"font-size: 10pt;font-weight:bold;\">".$value->getNom()."</span>";
            echo "<img class=\"" . $img_class . " caption\" id=\"" . $value->getId() . "\" title=\"" . $value->getNom() . "\" alt=\"" . $value->getType() . "\" src=\"?p=getresult&id=" . $projet->getId() . "&amp;f=" . $value->getMiniature() . "\" " . $style_str . "/>";
            echo "";
            echo "</div>";
            
            $init++;
        }
    }
    elseif($s_max >= 1 && $t != "gene") {
        foreach ($sortedList['supertrees'] as $key => $value) {
            $style_str = "style=\"border: 1px solid #000000;\"";
            $img_class = "default";
	    echo "<div class=\"vignette\">";
            echo "<span style=\"font-size: 10pt;font-weight:bold;\">".$value->getNom()."</span>";
            echo "<img class=\"" . $img_class . " caption\" id=\"" . $value->getId() . "\" title=\"" . $value->getNom() . "\" alt=\"" . $value->getType() . "\" src=\"?p=getresult&id=" . $projet->getId() . "&amp;f=" . $value->getMiniature() . "\" " . $style_str . "/>";
            echo "";
            echo "</div>";
        }
    }
?>
</div>
<?php

abstract class Utils {

    public static function alStr($nb) {
        $string = "";
        $chaine = "abcdefghijklmnpqrstuvwxy0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        srand((double) microtime() * 1000000);
        for ($i = 0; $i < $nb; $i++)
            $string .= $chaine[rand() % strlen($chaine)];
        return $string;
    }

    public static function filter($str) {
        $search = array('@[éèêëÊË]@i', '@[àâäÂÄ]@i', '@[îïÎÏ]@i', '@[ûùüÛÜ]@i', '@[ôöÔÖ]@i', '@[ç]@i', '@[ ]@i', '@[^a-zA-Z0-9_]@');
        $replace = array('e', 'a', 'i', 'u', 'o', 'c', '_', '');
        return preg_replace($search, $replace, $str);
    }

    public static function foldersize($path, $value) {
        $total_size = 0;
        $files = scandir($path);

        foreach ($files as $t) {
            if (is_dir($path . "/" . $t)) {
                if ($t != "." && $t != ".." && (preg_match("/^compphy.*/", $t) && $value == 0 || !preg_match("/^compphy.*/", $t) && $value != 0)) {
                    $size = Utils::foldersize($path . "/" . $t, $value + 1);
                    $total_size += $size;
                }
            } else {
                $size = filesize($path . "/" . $t);
                $total_size += $size;
            }
        }
        return $total_size;
    }

    public static function format_size($size, $round) {
        //Size must be bytes!
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i = 0; $size > 1024 && $i < count($sizes) - 1; $i++)
            $size /= 1024;
        return round($size, $round) . $sizes[$i];
    }

    public static function CalcFullDatabaseSize($db) {
        $dbsize = 0;
        $result = $db->query("SHOW TABLE STATUS");
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $dbsize += $row["Data_length"] + $row["Index_length"];
        }
        return $dbsize;
    }

    public static function randomKey($size = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $size; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    public static function displayCollections($sortedList,$both=true) {
        if(isset($sortedList['genetrees']) || isset($sortedList['supertrees'])){
            echo "<small><kbd>Shift</kbd>+<kbd>Click</kbd> to select multiple trees</small>";
        }
        if (isset($sortedList['genetrees'])) {
            echo "<p>Collection 1 list:</p>";
            $quarternb = intval(sizeof($sortedList['genetrees']) / 4);
            $count = 0;
            echo "<div class='small-3 columns'>";
            foreach ($sortedList['genetrees'] as $key => $value) {
                if ($quarternb != 0 && $count % $quarternb == 0 && $count != 0) {
                    echo "</div><div class='small-3 columns'>";
                }
                echo "<label for='restrict_ck_g_" . $value->getId() . "' class='selectable'><input type='checkbox' id='restrict_ck_g_" . $value->getId() . "' name='trees[]' value='" . $value->getId() . "' style='display:none' /><span class='custom checkbox'></span> " . $value->getNom() . "</label>";
                $count++;
            }
            echo "</div><div class='cf'></div><p></p>";
        }
        if (isset($sortedList['supertrees']) && $both) {
            echo "<p>Collection 2 list:</p>";
            $quarternb = intval(sizeof($sortedList['supertrees']) / 4);
            $count = 0;
            echo "<div class='small-3 columns'>";
            foreach ($sortedList['supertrees'] as $key => $value) {
                if ($quarternb != 0 && $count % $quarternb == 0 && $count != 0) {
                    echo "</div><div class='small-3 columns'>";
                }
                echo "<label for='restrict_ck_s_" . $value->getId() . "' class='selectable'><input type='checkbox' id='restrict_ck_s_" . $value->getId() . "' name='trees[]' value='" . $value->getId() . "' style='display:none' /><span class='custom checkbox'></span> " . $value->getNom() . "</label>";
                $count++;
            }
            echo "</div><div class='cf'></div><p></p>";
        }
        echo "<p><a class='selectora'>Select all trees</a> | <a class='deselectora''>Deselect all trees</a></p>";
    }

}

?>

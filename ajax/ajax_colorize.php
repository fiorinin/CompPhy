<?php
header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<list>";

include('utils.php');
//include('config.php');
set_INC ('compphy/ajax/');

//$db = new PDO('mysql:host=localhost;dbname='.DB, USER, PASS);
$db = new PDO('mysql:host='.DBHOST.';port='.DBPORT.';dbname='.DB, USER, PASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);

$id = 0;
if (isset($_GET['id']))
    $id = intval($_GET['id']);

$arbre = Arbre::getA($db, $id);
$projet = Projet::getP($db, $arbre->getProj_id());

$nwk = '';
$script = '';
$annotation = '';

if($arbre && $projet) {
    $tab = explode("\n", $arbre->getScript());
    for ($i=0;$i<=sizeof($tab);$i++)
    {
        if (preg_match('#(query_newick -ql \{)([\_\.\-\ a-zA-Z0-9]*)(\} -hi \{-o \{[^\}]*\} -c )(\#[a-zA-Z0-9]*)([^\}]*\})#',$tab[$i],$data)) {
            $taxanames = $data[2];
            $colorhexe = $data[4];
            $taxatable = explode(" ", $taxanames);
            foreach ($taxatable as $taxon) {
                if($taxon != "")
                    echo "<item id=\"".$taxon."\">".$colorhexe."</item>";
            }
        }
    }
}

echo "</list>";

?>

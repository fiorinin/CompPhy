<?php

class Arbre {
    
    private $db;
    
    private $id, $nom, $typet, $newick, $script, $annotation, $image, $miniature, $actif, $proj_id, $order;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function getType() {
        return $this->typet;
    }

    public function setType($type) {
        $this->typet = $type;
    }

    public function getNewick() {
        return $this->newick;
    }

    public function setNewick($newick) {
        $this->newick = $newick;
    }

    public function getScript() {
        return $this->script;
    }

    public function setScript($script) {
        $this->script = $script;
    }

    public function getAnnotation() {
        return $this->annotation;
    }

    public function setAnnotation($annotation) {
        $this->annotation = $annotation;
    }

    public function getImage() {
        return $this->image;
    }

    public function getImageR() {
        $pic = $this->image;
        $picR = str_replace(".svg", "_R.svg", $pic);
        return $picR;
        
    }

    public function setImage($image) {
        $this->image = $image;
    }

    public function getMiniature() {
        return $this->miniature;
    }

    public function setMiniature($miniature) {
        $this->miniature = $miniature;
    }

    public function getActif() {
        return $this->actif;
    }

    public function setActif($actif) {
        $this->actif = $actif;
    }

    public function getProj_id() {
        return $this->proj_id;
    }

    public function setProj_id($proj_id) {
        $this->proj_id = $proj_id;
    }
    
    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
    }

    
    public function __construct(array $donnees) {
        $this->hydrate($donnees);
    }
    
    public function hydrate(array $donnees) {
        foreach ($donnees as $key => $value) {
            switch ($key) {
                case 'db' :
                    $this->db = $value;
                    break;
                case 'id' :
                    $this->id = (int) $value;
                    break;
                case 'typet' :
                    $this->typet = (int) $value;
                    break;
                case 'nom' :
                    $this->nom = (string) $value;
                    break;
                case 'newick' :
                    $this->newick = (string) $value;
                    break;
                case 'script' :
                    $this->script = (string) $value;
                    break;
                case 'annotation' :
                    $this->annotation = (string) $value;
                    break;
                case 'image' :
                    $this->image = (string) $value;
                    break;
                case 'miniature' :
                    $this->miniature = (string) $value;
                    break;
                case 'actif' :
                    $this->actif = (int) $value;
                    break;
                case 'proj_id' :
                    $this->proj_id = (int) $value;
                    break;
                case 'ordernb' :
                    $this->order = (int) $value;
                    break;
            }
        }
    }
    
    public function add() {
        $numtree = sizeof($this->getListAll($this->db, $this->proj_id));
        if($numtree < MAXTREES) {
            $q = $this->db->prepare("INSERT INTO Arbres 
                                     SET typet = :type,
                                         nom = :nom, 
                                         newick = :newick,
                                         script = :script,
                                         annotation = :annotation,
                                         image = :image,
                                         miniature = :miniature,
                                         actif = :actif,
                                         proj_id = :proj_id");

            $q->bindValue(':type', $this->typet, PDO::PARAM_INT);
            $q->bindValue(':nom', $this->nom);
            $q->bindValue(':newick', $this->newick);
            $q->bindValue(':script', $this->script);
            $q->bindValue(':annotation', $this->annotation);
            $q->bindValue(':image', $this->image);
            $q->bindValue(':miniature', $this->miniature);
            $q->bindValue(':actif', $this->actif, PDO::PARAM_INT);
            $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);

            $q->execute();

            $this->hydrate(array(
                'id' => $this->db->lastInsertId()
            )); 
        } else {
            Navigate::addMessage("You have reached the maximum number of trees (".MAXTREES.") for this project.", 2);
        }
        $numtaxa = sizeof($this->getTaxaList($this->db, $this->proj_id));
        if($numtaxa > MAXTAXA) {
            Navigate::addMessage("You have reached the maximum number of taxa (".MAXTAXA.") for this project.", 2);
            $this->trueDelete();
        }
        
        $projet = Projet::getP($this->db, $this->proj_id);
        $projet->update();
    }
    
    public function update() {
        $q = $this->db->prepare('UPDATE Arbres 
                                 SET typet = :type,
                                     nom = :nom,
                                     newick = :newick,
                                     script = :script,
                                     annotation = :annotation,
                                     image = :image,
                                     miniature = :miniature,
                                     actif = :actif,
                                     proj_id = :proj_id,
                                     ordernb = :order
                                     WHERE id = :id');
        
        $q->bindValue(':type', $this->typet, PDO::PARAM_INT);
        $q->bindValue(':nom', $this->nom);
        $q->bindValue(':newick', $this->newick);
        $q->bindValue(':script', $this->script);
        $q->bindValue(':annotation', $this->annotation);
        $q->bindValue(':image', $this->image);
        $q->bindValue(':miniature', $this->miniature);
        $q->bindValue(':actif', $this->actif, PDO::PARAM_INT);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->bindValue(':order', $this->order, PDO::PARAM_INT);
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        
        $q->execute();
        
        $projet = Projet::getP($this->db, $this->proj_id);
        $projet->update();
    }
    
    public function delete() {
        $q = $this->db->prepare('UPDATE Arbres 
                                 SET actif = 0
                                 WHERE id = :id');
        
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
        
        $projet = Projet::getP($this->db, $this->proj_id);
        $projet->update();
    }
    
    public function trueDelete() {
        // Delete from DB
        $q = $this->db->prepare('DELETE FROM Arbres WHERE id = :id');
        
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
        
        // Delete files
        $projet = Projet::getP($this->db, $this->proj_id);
        unlink(EXECPATH . $projet->getRepertoire() . "/" . $this->getImage());
        unlink(EXECPATH . $projet->getRepertoire() . "/" . $this->getImageR());
        unlink(EXECPATH . $projet->getRepertoire() . "/" . $this->getMiniature());
        $projet->update();
    }
    
    public static function getList($db, $idprj) {
        $q = $db->prepare('SELECT * FROM Arbres WHERE proj_id = :id_prj AND actif = 1 ORDER BY ordernb');
        $q->execute(array(':id_prj' => $idprj));
        
        $trees = array();
        foreach($q->fetchAll() as $qtree){
            $qtree['db'] = $db;
            $tree = new Arbre($qtree);
            $trees[] = $tree;
        }
        return $trees;
    }
    
    public static function getListAll($db, $idprj) {
        $q = $db->prepare('SELECT * FROM Arbres WHERE proj_id = :id_prj ORDER BY ordernb');
        $q->execute(array(':id_prj' => $idprj));
        
        $trees = array();
        foreach($q->fetchAll() as $qtree){
            $qtree['db'] = $db;
            $tree = new Arbre($qtree);
            $trees[] = $tree;
        }
        return $trees;
    }
    
    public static function getSortedList($db, $idprj) {
        $trees = Arbre::getList($db, $idprj);
        $sortedTrees = array();
        foreach($trees as $key => $value) {
            if($value->getType() == 1) {
                $sortedTrees['genetrees'][] = $value;
            }
            else {
                $sortedTrees['supertrees'][] = $value;
            }
        }
        return $sortedTrees;
    }
    
    public static function CountTrees($db, $idprj, $type=0) {
        $typeparam = $type != 0 ? " AND typet = ".$type : "";
        $q = $db->query('SELECT * FROM Arbres WHERE actif = 1 AND proj_id = '.$idprj. $typeparam);
        $retour = $q->fetchAll();
        return count($retour);
    }
    
    public static function getA($db, $info) {
        $q = $db->prepare('SELECT * FROM Arbres WHERE actif = 1 AND id = :id');
        $q->execute(array(':id' => $info));
        
        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $arbre = new Arbre($params);
        
        return $arbre;
    }
    
    public static function getListA($db, array $info) {
        $ids     = $info;
        $inQuery = implode(',', array_fill(0, count($ids), '?'));

        $q = $db->prepare(
            'SELECT *
             FROM Arbres
             WHERE id IN(' . $inQuery . ')'
        );

        // bindvalue is 1-indexed, so $k+1
        foreach ($ids as $k => $id)
            $q->bindValue(($k+1), $id);

        $q->execute();
        
        $trees = array();
        foreach($q->fetchAll() as $qtree){
            $qtree['db'] = $db;
            $tree = new Arbre($qtree);
            $trees[] = $tree;
        }
        return $trees;
    }
    
    public static function getTaxaList($db, $idprj) {
        $trees = Arbre::getList($db, $idprj);
        
        $taxaList = array();
        foreach($trees as $key => $value) {
            //$templist = preg_replace("#[\;\(\)\:\.]#", ",", $value->getNewick());
            $templist = trim(preg_replace('/[\)\:]+[0-9.eE\-+]+/', ',', $value->getNewick()));
            $templist = preg_replace("#[\;\(\)\:\.]#", ",", $templist);
            $singleTreeTaxaList = explode(",", $templist);
            foreach($singleTreeTaxaList as $key => $value) {
                if ($value == "" || preg_match("#^\d+$#", $value) || preg_match("#^\s+$#", $value))
                    unset ($singleTreeTaxaList[$key]);
                else
                    $taxaList[$value] = 1;
            }
        }
        ksort($taxaList);
        return $taxaList;
    }
    
    public function getOneTaxaList() {
        $taxaList = array();
        $templist = trim(preg_replace('/[\)\:]+[0-9.eE\-+]+/', ',', $this->newick));
        $templist = preg_replace("#[\;\(\)\:\.]#", ",", $templist);
        $singleTreeTaxaList = explode(",", $templist);
        foreach($singleTreeTaxaList as $key => $value) {
            if ($value == "" || preg_match("#^\d+$#", $value) || preg_match("#^\s+$#", $value))
                unset ($singleTreeTaxaList[$key]);
            else
                $taxaList[$value] = 1;
        }
        ksort($taxaList);
        return $taxaList;
    }
    
    public function create($result_dir, $new = false) {
        if(!$this->getScript()) {
            $this->setScript("t -x 20 -y 20 -interleaf 20\nesn -what x: -box 0 -fg blue -font {arial 5 normal}");
        }
        // Get transform parameters if a picture is already created for this tree
        $previous = false;
        $previousR = false;
        if($this->getImage() != null && file_exists($result_dir . $this->getImage())) {
            $image = $result_dir.$this->getImage();

            $svg = new SimpleXMLElement(file_get_contents($image));
            $params = $svg->g["transform"];
            $paramsw = $svg->g["width"];
            $paramsh = $svg->g["height"];
            $previous = true;
        }
        if($this->getImageR() != null && file_exists($result_dir . $this->getImageR())) {
            $imageR = $result_dir.$this->getImageR();

            $svgR = new SimpleXMLElement(file_get_contents($imageR));
            $paramsR = $svgR->g["transform"];
            $paramsRw = $svgR->g["width"];
            $paramsRh = $svgR->g["height"];
            $previousR = true;
        }
        
        file_put_contents($result_dir . 'treetemp.nwk', $this->getNewick());
        if ($this->getScript()) {
            $scriptNormal = $this->getScript();
            file_put_contents($result_dir . 'scripttemp.nwk', $scriptNormal);
        }
        if ($this->getAnnotation())
            file_put_contents($result_dir . 'annotationtemp.nwk', $this->getAnnotation());

        $s_file = ($this->getScript() == true) ? '-s ' . $result_dir . 'scripttemp.nwk ' : '';
        $a_file = ($this->getAnnotation() == true) ? '-a ' . $result_dir . 'annotationtemp.nwk ' : '';

        exec('php ' . WSPATH . 'scriptree-client.php -t ' . $result_dir . 'treetemp.nwk ' . $s_file . ' ' . $a_file . ' -f ' . $result_dir . ' -n treepict_' . $this->getId() . ' -e svg >' . $result_dir . 'scriptree-stdout.txt 2>' . $result_dir . 'scriptree-stderr.txt');
        exec('convert -resize 80x80 ' . $result_dir . 'treepict_' . $this->getId() . '.svg ' . $result_dir . 'treepict_' . $this->getId() . '_mini.png');
        
        if($this->getScript()) {
            unlink($result_dir . 'scripttemp.nwk');
            $scriptRenverse = explode("\n", $scriptNormal);
            $scriptRenverse[0] = trim($scriptRenverse[0])." -orientation ew";
            $renverse_final = implode("\n", $scriptRenverse);
            file_put_contents($result_dir . 'scripttemp.nwk', $renverse_final);
            exec('php ' . WSPATH . 'scriptree-client.php -t ' . $result_dir . 'treetemp.nwk ' . $s_file . ' ' . $a_file . ' -f ' . $result_dir . ' -n treepict_' . $this->getId() . '_R -e svg >' . $result_dir . 'scriptree-stdout.txt 2>' . $result_dir . 'scriptree-stderr.txt');
        }
        
        if(file_exists($result_dir . $this->getImage()) && $previous) {
            $image = $result_dir . $this->getImage();

            $svg = new SimpleXMLElement(file_get_contents($image));
            $svg->g["transform"] = $params;
            if($paramsw != "")
                $svg->g["width"] = $paramsw;
            if($paramsh != "")
                $svg->g["height"] = $paramsh;
            $svg->asXML($image);
        }
        if(file_exists($result_dir . $this->getImageR()) && $previousR) {
            $imageR = $result_dir . $this->getImageR();
            
            $svgR = new SimpleXMLElement(file_get_contents($imageR));
            $svgR->g["transform"] = $paramsR;
            if($paramsRw != "")
                $svgR->g["width"] = $paramsRw;
            if($paramsRh != "")
                $svgR->g["height"] = $paramsRh;
            $svgR->asXML($imageR);
        }
        
        if($new) {
            $this->setImage("treepict_".$this->getId().".svg");
            $this->setMiniature("treepict_".$this->getId()."_mini.png");
            if($this->nom == "Unnamed" || $this->nom == "")
                $this->setNom("tree_".$this->getId());
            $this->setOrder($this->getId());
            $this->update();
        }
        
        $this->update();
        $projet = Projet::getP($this->db, $this->getProj_id());
        $projet->update();

        unlink($result_dir . 'treetemp.nwk');
        if ($this->getScript())
            unlink($result_dir . 'scripttemp.nwk');
        if ($this->getAnnotation())
            unlink($result_dir . 'annotationtemp.nwk');
    }
}

?>

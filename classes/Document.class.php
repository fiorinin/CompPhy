<?php

class Document {
    
    private $db;

    private $id, $titre, $sender, $adresse, $proj_id, $tree_id, $timestamp;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getTitre() {
        return $this->titre;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }
    
    public function getSender() {
        return $this->sender;
    }

    public function setSender($sender) {
        $this->sender = $sender;
    }

    public function getAdresse() {
        return $this->adresse;
    }

    public function setAdresse($adresse) {
        $this->adresse = $adresse;
    }

    public function getProj_id() {
        return $this->proj_id;
    }

    public function setProj_id($proj_id) {
        $this->proj_id = $proj_id;
    }
    
    public function getTree_id() {
        return $this->tree_id;
    }

    public function setTree_id($tree_id) {
        $this->tree_id = $tree_id;
    }
    
    public function getTimestamp() {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
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
                case 'titre' :
                    $this->titre = (string) $value;
                    break;
                case 'sender' :
                    $this->sender = (int) $value;
                    break;
                case 'adresse' :
                    $this->adresse = (string) $value;
                    break;
                case 'proj_id' :
                    $this->proj_id = (int) $value;
                    break;
                case 'tree_id' :
                    $this->tree_id = (int) $value;
                    break;
                case 'timestamp' :
                    $this->timestamp = (string) $value;
                    break;
            }
        }
    }
    
    public function delete()
    {
        $p = Projet::getP($this->db, $this->proj_id);
        exec("rm -rf " . EXECPATH . $p->getRepertoire() . "/uploads/".$this->adresse." \n");
        $this->db->exec('DELETE FROM Documents WHERE id = '.$this->id);
    }  
    
    public function add() {
        $q = $this->db->prepare('INSERT INTO Documents SET titre = :titre, sender = :sender, adresse = :adresse, proj_id = :proj_id, tree_id = :tree_id, timestamp = NOW()');

        $q->bindValue(':titre', $this->titre);
        $q->bindValue(':sender', $this->sender);
        $q->bindValue(':adresse', $this->adresse);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->bindValue(':tree_id', $this->tree_id, PDO::PARAM_INT);

        $q->execute();

        $this->hydrate(array(
            'id' => $this->db->lastInsertId()
        )); 
    }
    
    public function update() {
        $q = $this->db->prepare('UPDATE Documents 
                                 SET titre = :titre, 
                                     sender = :sender, 
                                     adresse = :adresse, 
                                     proj_id = :proj_id, 
                                     timestamp = NOW()
                                 WHERE id = :id');

        $q->bindValue(':titre', $this->titre);
        $q->bindValue(':sender', $this->sender);
        $q->bindValue(':adresse', $this->adresse);
        $q->bindValue(':id', $this->id);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);

        $q->execute();
    }
    
    public function exists()
    {
        $q = $this->db->prepare('SELECT COUNT(*) FROM Documents WHERE titre = :titre AND proj_id = :proj_id');
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->execute(array(':titre' => $this->titre));

        $return = (bool) $q->fetchColumn();
        return $return;
    }
    
    public static function getList($db, $idprj) {
        $q = $db->prepare('SELECT * FROM Documents WHERE proj_id = :id_prj ORDER BY id DESC');
        $q->execute(array(':id_prj' => $idprj));
        
        $documents = array();
        foreach ($q->fetchAll() as $qdocument) {
            $qdocument['db'] = $db;
            $document = new Document($qdocument);
            $documents[] = $document;
        }
        return $documents;
    }
    
    public function createFileName() {
        $str = Utils::alStr(16);
        return $str;
    }
    
    public function isSender($idmbr) {
        return intval($idmbr) == $this->sender;
    }
    
    
    public static function getD($db, $info) {
        $q = $db->prepare('SELECT * FROM Documents WHERE id = :id');
        $q->execute(array(':id' => $info));

        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $doc = new Document($params);
        return $doc;
    }
    
}

?>

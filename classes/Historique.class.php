<?php

class Historique {
    
    private $db;
    
    private $id, $date, $user_id, $description, $proj_id, $save;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function getUser_id() {
        return $this->user_id;
    }

    public function setUser_id($user_id) {
        $this->user_id = $user_id;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getProj_id() {
        return $this->proj_id;
    }

    public function setProj_id($proj_id) {
        $this->proj_id = $proj_id;
    }
    
    public function getSave() {
        return $this->save;
    }

    public function setSave($save) {
        $this->save = $save;
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
                case 'user_id' :
                    $this->user_id = (int) $value;
                    break;
                case 'description' :
                    $this->description = (string) $value;
                    break;
                case 'date' :
                    $this->date = (string) $value;
                    break;
                case 'proj_id' :
                    $this->proj_id = (int) $value;
                    break;
                case 'save' :
                    $this->save = (int) $value;
                    break;
            }
        }
    }
    
    public function delete($type='Historiques')
    {
        if(isset($this->save) && $this->save != 0) {
            $projet = Projet::getP($this->db, $this->proj_id);
            $projet->removeSave($this->save);
        }
        $this->db->exec("DELETE FROM $type WHERE id = ".$this->id);
    }  
    
    public function add($type='Historiques') {
        $q = $this->db->prepare("INSERT INTO $type
                                 SET user_id = :user_id,
                                     description = :description, 
                                     date = NOW(),
                                     proj_id = :proj_id");

        $q->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $q->bindValue(':description', $this->description);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);

        $ret = $q->execute();

        $this->hydrate(array(
            'id' => $this->db->lastInsertId()
        )); 
    }
    
    public function update($type='Historiques') {
        $save = '';
        if($type=="Historiques")
            $save = "save = ".$this->save.",";
        $q = $this->db->prepare("UPDATE $type
                                 SET user_id = :user_id,
                                     description = :description, 
                                     date = NOW(),
                                     $save
                                     proj_id = :proj_id
                                     WHERE id = :id");

        $q->bindValue(':user_id', $this->user_id, PDO::PARAM_INT);
        $q->bindValue(':description', $this->description);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);

        $ret = $q->execute();
    }
    
    public static function getH($db, $id, $type='Historiques') {
        $q = $db->prepare("SELECT * FROM $type WHERE id = :id");
        $q->execute(array(':id' => $id));
        
        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $historique = new Historique($params);
        return $historique;
    }
    
    public static function getBySave($db, $id) {
        $q = $db->prepare("SELECT * FROM Historiques WHERE save = :id");
        $q->execute(array(':id' => $id));
        
        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $historique = new Historique($params);
        return $historique;
    }
    
    public static function getList($db, $projet, $type='Historiques') {
        if($type != 'Messages')
            $q = $db->prepare("SELECT * FROM $type WHERE proj_id = :proj_id ORDER BY id DESC");
        else $q = $db->prepare("SELECT * FROM $type WHERE proj_id = :proj_id ORDER BY id ASC");
        $q->execute(array(':proj_id' => $projet));
        
        $historique = array();
        foreach($q->fetchAll() as $qmessage){
            $qmessage['db'] = $db;
            $message = new Historique($qmessage);
            $historique[] = $message;
        }
        return $historique;
    }
    
}

?>

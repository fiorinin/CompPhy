<?
class ToDo{

    private $db, $id, $content, $status, $proj_id, $ordernb;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getContent() {
        return $this->content;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getProj_id() {
        return $this->proj_id;
    }

    public function setProj_id($proj_id) {
        $this->proj_id = $proj_id;
    }

    public function getOrdernb() {
        return $this->ordernb;
    }

    public function setOrdernb($ordernb) {
        $this->ordernb = $ordernb;
    }

    public function __construct($par){
        $this->hydrate($par);
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
                case 'content' :
                    $this->content = (string) $value;
                    break;
                case 'status' :
                    $this->status = (int) $value;
                    break;
                case 'proj_id' :
                    $this->proj_id = (int) $value;
                    break;
                case 'ordernb' :
                    $this->ordernb = (int) $value;
                    break;
            }
        }
    }

    public function __toString(){
        return '
                <li id="todo-'.$this->id.'" class="todo">
                    <div class="texttodo">'.$this->content.'</div>
                    <div class="actions">
                        <a href="" class="edit">Edit</a>
                        <a href="" class="timelinelogo" title="Done. Insert in timeline" >Timeline</a>
                        <a href="" class="validatelogo" title="Done. Forget about it">Validate</a>
                        <a href="" class="delete" title="Delete this item">Delete</a>
                    </div>
                </li>';
    }

    public function toStringOld(){
        return '
                <li id="todo-'.$this->id.'" class="todo">
                    <div class="texttodoold">'.$this->content.'</div>
                </li>';
    }
    
    public function delete() {
        $this->db->exec('DELETE FROM Todos WHERE id = '.$this->id);
    }  
    
    public function add() {
        $q = $this->db->prepare('INSERT INTO Todos SET content = :content, status = :status, proj_id = :proj_id, ordernb = :ordernb');

        $q->bindValue(':content', $this->content);
        $q->bindValue(':status', $this->status, PDO::PARAM_INT);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->bindValue(':ordernb', $this->ordernb, PDO::PARAM_INT);

        $q->execute();

        $this->hydrate(array(
            'id' => $this->db->lastInsertId()
        )); 
    }
    
    public function update() {
        $q = $this->db->prepare('UPDATE Todos 
                                 SET content = :content, 
                                     status = :status, 
                                     proj_id = :proj_id, 
                                     ordernb = :ordernb
                                 WHERE id = :id');

        $q->bindValue(':content', $this->content);
        $q->bindValue(':status', $this->status, PDO::PARAM_INT);
        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->bindValue(':ordernb', $this->ordernb, PDO::PARAM_INT);
        $q->bindValue(':id', $this->id);

        $q->execute();
    }
    
    public function calcOrdernb() {
        $q = $this->db->prepare('SELECT MAX(ordernb)+1 AS maxnb
                                 FROM Todos
                                 WHERE proj_id = :proj_id');

        $q->bindValue(':proj_id', $this->proj_id, PDO::PARAM_INT);
        $q->execute();
        
        $params = $q->fetch(PDO::FETCH_ASSOC);
        unset($q);
        $ordernb = $params["maxnb"] != null ? $params["maxnb"]:1;
        $this->ordernb = $ordernb;
    }
    
    public static function getList($db, $idprj) {
        $q = $db->prepare('SELECT * FROM Todos WHERE proj_id = :id_prj AND status = 1 ORDER BY ordernb ASC');
        $q->execute(array(':id_prj' => $idprj));
        
        $todos = array();
        foreach ($q->fetchAll() as $qtodo) {
            $qtodo['db'] = $db;
            $todo = new ToDo($qtodo);
            $todos[] = $todo;
        }
        return $todos;
    }
    
    public static function getListOld($db, $idprj) {
        $q = $db->prepare('SELECT * FROM Todos WHERE proj_id = :id_prj AND status = 0 ORDER BY ordernb DESC');
        $q->execute(array(':id_prj' => $idprj));
        
        $todos = array();
        foreach ($q->fetchAll() as $qtodo) {
            $qtodo['db'] = $db;
            $todo = new ToDo($qtodo);
            $todos[] = $todo;
        }
        return $todos;
    }
    
    public static function getT($db, $info) {
        $q = $db->prepare('SELECT * FROM Todos WHERE id = :id');
        $q->execute(array(':id' => $info));

        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $todo = new ToDo($params);
        return $todo;
    }
}
?>
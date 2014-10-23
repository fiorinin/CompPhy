<?php

class Utilisateur {
    
    // Needed for keeping DB connexion
    private $db;

    private $id, $nom, $prenom, $mail, $password, $avatar, $last_action;
    
    ///////// ACCESSEURS ////////////////////////////////
    /**
     * Getters
     */
    public function getId() {
        return $this->id;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function getMail() {
        return $this->mail;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getAvatar() {
        return $this->avatar;
    }
    
    public function getLast_action() {
        return $this->last_action;
    }
    
    /**
     * Setters
     */
    public function setId($id) {
        $this->id = $id;
    }

    public function setNom($nom) {
        $this->nom = $nom;
    }

    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }

    public function setMail($mail) {
        $this->mail = $mail;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setAvatar($avatar) {
        $this->avatar = $avatar;
    }

    public function setLast_action($last_action) {
        $this->last_action = $last_action;
    }

    /*public function setIsConnected($isConnected) {
        $this->isConnected = $isConnected;
    }*/
    ///////////////////////////////////////////////

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
                case 'nom' :
                    $this->nom = (string) $value;
                    break;
                case 'prenom' :
                    $this->prenom = (string) $value;
                    break;
                case 'mail' :
                    $syntaxe='#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,6}$#'; 
                    if(preg_match($syntaxe,$value)) 
                        $this->mail = (string) $value;  
                    break;
                case 'password' :
                    $this->password = (string) $value;
                    break;
                case 'avatar' :
                    $this->avatar = (string) $value;
                    break;
                /*case 'isConnected' :
                    $this->isConnected = (bool) $value;
                    break;*/
                case 'last_action' :
                    $this->last_action = (string) $value;
                    break;
            }
        }
    }
        
    /**
    * Sauvegarde du membre : update ou add
    */
    public function save($explicit=true) {
        $this->isNew() === true ? $this->add($explicit) : $this->update();
    }
    
    /**
    * Fonction d'ajout de membre
    */
    private function add($explicit) {
        if($this->password == "") {
            $this->createPass();
            $clearpass = $this->password;
            $this->password = md5($clearpass);
        } else {
            $clearpass = $this->password;
            $this->password = md5($clearpass);
        }
        $q = $this->db->prepare('INSERT INTO Utilisateurs SET nom = :nom, prenom = :prenom, mail = :mail, password = :password, avatar = :avatar, last_action = NOW()');

        $q->bindValue(':nom', $this->nom);
        $q->bindValue(':prenom', $this->prenom);
        $q->bindValue(':mail', $this->mail);
        $q->bindValue(':password', $this->password);
        $q->bindValue(':avatar', $this->avatar);

        $q->execute();

        // M�J id
        $this->hydrate(array(
            'id' => $this->db->lastInsertId()
        )); 
        
        if($explicit === true) {
            $mail = new phpmailer;

            $mail->IsMail();
            $mail->From = "no-reply@lirmm.fr";
            $mail->FromName = "CompPhy";
            $mail->AddAddress($this->mail, $this->prenom.' '.$this->nom);
            $mail->WordWrap = 100;  
            $mail->IsHTML(true); 
            $mail->Subject = "New account";
            $mail->Body = "Dear ".$this->prenom." ".$this->nom.",<br><br>
                You have created an account on <a href='".COMPPHYROOTWEB."'>CompPhy</a>. Here are your account connexion details:<br><br>
                Login: <b>".$this->mail.".</b><br>
                Password: <b>$clearpass</b><br><br>
                You can access your account detais and projects by going on your <a href='".COMPPHYROOTWEB."?p=projects'>projects page</a>.<br><br>
                Thank you for your attention, see you soon on CompPhy.<br>
                -------------------------------------------------<br>
                The CompPhy team.";
            $mail->Send();
        }
    }
    
    /**
    * Update du membre
    */
    private function update()
    {
        $q = $this->db->prepare('UPDATE Utilisateurs SET nom = :nom, prenom = :prenom, mail = :mail, password = :password, avatar = :avatar, last_action = NOW() WHERE id = :id');

        $q->bindValue(':nom', $this->nom);
        $q->bindValue(':prenom', $this->prenom);
        $q->bindValue(':mail', $this->mail);
        $q->bindValue(':password', $this->password);
        $q->bindValue(':avatar', $this->avatar);
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);

        $q->execute();
    }
    
    /**
    * Supprime un membre
    */
    public function delete()
    {
        $q = $this->db->prepare('DELETE FROM Utilisateurs 
                                 WHERE id = :id');
        
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
    }   
    
    /**
    * V�rification de la nouveaut� du membre
    */
    public function isNew() {
        return empty($this->id);    
    }
    
    /**
    * Verifie l'existence d'un membre selon l'id ou le mail
    */
    public static function exists($mail, $db) {
        $q = $db->prepare('SELECT COUNT(*) FROM Utilisateurs WHERE mail = :mail');
        $q->execute(array(':mail' => $mail));

        $return = (bool) $q->fetchColumn();
        return $return;
    }
    
    /**
     * Connexion d'un utilisateur
     */
    public function connect() {
        $q = $this->db->prepare('SELECT * FROM Utilisateurs WHERE mail = :mail');
        $q->execute(array(':mail' => $this->mail));
        
        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $this->db;
        $membre = new Utilisateur($params); 
        
        if ($membre->getPassword() != $this->password)
            $membre = null;
                
        return $membre;
    }
    
    /**
     * Récupération du membre
     */
    public static function getM($db, $info) {
        if (!is_int($info)) {
            $q = $db->prepare('SELECT * FROM Utilisateurs WHERE mail = :mail');
            $q->execute(array(':mail' => $info));
        }
        else {
            $q = $db->prepare('SELECT * FROM Utilisateurs WHERE id = :id');
            $q->execute(array(':id' => $info));
        }
        
        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $membre = new Utilisateur($params);
        
        return $membre;
    }
    
    public static function getList($db, $idmbr=null) {
        $q = $db->prepare('SELECT * FROM Utilisateurs');
        $q->execute();

        $users = array();
        foreach ($q->fetchAll() as $quser) {
            $quser['db'] = $db;
            $user = new Utilisateur($quser);
            $users[] = $user;
        }
        return $users;
    }
    
    public function createPass() {
        $this->password = Utils::alStr(8);
    }
    
    public function passRecovery() {
        $this->createPass();
        $clearpass = $this->password;
        $this->password = md5($clearpass);
        $this->save();

        $mail = new phpmailer;

        $mail->IsMail();
        $mail->From = "no-reply@lirmm.fr";
        $mail->FromName = "CompPhy";
        $mail->AddAddress($this->mail, $this->prenom.' '.$this->nom);
        $mail->WordWrap = 100;  
        $mail->IsHTML(true); 
        $mail->Subject = "Password recovery";
        $mail->Body = "Dear ".$this->prenom." ".$this->nom.",<br><br>
            You requested us to send you a new password for your account on <a href='".COMPPHYROOTWEB."'>CompPhy</a>.<br>
            Here is your new password : <b>$clearpass</b>.<br>
            Please change this password once logged in, in your account settings.<br><br>
            Thank you for your attention, see you soon on CompPhy.<br>
            -------------------------------------------------<br>
            The CompPhy team.";
        $mail->Send();
    }
    
    public function createAvatarName() {
        $str = Utils::alStr(16);
        $this->avatar = $str.".png";
        return $str;
    }
    
    public function delAvatar() {
        exec("rm -f ". ROOTPATH . 'compphy/avatars/' . $this->avatar);
        $this->avatar = '';
    }
    
    public function getInvitations() {
        $q = $this->db->prepare('SELECT * FROM UInvitations WHERE ut_id = :id');
        $q->execute(array(':id' => $this->id));
        
        $projects = array();
        foreach ($q->fetchAll() as $qinvite) {
            $project = Projet::getP($this->db, intval($qinvite['proj_id']));
            $projects[] = $project;
        }
        return $projects;
    }
    
    public function isOnline() {
        if(strtotime($this->last_action) < time() - 300) { // Timeout
            return false;
        }
        return true;
    }
}

?>

<?php

class Projet {

    private $db;
    private $id, $titre, $description, $creationDate, $statut, $main, $repertoire, $chef_id, $public, $last_update, $maing, $maind;

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

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getCreationDate() {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate) {
        $this->creationDate = $creationDate;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function getMain() {
        return $this->main;
    }

    public function setMain($main) {
        $this->main = $main;
    }

    public function getRepertoire() {
        return $this->repertoire;
    }

    public function setRepertoire($repertoire) {
        $this->repertoire = $repertoire;
    }

    public function getChef_id() {
        return $this->chef_id;
    }

    public function setChef_id($chef_id) {
        $this->chef_id = $chef_id;
    }

    public function getPublic() {
        return $this->public;
    }

    public function setPublic($public) {
        $this->public = $public;
    }
    
    public function getLast_update() {
        return $this->last_update;
    }

    public function setLast_update($last_update) {
        $this->last_update = $last_update;
    }
    
    public function getMaing() {
        return $this->maing;
    }

    public function setMaing($maing) {
        $this->maing = $maing;
    }

    public function getMaind() {
        return $this->maind;
    }

    public function setMaind($maind) {
        $this->maind = $maind;
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
                case 'description' :
                    $this->description = (string) $value;
                    break;
                case 'creationDate' :
                    $this->creationDate = (string) $value;
                    break;
                case 'statut' :
                    $this->statut = (int) $value;
                    break;
                case 'main' :
                    $this->main = (int) $value;
                    break;
                case 'repertoire' :
                    $this->repertoire = (string) $value;
                    break;
                case 'chef_id' :
                    $this->chef_id = (int) $value;
                    break;
                case 'publict' :
                    $this->public = (int) $value;
                    break;
                case 'last_update' :
                    $this->last_update = (string) $value;
                    break;
                case 'maing' :
                    $this->maing = (int) $value;
                    break;
                case 'maind' :
                    $this->maind = (int) $value;
                    break;
            }
        }
    }

    public function add() {
        $q = $this->db->prepare('INSERT INTO Projets 
                                 SET titre = :titre, 
                                     description = :description, 
                                     creationDate = NOW(),
                                     last_update = NOW(),
                                     statut = :statut,
                                     main = :main,
                                     repertoire = :repertoire,
                                     publict = :public,
                                     maing = :maing,
                                     maind = :maind,
                                     chef_id = :chef_id');

        $q->bindValue(':titre', $this->titre);
        $q->bindValue(':description', $this->description);
        $q->bindValue(':statut', $this->statut, PDO::PARAM_INT);
        $q->bindValue(':main', $this->main, PDO::PARAM_INT);
        $q->bindValue(':repertoire', $this->repertoire);
        $q->bindValue(':chef_id', $this->chef_id, PDO::PARAM_INT);
        $q->bindValue(':public', $this->public, PDO::PARAM_INT);
        $q->bindValue(':maind', $this->maind, PDO::PARAM_INT);
        $q->bindValue(':maing', $this->maing, PDO::PARAM_INT);

        $q->execute();

        $this->hydrate(array(
            'id' => $this->db->lastInsertId()
        ));
        $this->addToProject($this->chef_id);
    }

    public function update() {
        $q = $this->db->prepare('UPDATE Projets 
                                 SET titre = :titre, 
                                     description = :description, 
                                     publict = :public,
                                     chef_id = :chef_id,
                                     main = :main,
                                     last_update = NOW(),
                                     maing = :maing,
                                     maind = :maind,
                                     viewed = viewed+1
                                  WHERE id = :id');

        $q->bindValue(':titre', $this->titre);
        $q->bindValue(':description', $this->description);
        $q->bindValue(':main', $this->main, PDO::PARAM_INT);
        $q->bindValue(':chef_id', $this->chef_id, PDO::PARAM_INT);
        $q->bindValue(':public', $this->public, PDO::PARAM_INT);
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->bindValue(':maind', $this->maind, PDO::PARAM_INT);
        $q->bindValue(':maing', $this->maing, PDO::PARAM_INT);
        /*echo "UPDATE Projets 
                                 SET titre = $this->titre, 
                                     description = $this->description, 
                                     publict = $this->public,
                                     chef_id = $this->chef_id,
                                     main = $this->main,
                                     last_update = NOW(),
                                     maing = $this->maing,
                                     maind = $this->maind,
                                     viewed = viewed+1
                                  WHERE id = $this->id";exit;*/

        $q->execute();
    }
    
    public function delete() {        
        $q = $this->db->prepare('DELETE FROM Projets 
                                 WHERE id = :id');
        
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
        
        exec("rm -rf " . EXECPATH . $this->repertoire . "/ \n");
    }
    
    public function deleteDB() {        
        $q = $this->db->prepare('DELETE FROM Projets');
        $q->execute();
    }

    public function addToProject($idmbr) {
        $q = $this->db->prepare('INSERT INTO Appartient 
                                 SET prj_id = :prj_id, 
                                     ut_id = :ut_id');

        $q->bindValue(':prj_id', $this->id, PDO::PARAM_INT);
        $q->bindValue(':ut_id', $idmbr, PDO::PARAM_INT);

        $q->execute();
    }

    public function delFromProject($idmbr) {
        $q = $this->db->prepare('DELETE FROM Appartient 
                                 WHERE prj_id = :prj_id 
                                   AND ut_id = :ut_id');

        $q->bindValue(':prj_id', $this->id, PDO::PARAM_INT);
        $q->bindValue(':ut_id', $idmbr, PDO::PARAM_INT);

        $q->execute();
    }

    public function canAccess($idmbr) {
        $q = $this->db->prepare('SELECT COUNT(*) FROM Appartient
                                 WHERE prj_id = :prj_id AND ut_id = :ut_id');

        $q->bindValue(':prj_id', $this->id, PDO::PARAM_INT);
        $q->bindValue(':ut_id', $idmbr, PDO::PARAM_INT);

        $q->execute();
        $return = (bool) $q->fetchColumn();
        return $return;
    }

    public function isLead($idmbr) {
        $q = $this->db->prepare('SELECT COUNT(*) FROM Projets WHERE chef_id = :idmbr AND id = :prj_id');

        $q->bindValue(':prj_id', $this->id, PDO::PARAM_INT);
        $q->bindValue(':idmbr', $idmbr, PDO::PARAM_INT);

        $q->execute();
        $return = (bool) $q->fetchColumn();
        return $return;
    }

    public function exists() {
        $q = $this->db->prepare('SELECT COUNT(*) FROM Projets WHERE repertoire = :repertoire');
        $q->execute(array(':repertoire' => $this->repertoire));

        $return = (bool) $q->fetchColumn();
        return $return;
    }

    public static function existsS($id, $db) {
        $q = $db->prepare('SELECT COUNT(*) FROM Projets WHERE id = :id');
        $q->execute(array(':id' => $id));

        $return = (bool) $q->fetchColumn();
        return $return;
    }

    public static function getList($db, $idmbr=null) {
        if ($idmbr) {
            $q = $db->prepare('SELECT Projets.* FROM Projets, Appartient WHERE ut_id = :id_mbr AND Appartient.prj_id = Projets.id');
            $q->execute(array(':id_mbr' => $idmbr));
        } else {
            $q = $db->prepare('SELECT * FROM Projets');
            $q->execute();
        }

        $projects = array();
        foreach ($q->fetchAll() as $qproject) {
            $qproject['db'] = $db;
            $project = new Projet($qproject);
            $projects[] = $project;
        }
        return $projects;
    }

    public static function getP($db, $info) {
        if (is_int($info)) {
            $q = $db->prepare('SELECT * FROM Projets WHERE id = :id');
            $q->execute(array(':id' => $info));
        } else {
            $q = $db->prepare('SELECT * FROM Projets WHERE repertoire = :repertoire');
            $q->execute(array(':repertoire' => $info));
        }

        $params = $q->fetch(PDO::FETCH_ASSOC);
        $params['db'] = $db;
        $projet = new Projet($params);
        return $projet;
    }

    public function getLead() {
        $q = $this->db->prepare('SELECT * FROM Utilisateurs WHERE id = :chef_id');

        $q->bindValue(':chef_id', $this->chef_id, PDO::PARAM_INT);
        $q->execute();

        $result = $q->fetch(PDO::FETCH_ASSOC);
        $result['db'] = $this->db;
        $user = new Utilisateur($result);
        return $user;
    }

    public function getUsers() {
        $q = $this->db->prepare('SELECT Utilisateurs.* FROM Utilisateurs, Appartient WHERE Utilisateurs.id = Appartient.ut_id AND Appartient.prj_id = :proj_id ORDER BY Utilisateurs.nom');

        $q->bindValue(':proj_id', $this->id, PDO::PARAM_INT);
        $q->execute();

        $users = array();
        foreach ($q->fetchAll() as $user) {
            $user['db'] = $this->db;
            $user = new Utilisateur($user);
            $users[] = $user;
        }
        return $users;
    }
    
    public function invite($membre, $email, $lname, $fname='') {
        $newuser = null;
        $memberinvited = null;

        if(Utilisateur::exists($email, $this->db)) {
            $newuser = 0;
            $memberinvited = Utilisateur::getM($this->db, $email);

            $q = $this->db->prepare('SELECT COUNT(*) FROM UInvitations WHERE proj_id = :proj_id AND ut_id = :ut_id');
            $q->bindValue(':proj_id', $this->id, PDO::PARAM_INT);
            $q->bindValue(':ut_id', $memberinvited->getId(), PDO::PARAM_INT);
            $q->execute();
            
            $exists = (bool) $q->fetchColumn();
            $q->closeCursor();
            
            if(!$exists) {
                $mail = new phpmailer;

                $mail->IsMail();
                $mail->From = "no-reply@lirmm.fr";
                $mail->FromName = "CompPhy";
                $mail->AddAddress($email, $fname.' '.$lname);
                $mail->WordWrap = 100;  
                $mail->IsHTML(true); 
                $mail->Subject = "Compphy invitation";
                $mail->Body = "Dear Mr/Ms $fname $lname,<br><br>
                        ".$membre->getPrenom(). " " . $membre->getNom() . " has invited you to the \"" . $this->titre . "\" project. As you already have an account on CompPhy, you can either accept or decline this invitation on <a href='" . ROOT . "compphy/?p=projects'>your projects' page</a>.<br><br>
                            Thank you for your attention, see you soon on CompPhy.<br>
                            -------------------------------------------------<br>
                            The CompPhy team.";
                $mail->Send();
            }
        }
        else {
            $newuser = 1;
            $exists = false;

            $newmember = new Utilisateur(array('db' => $this->db, "nom" => $lname, "prenom" => $fname, "mail" => $email));
            $newmember->createPass();
            $clearpass = $newmember->getPassword();
            $newmember->save(false);
            $memberinvited = $newmember;

            $mail = new phpmailer;

            $mail->IsMail();
            $mail->From = "no-reply@lirmm.fr";
            $mail->FromName = "CompPhy";
            $mail->AddAddress($email, $fname.' '.$lname);
            $mail->WordWrap = 100;  
            $mail->IsHTML(true); 
            $mail->Subject = "Compphy invitation";
            $mail->Body = "Dear Mr/Ms $fname $lname,<br><br>
                    ".$membre->getPrenom(). " " . $membre->getNom() . " has invited you to the \"" . $this->titre . "\" project. CompPhy is a web-based collaborative platform for comparing phylogenies.<br><br>
                    An account has been created for you with the following password: <b>$clearpass</b>.<br>
                    You can log in CompPhy with this password and your email: <b>$email</b> <br>
                    Once connected, you will be able to access the project to which you are invited, and use CompPhy for your own projects as well. Please note that if you don't log in before 30 days, this account will be deleted.<br><br>
                    Interested by joining us ? Click here to log in and access your CompPhy page : <a href='".ROOT."compphy/'>".ROOT."compphy/</a>.<br><br>
                    Thank you for your attention, see you soon on CompPhy.<br>
                    -------------------------------------------------<br>
                    The CompPhy team.";
            $mail->Send();
        }

        if(!$exists) {
            $q = $this->db->prepare('INSERT INTO UInvitations 
                                     SET proj_id = :proj_id, 
                                         ut_id = :ut_id,
                                         date = NOW(),
                                         newuser = :newuser');

            $q->bindValue(':proj_id', $this->id, PDO::PARAM_INT);
            $q->bindValue(':ut_id', $memberinvited->getId(), PDO::PARAM_INT);
            $q->bindValue(':newuser', $newuser, PDO::PARAM_INT);
            $q->execute();
        }
    }
    
    public function acceptInvitation(Utilisateur $m, $v=true) {
        $q = $this->db->prepare('DELETE FROM UInvitations 
                                 WHERE proj_id = :pid AND ut_id = :uid');

        $q->bindValue(':pid', $this->id, PDO::PARAM_INT);
        $q->bindValue(':uid', $m->getId(), PDO::PARAM_INT);
        $q->execute();
        
        if($v) {
            $this->addToProject($m->getId());
        }
    }
    
    public static function get5Last($db) {
        $q = $db->prepare('SELECT * FROM Projets WHERE publict = 1 ORDER BY creationDate DESC LIMIT 0,4');
        $q->execute();
        
        $projects = array();
        foreach ($q->fetchAll() as $qproject) {
            $qproject['db'] = $db;
            $project = new Projet($qproject);
            $projects[] = $project;
        }
        return $projects;
    }
    
    public static function get5Hot($db) {
        $q = $db->prepare('SELECT * FROM Projets WHERE publict = 1 ORDER BY viewed DESC LIMIT 0,4');
        $q->execute();
        
        $projects = array();
        foreach ($q->fetchAll() as $qproject) {
            $qproject['db'] = $db;
            $project = new Projet($qproject);
            $projects[] = $project;
        }
        return $projects;
    }
    
    public function verifHand(Utilisateur $mbr) {
        $main = Utilisateur::getM($this->db, $this->main);
        if(!$main->isOnline()) {
            $this->donnerMain($mbr->getId());
            return true;
        }
        else return false;
    }
    
    public function hasHand() {
        $membre = Utilisateur::getM($this->db, $this->main);
        return $membre;
    }
    
    public function demanderMain($idmbr) {
        if(!$this->verifHand(Utilisateur::getM($this->db, $idmbr))) {
            $q = $this->db->prepare('INSERT INTO DemandeMain 
                                     SET proj_id = :proj_id, 
                                         ut_id = :ut_id,
                                         datetime = NOW()');

            $q->bindValue(':proj_id', $this->id, PDO::PARAM_INT);
            $q->bindValue(':ut_id', $idmbr, PDO::PARAM_INT);

            $q->execute();
        }
    }
    
    public function listerDemandes() {
        // First, we give control to the first one who asked without having any answer
        $q = $this->db->prepare('SELECT * FROM DemandeMain WHERE proj_id = :id AND datetime < NOW() - INTERVAL 30 SECOND LIMIT 1');
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
        $row = $q->fetch();
        if(!empty($row)) {
            $this->donnerMain($row['ut_id']);
            $this->deleteDemande($row['ut_id']);
        }
        
        // Then, removing old requests
        $q = $this->db->prepare('DELETE FROM DemandeMain WHERE proj_id = :id AND datetime < NOW() - INTERVAL 30 SECOND');
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
        
        // Now listing current requests
        $q = $this->db->prepare('SELECT * FROM DemandeMain WHERE proj_id = :id');
        $q->bindValue(':id', $this->id, PDO::PARAM_INT);
        $q->execute();
        
        $demandes = array();
        foreach ($q->fetchAll() as $qdemande) {
            $membre = Utilisateur::getM($this->db, intval($qdemande['ut_id']));
            $demande = array($membre,$qdemande['datetime']);
            $demandes[] = $membre;
        }
        return $demandes;
    }
    
    public function donnerMain($idmbr) {
        $this->main = $idmbr;
        $this->update();
        $this->deleteDemande($idmbr);
    }
    
    public function deleteDemande($idmbr) {
        $q = $this->db->prepare('DELETE FROM DemandeMain 
                                 WHERE proj_id = :proj_id
                                 AND ut_id = :ut_id');

        $q->bindValue(':proj_id', $this->id, PDO::PARAM_INT);
        $q->bindValue(':ut_id', $idmbr, PDO::PARAM_INT);

        $q->execute();
    }
    
    public function saveNow($id) {
        $str = Utils::alStr(8);
        //exec("cd ".EXECPATH . $this->repertoire . "\nmysqldump -p".PASS." -u ".USER." -e --opt -t --where=\"proj_id=".$this->id."\" ".DB." Arbres > $str.sql\n");
        exec("cd ".EXECPATH . $this->repertoire . "\nmysqldump -p".PASS." -u ".USER." -h ".DBHOST." -P ".DBPORT." -e --opt -t --where=\"proj_id=".$this->id."\" ".DB." Arbres > $str.sql\n");
        $q = $this->db->prepare('INSERT INTO Sauvegardes 
                                 SET adresse = :str,
                                     historique_id = :hid');
        
        $q->bindValue(':str', $str.'.sql', PDO::PARAM_INT);
        $q->bindValue(':hid', $id, PDO::PARAM_INT);

        $q->execute();
        
        return $this->db->lastInsertId();
    }
    
    public function restore($id) {
        $listA = Arbre::getListAll($this->db, $this->id);
        foreach($listA as $key => $tree)  {
            $tree->trueDelete();
        }
        
        $q = $this->db->prepare('SELECT * FROM Sauvegardes 
                                 WHERE id = :id');
        
        $q->bindValue(':id', $id, PDO::PARAM_INT);
        $q->execute();
        $return = $q->fetch(PDO::FETCH_ASSOC);
        //exec("cd ".EXECPATH . $this->repertoire . "\nmysql -p".PASS." -u ".USER." ".DB." < ".$return['adresse']."\n");
        exec("cd ".EXECPATH . $this->repertoire . "\nmysql -p".PASS." -u ".USER." -h ".DBHOST." -P ".DBPORT." ".DB." < ".$return['adresse']."\n");
        $this->update();
        $newListA = Arbre::getListAll($this->db, $this->id);
        foreach($newListA as $key => $tree)  {
            $tree->create(EXECPATH.$this->repertoire.'/');
        }
    }
    
    public function removeSave($id) {        
        $q = $this->db->prepare('SELECT * FROM Sauvegardes 
                                 WHERE id = :id');
        
        $q->bindValue(':id', $id, PDO::PARAM_INT);
        $q->execute();
        $return = $q->fetch(PDO::FETCH_ASSOC);
        
        exec("rm ".EXECPATH . $this->repertoire . "/" . $return['adresse'] . "\n"); 
        
        $message = Historique::getBySave($this->db, $id);
        $message->setSave(0);
        $message->update();
        
        $q = $this->db->prepare('DELETE FROM Sauvegardes 
                                 WHERE id = :id');
        
        $q->bindValue(':id', $id, PDO::PARAM_INT);
        $q->execute();
    }
}
?>

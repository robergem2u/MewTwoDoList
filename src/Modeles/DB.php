<?php

namespace App\Modeles;

use App\Classe\Liste;
use App\Classe\NotificationChangement;
use App\Classe\NotificationChangementProprietaire;
use App\Classe\Tache;
use App\Classe\Utilisateur;
use Exception;
use PDO;

class DB {

    /**
     * @var Singleton
     * @access private
     * @static
     */
    private static $_instance = null;
    private $pdo;

    /**
     * Constructeur de la classe
     *
     * @param void
     * @return void
     */
    private function __construct()
    {
        try {

            if (file_exists('../src/Config/config.ini')) {
                $config = parse_ini_file('../src/Config/config.ini');
            } elseif (file_exists('src/Config/config.ini')) {
                $config = parse_ini_file('src/Config/config.ini');
            } else {
                throw new Exception('Pas de fichier de config');
            }

            $this->pdo = new PDO("mysql:host=$config[host];dbname=$config[db];charset=utf8", $config['user'], $config['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        } catch (Exception $e) {
        }
    }

    public static function getInstance()
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new DB();
        }

        return self::$_instance;
    }

    /**
     * Retourne l'instance de PDO
     * @return PDO Instance de PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    public function deleteUser($mail){

        //On supprime toutes les listes desquelles il est propriétaire
        $resList = DB::getInstance()->getPDO()->prepare("SELECT idListe FROM Liste WHERE mailProprietaire = :mail");
        $resList->bindParam(":mail", $mail);
        $resList->execute();
        while($donnees = $resList->fetch()){
            $this->deleteListe($donnees["idListe"]);
        }

        //On le retire de toutes les listes dont il est membre
        $resList = DB::getInstance()->getPDO()->prepare("SELECT idListe FROM Membre WHERE mail = :mail");
        $resList->bindParam(":mail", $mail);
        $resList->execute();
        while($donnees = $resList->fetch()){
            $this->deleteListMember($mail, $donnees["idListe"]);
        }

        //On le supprime de la base de données
        $resUser = DB::getInstance()->getPDO()->prepare("DELETE FROM Utilisateur WHERE mail = :mail");
        $resUser->bindParam(":mail", $mail);
        $resUser->execute();
    }

	public function deleteListMember($mail, $idListe){
        $res = DB::getInstance()->getPDO()->prepare("SELECT mailProprietaire FROM Liste WHERE idListe = :idListe");
        $res->bindParam(":idListe", $idListe);
        $res->execute();
        if($donnees = $res->fetch()){
            $mailProprio = $donnees["mailProprietaire"];
        }

        $res = DB::getInstance()->getPDO()->prepare("DELETE FROM Membre WHERE mail = :mail and idListe = :idListe and mail != :mailProprio");
        $res->bindParam(":mail", $mail);
        $res->bindParam(":idListe", $idListe);
        $res->bindParam(":mailProprio", $mailProprio);
        $res->execute();
    }

    public function deleteAllListMembers($idListe){
        $res = DB::getInstance()->getPDO()->prepare("DELETE FROM Membre WHERE idListe = :idListe");
        $res->bindParam(":idListe", $idListe);
        $res->execute();
    }

    public function getUtilisateurs($criteria, $args, $id)
    {
        switch ($criteria) {
            case "name":
                $getUser = DB::getInstance()->getPDO()->prepare("SELECT * FROM Utilisateur
                        where mail not in (SELECT DISTINCT utilisateur.mail from utilisateur, membre where membre.mail like utilisateur.mail and membre.idListe = :id )
                         and (nomUser like UPPER(CONCAT(:nomUser,'%')) or
                         prenomUser like UPPER(CONCAT(:nomUser,'%')) or
                         ( :prenomUser not like '' and prenomUser like UPPER(CONCAT(:prenomUser,'%'))) or
                         ( :prenomUser not like '' and nomUser like UPPER(CONCAT(:prenomUser,'%')))) ");


                $name = $args[0];
                $prenom = $args[1];

                $getUser->bindParam(':nomUser', $name);
                $getUser->bindParam(':prenomUser', $prenom);
                $getUser->bindParam(':id', $id);
                break;
            case "mail":
                $getUser = DB::getInstance()->getPDO()->prepare("SELECT * FROM Utilisateur where mail not in (SELECT DISTINCT utilisateur.mail from utilisateur, membre where membre.mail like utilisateur.mail and membre.idListe = :id ) and mail like UPPER(CONCAT(:mail,'%'))");

                $mail = $args[0];
                $getUser->bindParam(':mail', $mail);
                $getUser->bindParam(':id', $id);

                break;
            case "pseudo":
                $getUser = DB::getInstance()->getPDO()->prepare("SELECT * FROM Utilisateur where mail not in (SELECT DISTINCT utilisateur.mail from utilisateur, membre where membre.mail like utilisateur.mail and membre.idListe = :id ) and pseudoUser  like UPPER(CONCAT(:users,'%'))");
                $user = $args[0];
                $getUser->bindParam(':users', $user);
                $getUser->bindParam(':id', $id);
                break;
            default:
                $getUser = DB::getInstance()->getPDO()->prepare("SELECT * FROM Utilisateur");
                break;
        }

        $getUser->execute();

        $utilisateurs = array();

        while ($donnees = $getUser->fetch()) {
            $utilisateur = new Utilisateur($donnees["nomUser"], $donnees["prenomUser"], $donnees["pseudoUser"], $donnees["mail"], null, $donnees['photo']);

            $utilisateurs[$utilisateur->getMail()] = $utilisateur;


        }

        return $utilisateurs;
    }

    public function getUtilisateur($name)
    {

                $getUser = DB::getInstance()->getPDO()->prepare("SELECT * FROM Utilisateur where  pseudoUser  like :users");
                $user = $name;
                $getUser->bindParam(':users', $user);


        $getUser->execute();

        if($donnees = $getUser->fetch()) {
            $utilisateur = new Utilisateur($donnees["nomUser"], $donnees["prenomUser"], $donnees["pseudoUser"], $donnees["mail"], null, $donnees['photo']);

            return $utilisateur;
        }

        return null;
    }

    public function loadUtilisateur($mail)
    {
        $tabListes = array();
        /*Préparation des requêtes*/
        $verifMail = DB::getInstance()->getPDO()->prepare("SELECT * FROM Utilisateur where mail = :mailVerification");
        $getListeProp = DB::getInstance()->getPDO()->prepare("SELECT * FROM Liste where mailProprietaire = :mailVerification");

        /*On test si le mail existe dans la base de données*/
        $verifMail->bindParam(':mailVerification', $mail);
        $verifMail->execute();

        if ($donnees = $verifMail->fetch()) {
            $utilisateur = new Utilisateur($donnees["nomUser"], $donnees["prenomUser"], $donnees["pseudoUser"], $donnees["mail"], null, $donnees["photo"]);


            /*On récupère les liste dont l'utilisateur est le propriétaire dans la base de données*/
            $getListeProp->bindParam(':mailVerification', $mail);
            $getListeProp->execute();

            while ($donnesListe = $getListeProp->fetch()) {
                $liste = $this->loadListe($donnesListe['idListe']);
                array_push($tabListes, $liste);

            }$utilisateur->setListesProprietaire($tabListes);
            return $utilisateur;
        } else {
            return null;
        }
    }

    public function loadListe($id){
        /*Préparation des requêtes*/
        $verifId = DB::getInstance()->getPDO()->prepare("SELECT * FROM Liste where idListe = :idVerification");

        /*On test si le mail existe dans la base de données*/
        $verifId->bindParam(':idVerification', $id);
        $verifId->execute();

        if ($donnees = $verifId->fetch()) {
            $liste = new Liste($donnees["idListe"], $donnees["intituleListe"], $donnees["dateCreation"], $donnees["dateFin"], $donnees["mailProprietaire"]);

            $recupererTaches = DB::getInstance()->getPDO()->prepare("SELECT idTache FROM Tache where idListeT = :idVerification2");
            $recupererTaches->bindParam(':idVerification2', $donnees["idListe"]);
            $recupererTaches->execute();
            while ($idTache = $recupererTaches->fetch()) {
                $tache = $this->loadTache($idTache["idTache"]);
                $liste->ajouterTache($tache);
            }

            $recupererMembre = DB::getInstance()->getPDO()->prepare("SELECT * FROM Membre WHERE idListe = :id ");
            $recupererMembre->bindParam(":id", $id);
            $recupererMembre->execute();
            while ($donneesMembre = $recupererMembre->fetch()) {
                if($donneesMembre != $donnees["mailProprietaire"]) {
                    $liste->ajouterUtilisateur($donneesMembre["mail"]);
                }
            }

            return $liste;
        } else {
            return null;
        }
    }

    public function loadTache($idTache){
        /*Préparation des requêtes*/
        $verifId = DB::getInstance()->getPDO()->prepare("SELECT * FROM Tache where idTache = :idVerification");

        /*On test si le mail existe dans la base de données*/
        $verifId->bindParam(':idVerification', $idTache);
        $verifId->execute();

        if ($donnees = $verifId->fetch()) {
            $tache = new Tache($donnees["idTache"], $donnees["intituleTache"], $donnees["etat"], $donnees["idListeT"], $donnees["mailUtilisateur"], $donnees["valide"]);

            return $tache;
        } else {
            return null;
        }
    }

    public function lastImagesId(){
        $results = DB::getInstance()->getPDO()->prepare("SELECT idImage FROM Images ORDER BY idImage DESC");
        $results->execute();
        if($donnees = $results->fetch()){
            return $donnees['idImage'];
        }
        return null;
    }

    public function addUtilisateur($utilisateur){

        $mail = $utilisateur->getMail();
        $nom = $utilisateur->getNom();
        $prenom = $utilisateur->getPrenom();
        $mdp = $utilisateur->getMotDePasse();
        $pseudo = $utilisateur->getPseudo();
        $photo = $utilisateur->getPhoto();

        $results = DB::getInstance()->getPDO()->prepare('INSERT INTO Utilisateur(mail, nomUser, prenomUser,pseudoUser,mdp,photo) VALUES (:mail,:nomUser, :prenomUser,:pseudoUser, :mdp,:photo)');
        $results->bindParam(':mail', $mail);
        $results->bindParam(':nomUser', $nom);
        $results->bindParam(':prenomUser', $prenom);
        $results->bindParam(':pseudoUser', $pseudo);
        $results->bindParam(':mdp', password_hash($mdp, PASSWORD_DEFAULT));
        $results->bindParam(':photo', $photo);
        $results->execute();

    }

    public function updateUtilisateur($mail, $nom, $prenom, $mdp, $pseudo, $photo){
        $utilisateur = $this->loadUtilisateur($mail);
        $existe = 0;

        if ($mdp != null) {
            $results = DB::getInstance()->getPDO()->prepare("UPDATE Utilisateur SET nomUser = :nomUser, prenomUser = :prenomUser, pseudoUser = :pseudoUser, mdp = :mdp, photo = :photo WHERE mail = :mail");
            $results->bindParam('mdp', $mdp);
        }else{
            $results = DB::getInstance()->getPDO()->prepare("UPDATE Utilisateur SET nomUser = :nomUser, prenomUser = :prenomUser, pseudoUser = :pseudoUser, photo = :photo WHERE mail = :mail");
        }
        $results->bindParam('mail', $mail);
        if ($nom != null) {
            $results->bindParam('nomUser', $nom);
        }else {
            $nom = $utilisateur->getNom();
            $results->bindParam('nomUser', $nom);
        }
        if ($prenom != null) {
            $results->bindParam('prenomUser', $prenom);
        }else {
            $prenom = $utilisateur->getPrenom();
            $results->bindParam('prenomUser', $prenom);
        }
        if ($pseudo != null) {
            $results->bindParam('pseudoUser', $pseudo);
        }else {
            $pseudo = $utilisateur->getPseudo();
            $results->bindParam('pseudoUser', $pseudo);
        }
        if ($photo != null) {
            $results->bindParam('photo', $photo);
        }else {
            $photo = $utilisateur->getPhoto();
            $results->bindParam('photo', $photo);
        }
        $results->execute();
    }

    public function isPseudo($pseudo){
        $requete = DB::getInstance()->getPDO()->prepare('SELECT pseudoUser FROM Utilisateur');
        $requete->execute();
        while ($donnees = $requete->fetch()){
            if($donnees['pseudoUser'] == $pseudo){
                return 1;
            }
        }
        return 0;
    }

    public function addListe($idListe,$intituleListe,$dateCreation, $dateFin,$mailProprietaire)
    {
        $results = DB::getInstance()->getPDO()->prepare('INSERT INTO Liste(idListe,intituleListe,dateCreation,dateFin,mailProprietaire) VALUES (:id, :intitule, :dateCrea, :dateFin, :mail)');
        $results->bindParam(':id', $idListe);
        $results->bindParam(':intitule', $intituleListe);
        $results->bindParam(':dateCrea', $dateCreation);
        $results->bindParam(':dateFin', $dateFin);
        $results->bindParam(':mail', $mailProprietaire);
        $results->execute();
    }

    public function addNotification($notification){
        $idNotif = $notification->getIdNotification();
        $dateEnvoi = $notification->getDateCreation();
        $valide = 0;
        $contenu = $notification->getContenu();
        $lu = $notification->isLu();
        $mail = $notification->getSourceUtilisateur();
        $idListe = $notification->getIdListe();
        $mailMembre = $notification->getDestUtilisateur();
        echo $idNotif." ".$dateEnvoi." ".$valide." ".$contenu." ".$lu." ".$mail." ".$idListe;
        $requete = DB::getInstance()->getPDO()->prepare('INSERT INTO Notification(idNotification, dateEnvoi, valide, contenu, lu, mail, idListe, mailMembre) VALUES (:idNotif, :dateEnvoi, :valide, :contenu, :lu, :mail, :idListe, :mailMembre)');
        $requete->bindParam(':idNotif', $idNotif);
        $requete->bindParam(':dateEnvoi', $dateEnvoi);
        $requete->bindParam(':valide', $valide);
        $requete->bindParam(':contenu', $contenu);
        $requete->bindParam(':lu', $lu);
        $requete->bindParam(':mail', $mail);
        $requete->bindParam(':idListe', $idListe);
        $requete->bindParam(':mailMembre', $mailMembre);
        $requete->execute();
    }

    public function alterNotif($idNotif, $lu,$valide){

        $results = DB::getInstance()->getPDO()->prepare('UPDATE Notification SET lu=:lu, valide=:valide WHERE idNotification = :id');
        $results->bindParam(':lu', $lu);
        $results->bindParam(':valide', $valide);
        $results->bindParam(':id', $idNotif);
        $results->execute();
    }


    public function alterListe($idListe,$intituleListe,$dateCreation, $dateFin,$mailProprietaire)
    {
        $results = DB::getInstance()->getPDO()->prepare('UPDATE Liste SET idListe=:id, intituleListe=:intitule, dateCreation=:dateCrea, dateFin=:dateFin WHERE idListe = :id');
        $results->bindParam(':id', $idListe);
        $results->bindParam(':intitule', $intituleListe);
        $results->bindParam(':dateCrea', $dateCreation);
        $results->bindParam(':dateFin', $dateFin);
        $results->bindParam(':mail', $mailProprietaire);
        $results->execute();
    }

    public function deleteListe($idListe)
    {
        $results = DB::getInstance()->getPDO()->prepare("SELECT idTache FROM Tache WHERE idListeT=:id");
        $results->bindParam(':id', $idListe);
        $results->execute();
        while($donnees = $results->fetch()){
            $this->deleteTache($donnees['idTache']);
        }

        $this->deleteAllListMembers($idListe);

        $this->deleteAllListNotification($idListe);

        $results = DB::getInstance()->getPDO()->prepare('DELETE FROM Liste WHERE idListe = :id ');
        $results->bindParam(':id', $idListe);
        $results->execute();
        $user = unserialize($_SESSION['user']);
        $user->supprimerListe($idListe);
        $_SESSION['user'] = serialize($user);
    }

    public function addTache($idTache,$intituleTache,$etat, $idListeTache,$mailUtilisateur,$valide)
    {
        $results = DB::getInstance()->getPDO()->prepare('INSERT INTO Tache(idTache,intituleTache,valide,idListeT,mailUtilisateur,etat) VALUES (:id, :intitule, :val, :idListe, :mail, :etat)');
        $results->bindParam(':id', $idTache);
        $results->bindParam(':intitule', $intituleTache);
        $results->bindParam(':val', $valide);
        $results->bindParam(':idListe', $idListeTache);
        $results->bindParam(':mail', $mailUtilisateur);
        $results->bindParam(':etat', $etat);
        $results->execute();
    }

    public function modifTache($idTache,$intituleTache,$etat, $idListeTache,$mailUtilisateur,$valide)
    {
        $results = DB::getInstance()->getPDO()->prepare('UPDATE Tache SET idTache=:id, intituleTache=:intitule, valide=:val, idListeT=:idListe, mailUtilisateur=:mail, etat=:etatT WHERE idTache = :id');
        $results->bindParam(':id', $idTache);
        $results->bindParam(':intitule', $intituleTache);
        $results->bindParam(':val', $valide);
        $results->bindParam(':idListe', $idListeTache);
        $results->bindParam(':mail', $mailUtilisateur);
        $results->bindParam(':etatT', $etat);
        $results->execute();
    }



    public function deleteTache($idTache)
    {
        $results = DB::getInstance()->getPDO()->prepare('DELETE FROM Tache WHERE idTache = :id ');
        $results->bindParam(':id', $idTache);
        $results->execute();
    }

    public function addMembre($mail, $idListe){
        $results = DB::getInstance()->getPDO()->prepare('INSERT INTO Membre(mail, idListe) VALUES (:mail, :id) ');
        $results->bindParam(':mail', $mail);
        $results->bindParam(':id', $idListe);
        $results->execute();
    }

    public function addUserTache($mail, $idTache){
        $results = DB::getInstance()->getPDO()->prepare('UPDATE Tache SET mailUtilisateur = :mail WHERE idTache = :id');
        $results->bindParam(':mail', $mail);
        $results->bindParam(':id', $idTache);
        $results->execute();
    }

    public function deleteUserTache($idTache){
        $results = DB::getInstance()->getPDO()->prepare('UPDATE Tache SET mailUtilisateur = NULL WHERE idTache = :id');
        $results->bindParam(':id', $idTache);
        $results->execute();
    }

    public function recupererMembres($idListe){
        $membres = array();
        $results = DB::getInstance()->getPDO()->prepare('SELECT * FROM Membre WHERE idListe = :id');
        $results->bindParam(':id', $idListe);
        $results->execute();
        while ($donnees = $results->fetch()){
            array_push($membres, $donnees);
        }
        return $membres;
    }

    public function isMemberIn($mail, $idListe){
        $results = DB::getInstance()->getPDO()->prepare('SELECT * FROM Membre WHERE mail = :mail and idListe = :id');
        $results->bindParam(':id', $idListe);
        $results->bindParam(':mail', $mail);
        $results->execute();
        if($donnes = $results->fetch()){
            return 1;
        }
        return null;
    }

    public function recupererListesMembres($mail){
        $listes = array();
        $results = DB::getInstance()->getPDO()->prepare('SELECT * FROM Membre WHERE mail = :mail');
        $results->bindParam(':mail', $mail);
        $results->execute();
        while ($donnees = $results->fetch()){
            $liste = $this->loadListe($donnees['idListe']);
            array_push($listes, $liste);
        }
        return $listes;
    }

	public function createNotif($idNotif, $date, $valide, $contenu, $lu, $mail, $idLis, $mailMembre){
        $lu = 0;
        $results = DB::getInstance()->getPDO()->prepare('INSERT INTO Notification (idNotification, dateEnvoi,valide,contenu,lu,mail,idListe, mailMembre) VALUES (:idNotif, :date, :valide, :contenu, :lu, :mail, :idLis, :mailMembre)');
        echo $idLis;
		$results->bindParam(':idNotif', $idNotif);
        $results->bindParam(':date', $date);
        $results->bindParam(':valide', $valide);
		$results->bindParam(':contenu', $contenu);
        $results->bindParam(':lu', $lu);
		$results->bindParam(':mail', $mail);
        $results->bindParam(':idLis', $idLis);
        $results->bindParam(':mailMembre', $mailMembre);
        $results->execute();
	}

	public function createNotifAjoutMembre($idNotif){
        $results = DB::getInstance()->getPDO()->prepare('insert into NotificationAjoutMembre values (:idNotif)');
        $results->bindParam(':idNotif', $idNotif);
        $results->execute();
    }

	public function createNotifAvecChangement($idNotif){
        $results = DB::getInstance()->getPDO()->prepare('insert into NotificationAvecChangement values (:idNotif)');
        $results->bindParam(':idNotif', $idNotif);
        $results->execute();
    }

	public function createNotifAvecChoix($idNotif, $repondu){
        $results = DB::getInstance()->getPDO()->prepare('insert into NotificationAvecChoix values (:idNotif, :repondu)');
        $results->bindParam(':idNotif', $idNotif);
		     $results->bindParam(':repondu', $repondu);
        $results->execute();
    }

	public function createNotifChangementProprietaire($idNotif){
        $results = DB::getInstance()->getPDO()->prepare('insert into NotificationChangementProprietaire values (:idNotif)');
        $results->bindParam(':idNotif', $idNotif);
        $results->execute();
    }

  public function createNotifSupprTache($idNotif, $idTache){
    $results = DB::getInstance()->getPDO()->prepare('insert into NotificationSupprTache values (:idNotif, :idTache)');
    $results->bindParam(':idNotif', $idNotif);
     $results->bindParam(':idTache', $idTache);
    $results->execute();
  }


	public function deleteNotifAjoutMembre($idNotif){
        $res = DB::getInstance()->getPDO()->prepare("delete from NotificationAjoutMembre where idNotification = :idNotif");
        $res->execute();

		$res = DB::getInstance()->getPDO()->prepare("delete from Notification where idNotification = :idNotif");
		$res->execute();
	}

	public function deleteNotifAvecChangement($idNotif){
        $res = DB::getInstance()->getPDO()->prepare("delete from NotificationAvecChangement where idNotification = :idNotif");
        $res->execute();

		$res = DB::getInstance()->getPDO()->prepare("delete from Notification where idNotification = :idNotif");
		$res->execute();
	}

	public function deleteNotifAvecChoix($idNotif){
    $res = DB::getInstance()->getPDO()->prepare("DELETE from NotificationAvecChoix where idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();

		$res = DB::getInstance()->getPDO()->prepare("delete from Notification where idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
		$res->execute();
	}

	public function deleteNotifChangementProprietaire($idNotif){
        $res = DB::getInstance()->getPDO()->prepare("delete from NotificationChangementProprietaire where idNotification = :idNotif");
        $res->execute();

		$res = DB::getInstance()->getPDO()->prepare("delete from Notification where idNotification = :idNotif");
		$res->execute();
	}

  public function deleteNotif($idNotif){

    $res = DB::getInstance()->getPDO()->prepare("DELETE FROM Notification WHERE idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();

    $res = DB::getInstance()->getPDO()->prepare("DELETE FROM NotificationAjoutMembre WHERE idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();

    $res = DB::getInstance()->getPDO()->prepare("DELETE FROM NotificationAvecChangement WHERE idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();

    $res = DB::getInstance()->getPDO()->prepare("DELETE FROM NotificationAvecChoix WHERE idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();

    $res = DB::getInstance()->getPDO()->prepare("DELETE FROM NotificationChangementProprietaire WHERE idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();

    $res = DB::getInstance()->getPDO()->prepare("DELETE FROM NotificationSupprTache WHERE idNotification = :idNotif");
    $res->bindParam(":idNotif", $idNotif);
    $res->execute();


  }

	public function loadNotifs($mail)
    {
        $notifs = array();
        $bdd = DB::getInstance()->getPDO()->prepare("SELECT * FROM Notification WHERE mailMembre = :mail ORDER BY idNotification DESC");
        $bdd->bindParam(':mail', $mail);
        $bdd->execute();
        while ($donnees = $bdd->fetch()) {
            $notif = new NotificationChangementProprietaire($donnees['idNotification'], $donnees['dateEnvoi'], $donnees['contenu'], $donnees['mail'], $donnees['idListe'], $donnees['mailMembre']);
            $notif->setValide($donnees['valide']);
            $notif->setLu($donnees['lu']);
            array_push($notifs, $notif);
        }
        return $notifs;
        /*

    while ($donnesListe = $getListeProp->fetch()) {
                    $liste = $this->loadListe($donnesListe['idListe']);
                    array_push($tabListes, $liste);

                }

    */
	}

	public function loadNotif($idNotif){
        $bdd = DB::getInstance()->getPDO()->prepare("SELECT * FROM Notification WHERE idNotification = :idNotif ");
        $bdd->bindParam(':idNotif', $idNotif);
        $bdd->execute();
        $notif = null;
        if($donnees = $bdd->fetch()){
            $notif = new NotificationChangementProprietaire($donnees['idNotification'], $donnees['dateEnvoi'], $donnees['contenu'], $donnees['mail'], $donnees['idListe'], $donnees['mailMembre']);
        }
        return $notif;

    }

  




    /**
     * Renvoie vrai si c'est une notification avec choix
     * @param $idNotif
     * @return bool
     */
	public function isNotifAvecChoix($idNotif){
        $bdd = DB::getInstance()->getPDO()->prepare("select count(*) from notificationavecchoix where idNotification = :id");
        $bdd->bindParam(':id', $idNotif);
        $bdd->execute();
        $donnees = $bdd->fetch();
        if($donnees[0] == 0){
            // on n'a pas trouvé la notification dans la table des notifications avec choix
            return false;
        }else{
            return true;
        }
    }

    /**
     * Renvoie vrai si c'est une notification pour un changement de propriétaire
     * @param $idNotif
     * @return bool
     */
    public function isNotifProprio($idNotif){
        $bdd = DB::getInstance()->getPDO()->prepare("select count(*) from notificationchangementproprietaire where idNotification = :id");
        $bdd->bindParam(':id', $idNotif);
        $bdd->execute();
        $donnees = $bdd->fetch();
        if($donnees[0] == 0){
            // on n'a pas trouvé la notification dans la table des notifications avec choix
            return false;
        }else{
            return true;
        }
    }

    /**
     * Renvoie vrai si c'est une notification d'invitation à rejoindre une liste
     * @param $idNotif
     * @return bool
     */
    public function isNotifAjoutMembre($idNotif){
        $bdd = DB::getInstance()->getPDO()->prepare("select count(*) from notificationajoutmembre where idNotification = :id");
        $bdd->bindParam(':id', $idNotif);
        $bdd->execute();
        $donnees = $bdd->fetch();
        if($donnees[0] == 0){
            // on n'a pas trouvé la notification dans la table des notifications avec choix
            return false;
        }else{
            return true;
        }
    }

    public function isNotifSupprTache($idNotif){
        $bdd = DB::getInstance()->getPDO()->prepare("SELECT count(*) FROM NotificationSupprTache where idNotification = :id");
        $bdd->bindParam(':id', $idNotif);
        $bdd->execute();
        $donnees = $bdd->fetch();
        if($donnees[0] == 0){
            // on n'a pas trouvé la notification dans la table des notifications avec choix
            return false;
        }else{
            return true;
        }
    }



    /**
     * Renvoie le mail du propriétaire de la liste
     */
    public function getMailProprietaire($idListe){
        $bdd = DB::getInstance()->getPDO()->prepare("select mailProprietaire from liste where idListe = :id");
        $bdd->bindParam(':id', $idListe);
        $bdd->execute();
        $donnees = $bdd->fetch();
        return $donnees[0];
    }

    public function changeProprietaire($mailUser, $idListe){
        $bdd = DB::getInstance()->getPDO()->prepare("UPDATE Liste SET mailProprietaire = :mail WHERE idListe = :id");
        $bdd->bindParam(':mail', $mailUser);
        $bdd->bindParam(':id', $idListe);
        $bdd->execute();
    }

    private function deleteAllListNotification($idListe){
        $bddRequete = DB::getInstance()->getPDO()->prepare("DELETE FROM Notification WHERE idListe = :idListe");
        $bddRequete->bindParam(":idListe", $idListe);
        $bddRequete->execute();
    }

    public function getListeProprietaire($mail){
        $listes = array();
        $bdd = DB::getInstance()->getPDO()->prepare("select * from Liste where mailProprietaire = :mail");
        $bdd->bindParam(':mail', $mail);
        $bdd->execute();
        while($donnees = $bdd->fetch()){
            $liste = DB::getInstance()->loadListe($donnees['idListe']);
            array_push($listes, $liste);
        }
        return $listes;
    }

    public function getListeInvite($mail){
        $listes = array();
        $bdd = DB::getInstance()->getPDO()->prepare("select * from Membre where mail = :mailMembre and idListe not in (select idListe from Liste where mailProprietaire = :mail)");
        $bdd->bindParam(':mailMembre', $mail);
        $bdd->bindParam(':mail', $mail);
        $bdd->execute();
        while($donnees = $bdd->fetch()){
            $liste = DB::getInstance()->loadListe($donnees['idListe']);
            array_push($listes, $liste);
        }
        return $listes;
    }

    public function getNotifTache($idNotif){
      $bdd = DB::getInstance()->getPDO()->prepare("SELECT * from NotificationSupprTache WHERE idNotification = :idNotif");
      $bdd->bindParam(":idNotif", $idNotif);
      $bdd->execute();
      if ($donnes = $bdd->fetch()){
        $tache = DB::getInstance()->loadTache($donnes['idTache']);
      }
      return $tache;
    }


}

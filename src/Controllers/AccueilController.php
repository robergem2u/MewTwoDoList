<?php

namespace App\Controllers;

use App\Controllers\Controller;
use App\Modeles\DB;

class AccueilController extends Controller {

	public function index() {
		$helloWorld = "Hello world";

		return $this->render('accueil', compact('helloWorld'));
	}
	
	public function ajouterUtilisateur(){
		$mail = $_POST['mail'] ?? null;
		$nom_user = $_POST['nomUser'] ?? null;
		$prenomUser  = $_POST['prenomUser'] ?? null;
		$pseudoUser  = $_POST['pseudoUser'] ?? null;
		$mdp  = $_POST['mdp'] ?? null;
		$photo = $_POST['photo'] ?? null;
		
		// On verifie les champs postés
		if (is_null($pseudo)) {
			return false;
		}

		if (is_null($mdp)) {
			return false;
		}

		if (is_null($mail)) {
			return false;
		}
		
		
		// Ensuite
		// On teste le pseudo
		if (!preg_match('/^[a-zA-Z0-9]{1,16}$/', $pseudoUser)) {
			return false;
		}

		// On teste le mot de passe
		if (!preg_match('/^[a-zA-Z0-9]{4,99}$/', $mdp)) {
			return false;
		}

		if($nom_user) {
			if(!preg_match('/^[a-zA-Z0-9\-]{1,30}$/', $nom_user)) {
				return false;
			}
		}

		if($prenomUser) {
			if(!preg_match('/^[a-zA-Z0-9\-]{1,30}$/', $prenomUser)) {
				return false;
			}
		}

		if($mail) {
			if(!preg_match('/^[a-zA-Z0-9\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-]+$/', $mail) || strlen($mail) > 100) {
				return false;
			}
		}
		
		$bdd = DB::getInstance();

		$req = $bdd->prepare('SELECT pseudo_user FROM utilisateur WHERE pseudo_user=? LIMIT 1');
		$req->execute([$pseudo_user]);
		$donnee = $req->fetch();
		if(!empty($donnee)) {
			return false;
		}
	}
	
	public function getUtilisateurConnecte(){
		$connecte = $_SESSION['pseudo_user'] ?? null;

		if(!$connecte) {
			return false;
		}

		$bdd = DB::getInstance();
		$req = $bdd->prepare('select * from utilisateur where pseudo=? limit 1');
		$req->execute([$connecte]);


		$utilisateur = $req->fetchAll();

		if(empty($utilisateur)) {
			return false;
		}

		$utilisateur = $utilisateur[0];

		$this->render('voir_utilisateur', compact('utilisateur'));
	}

}

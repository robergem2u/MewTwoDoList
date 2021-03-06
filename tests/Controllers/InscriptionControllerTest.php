<?php
namespace Tests\Controllers;

use App\Controllers\AccueilController;
use PHPUnit\Framework\TestCase;
use App\Modeles\DB;


/**
 * Tests InscriptionControllerTest
 */
class InscriptionControllerTest extends TestCase {

public function testInscription() {

	$fonction = new FonctionTest();
    	
		$res = false;

		$bdd = DB::getInstance()->getPDO();

		$requete = $bdd->prepare("SELECT count(*) FROM Utilisateur");
		$requete->execute();
		
		$compteur = 0;
		$compteur2 = 0;
		while($donnees = $requete->fetch()){
			$compteur++;
		}		

        //Compte le nb de lignes avant l'insertion
		$nblignes = $compteur;

		$fonction->ajouterUtilisateurTest();

        //Compte le nombre de lignes après l'insertion
		$nblignesdeux = $nblignes + 1;
		$requete = $bdd->prepare("SELECT count(*) FROM Utilisateur");
		$requete->execute();

		while($donnees = $requete->fetch()){
			$compteur2++;
		}
	
		if($compteur2 == $nblignesdeux){
			$res = true;	
		}

		$fonction->supprimerUtilisateurTest();

		$this->assert($res, true, 'insertion reussie');
	}
}
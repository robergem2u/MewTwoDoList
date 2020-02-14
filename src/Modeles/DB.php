<?php

namespace App\Modeles;

use Exception;
use PDO;
use Utilisateur;

class DB {

  /**
   * @var Singleton
   * @access private
   * @static
   */
  private static $_instance = null;

   /**
    * Constructeur de la classe
    *
    * @param void
    * @return void
    */
   private function __construct() {
   }

   /**
    * Creer une instance de PDO
    * @return PDO Instance de PDO
    */
   public static function getInstance() {

     if(is_null(self::$_instance)) {

      try {

        if(file_exists('../src/Config/config.ini')) {
          $config = parse_ini_file('../src/Config/config.ini');
        } elseif(file_exists('src/Config/config.ini')) {
          $config = parse_ini_file('src/Config/config.ini');
        } else {
            throw new Exception('Pas de fichier de config');
        }



        self::$_instance = new PDO("mysql:host=$config[host];dbname=$config[db];charset=utf8", $config['user'], $config['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
      } catch(Exception $e) {
        die($e->getMessage());
      }
    }

    return self::$_instance;
  }

    /**
     * Creer une instance de PDO
     * @return PDO Instance de PDO
     */
    public static function getFirstInstance() {

        if(is_null(self::$_instance)) {

            try {

                if(file_exists('../src/Config/config.ini')) {
                    $config = parse_ini_file('../src/Config/config.ini');
                } elseif(file_exists('src/Config/config.ini')) {
                    $config = parse_ini_file('src/Config/config.ini');
                } else {
                    throw new Exception('Pas de fichier de config');
                }



                self::$_instance = new PDO("mysql:host=$config[host];charset=utf8", $config['user'], $config['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            } catch(Exception $e) {
                die($e->getMessage());
            }
        }

        return self::$_instance;
    }

    public function loadUtilisateur($mail)
    {

        /*Préparation des requêtes*/
        $verifMail = prepare("SELECT * FROM Utilisateur where mail = :mailVerification");

        /*On test si le mail existe dans la base de données*/
        $verifMail->bindParam(':mailVerification', $mail);
        $verifMail->execute();

        $utilisateur = new Utilisateur($verifMail[""]);

        return $utilisateur;
    }

}


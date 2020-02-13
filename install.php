<?php

try {
$config = parse_ini_file("src/Config/config.ini");
} catch(Exception $e) {
	echo "Merci de creer un fichier de configuration dans 'src/Config/config.ini'";
	die;
}

$query = file_get_contents("sql.sql");



require 'src/Modeles/DB.php';
$bdd = App\Modeles\DB::getInstance();

$stmt = $bdd->prepare($query);

if ($stmt->execute())
     echo "Succes";
else
     echo "Echec";
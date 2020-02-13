<?php

$page = $_GET['page'] ?? header('Location: index.php?page=accueil');

//var_dump($page);die;

// Le router de l'application est ici

switch($page) {

	case 'accueil':
		(new App\Controllers\AccueilController)->index();
		break;

    case 'login':
        (new App\Controllers\LoginController())->login();
        break;

    case 'inscription':
        (new App\Controllers\InscriptionController())->inscription();
        break;

    case 'compte':
        (new App\Controllers\CompteController())->compte();
        break;

    case 'sessioncreate':
        (new App\Controllers\SessionCreateController())->sessionCreate();
        break;

	default: // Si, rien, alors erreur 404
		(new App\Controllers\ErreurController)->_404();
		break;
}


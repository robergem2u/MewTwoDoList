# Projet PPIL, 2020

## Initialiser la base de données:

Creer un fichier de configuration dans **src/Config/config.ini**.

Un exemple de configuration est fourni dans **src/Config/config-exemple.ini**.


## Contenu du fichier src/Config/config.ini

	; Nom d'utilisateur de la base de données
	user=root

	; Mot de passe de la base de données
	pass=root

	; Le nom de la base de donnee utilisee
	db=ppil

	; Le nom d'hôte de la base de donnees
	host=localhost

## Mise en place de la BDD

	php install.php
	
## Mise en place du serveur HTTP

	php -S localhost:8080 -t ./public/
	
Puis se rendre sur la page : **//localhost:8080**

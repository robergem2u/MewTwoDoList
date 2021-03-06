<?php
use App\Modeles\DB;

?>
<!DOCTYPE html>
<link rel="icon" href="assests/favicon.png" />
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $titre ?></title>
    <script src="cdn/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assests/favicon.ico" />
    <link rel="stylesheet" href="cdn/bootstrap-4.3.1-dist/css/bootstrap.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="cdn/bootstrap-4.3.1-dist/js/bootstrap.js"></script>
</head>
<body>

<?php $page = $_GET['page'] ?? '';
if ($page != "login" && $page != "recuperationCompte" && $page != "disconnect" && $page != "inscription" && $page != "modifTache" && $page != "ajoutTache"){
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="?page=accueil">MewTwoDoList</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <li class="nav-item">
                </li>
            <li class="nav-item active">
                <li class="nav-item">
            </li>
            <li class="nav-item active">
                <li class="nav-item">
            </li>
        </ul>
    </div>
    <?php
    } ?>



    <!-- affichage des icônes de menu -->
    <?php if (isset($_SESSION['user']) && $page != "modifTache" && $page != "ajoutTache") {?>
        <div class="btn-group">

            <button class="btn btn-default dropdown-toggle mr-4 float-right" type="button" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <img src="assests/notif.png" alt="notification" width="20" height="20">
                <span class="badge badge-pill "><?php
                    $user = unserialize($_SESSION['user']);
                    $countNotif = 0;
                    $countNotifNonRead = 0;
                    $bdd = DB::getInstance();
                    $mail = $user->getMail();
                    $requete = $bdd->loadNotifs($mail);

                    foreach ($requete as $donneesNotif) {
                        if (!$donneesNotif->isLu()) {
                            $countNotifNonRead++;
                        }
                        $countNotif++;
                    }
                    $_SESSION['user'] = serialize($user);
                    echo $countNotifNonRead; ?></span>
            </button>
            <?php
            $user = unserialize($_SESSION['user']);
            $mail = $user->getMail();
                $notifs = DB::getInstance()->loadNotifs($mail);
                $i = 0;
                ?><div class="dropdown-menu dropdown-menu-right"><?php
                    if (!empty($notifs)) {

                for ($i = 0;
                $i < 3;
                $i++) {
                if ($i < $countNotif) { ?>
                <div
                <button  onclick="window.location.href='?page=notification'" class="dropdown-item"><?= $notifs[$i]->getContenu() ?></button>
            </div> <?php
        }
        }
        }
                    if($countNotif == 0) {
                        ?>
                        <button class="btn dropdown-item" onclick="window.location.href='?page=notification'">Mes notifications
                        </button>
                        <?php
                    }
            ?>
                </div>
        </div>
        <div class="btn-group">
            <button class="btn btn-default dropdown-toggle mr-4 float-right" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <?php
                $user = unserialize($_SESSION['user']);
                if ($user->getPhoto() == null) {
                    echo "<img src='assests/profil.png' alt='profil' class=\"img-rounded\" width=\"20\" height=\"20\">";
                } else {
                    echo "<img src=\"" . $user->getPhoto() . "\" class=\"img-rounded\" width=\"20\" height=\"20\" alt='profil'>";
                } ?>
            </button>

            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="?page=compte">Mon Compte</a>
                <?php if (isset($_SESSION['user'])) {?>
                    <a class="dropdown-item" href="?page=disconnect">Se déconnecter</a>
                <?php }else{ ?>
                    <a class="dropdown-item" href="?page=disconnect">Se connecter</a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

        </nav>



<main>
    <section class="container">
        <?= $contenu ?>
    </section>
</main>

</body>


</html>

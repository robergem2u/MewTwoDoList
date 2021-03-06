<?php
use App\Modeles\DB;
?>
<script src="javascript/tri_liste.js"></script>
<div class="jumbotron-fluid text-center">

    <?php
if(isset($_SESSION["user"])){?>
    <div class="jumbotron justify-content-center">
        <h1>Mes listes :</h1>
        <br>
        <br>
        <h4>Trier par:</h4>
        <select onchange="sort_by_name(this.value)" onload="sort_by_name(this.value)">
            <option value="alphab">Ordre alphabétique</option>
            <option value="alphabInverse">Ordre alphabétique inverse</option>
            <option value="debRecent">Date de début la plus récente</option>
            <option value="debAncien">Date de début la plus ancienne</option>
            <option value="finRecent">Date de fin la plus récente</option>
            <option value="finAncien">Date de fin la plus ancienne</option>
        </select>
        <br>
        <br>
        <br>
    </div>
    <div class="jumbotron-fluid">
        <p>Mes listes :</p>
        <div class="row justify-content-center" id="liste" style="display: flex">
            <?php
            $user = unserialize($_SESSION["user"]);
            $listes = DB::getInstance()->getListeProprietaire($user->getMail());
            foreach ($listes as $liste) {
                    ?>
                    <div class="jumbotron col-auto" id="<?= $liste->getIntituleListe() ?>\<?= $liste->getDateCreation() ?>\<?= $liste->getDateFin() ?>" style="border: solid; padding: 30px; margin: 10px;"
                         onclick="window.location.href = '?page=liste&id=<?= $liste->getIdListe() ?>'">
                        <nom_listes><?= htmlspecialchars($liste->getIntituleListe()) ?></nom_listes>
                        <?php if ($liste->getDateFin() == null) { ?>
                            <dates><br><br>A partir du <?= htmlspecialchars($liste->getDateCreation()) ?><br></dates>
                        <?php } else { ?>
                            <dates><br><br>Du <?= htmlspecialchars($liste->getDateCreation()) ?>
                                <br>au <?= htmlspecialchars($liste->getDateFin()) ?></dates>
                        <?php } ?>
                    </div>
                <?php }
            $_SESSION['user'] = serialize($user); ?>

        </div>
        <div class="jumbotron-fluid col-auto">
            <a onclick="window.location.href = '?page=creationListe'"><img src="assests/plus.png" alt="Ajouter une liste" class="small"/></a>
        </div>
    </div>
    <div class="jumbotron-fluid">
        <p>Les listes où je suis invité(e) :</p>
    </div>
    <div class="row justify-content-center" id="liste2" style="display: flex">
        <?php
            $user = unserialize($_SESSION["user"]);
            $mail = $user->getMail();
            $bdd = DB::getInstance();
            /*$listesTotal = $bdd->recupererListesMembres($mail);
            $listesInvite = array();
            foreach ($listesTotal as $listeMembre){
                if (!empty($listes)) {
                    if ($listeMembre->getMailProprietaire() != unserialize($_SESSION["user"])->getMail() && !in_array($listeMembre, $listesInvite)) {
                        array_push($listesInvite, $listeMembre);
                    }
                }else{
                    array_push($listesInvite, $listeMembre);
                }
            }*/
            $listesInvite = $bdd->getListeInvite($mail);
            foreach ($listesInvite as $listeInvite){
            ?>

                <div id="<?php echo $listeInvite->getIntituleListe() ?>\<?php echo $listeInvite->getDateCreation() ?>\<?php echo $listeInvite->getDateFin() ?>" class="jumbotron col-auto" style="border: solid; padding: 30px; margin: 10px;"
                     onclick="window.location.href = '?page=liste&id=<?= $listeInvite->getIdListe() ?>'">
                    <nom_listes><?= $listeInvite->getIntituleListe(); ?></nom_listes>
                    <?php if ($listeInvite->getDateFin() == null) { ?>
                        <dates><br><br>A partir du <?= $listeInvite->getDateCreation() ?><br></dates>
                    <?php } else { ?>
                        <dates><br><br>Du <?= $listeInvite->getDateCreation() ?>
                            <br>au <?= $listeInvite->getDateFin() ?></dates>
                    <?php } ?>
                </div>

            <?php }
        ?>
    </div>
<?php } ?>
    <script>sort_by_name("alphab");</script>
</div>

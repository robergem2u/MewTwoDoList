
<link rel="icon" href="assests/favicon.ico" />
<?php


use \App\Modeles\DB;
use \App\Controllers\ModificationTacheController;
$modifTache = new App\Controllers\ModificationTacheController;
 ?>

 <?php
 if (isset($_GET['id'])) {
   $tache = DB::getInstance()->loadTache($_GET['id']);
   $intitule = $tache->getIntituleTache();
   $id = $tache->getIdTache();
   $idListe = $tache->getIdListeTache();

   if (isset($_SESSION["user"]))
   {
     $user = unserialize($_SESSION["user"]);
     $liste = DB::getInstance()->loadListe($idListe);
     if ($user->getMail() == $liste->getMailProprietaire()){
       $proprio = "true";
     } else {
       $proprio = "false";
     }
     $mail = $user->getMail();
   }


   if (isset($_POST['texte'])){
       if (!empty($_POST['texte'])) {
           $tache->setIntituleTache($_POST['texte']);
           $tache->modifBDD();
           echo "<script type='text/javascript'>",
           "window.opener.location.reload(true);",
           "window.close();",
           "</script>";

       }else{ ?>
           <em> L'intitulé de la liste n'est pas rempli'. </em><br>
       <?php }
   }
 } else {
   $intitule = "";
 }
 ?>

<div class="jumbotron text-center">
<h1 class="display-4"> Modification de la tâche </h1>
</div>
<button class="btn float-right " type="button" data-toggle="dropdown" data-target="membres" data-proprio="<?php echo $proprio; ?>" data-mail="<?php echo $mail; ?>"
        aria-haspopup="listbox" aria-expanded="false" id="delete" value="<?php echo $id.' '.$idListe; ?>"><img src="assests/delete.png" width="20" height="20"></button>


<form class="form-sign_in" method="POST" action=<?php echo "?page=modifTache&id=".$_GET['id'] ;?> >
<div class="input-group">
  <div class="input-group-pretend">
    <span class="input-group-text"> Modification de la tâche </span>
  </div>
  <textarea class="form-control" aria-label="Intitulé de la tâche" name="texte" placeholder="<?php echo $intitule; ?>"></textarea>
</div>
<div class="fixed-bottom">
  <button class="btn  btn-primary  text-uppercase" onclick="window.close()"> Annuler </button>
  <div class="float-md-right">
    <button class="btn  btn-primary  text-uppercase" type="submit"=> Confirmer </button>
  </div>
</div>
</form>

<script type="text/javascript" src="cdn/jquery.js"> </script>
<script type="text/javascript" src="javascript/delete_tache.js"></script>

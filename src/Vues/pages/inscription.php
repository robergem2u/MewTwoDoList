<link type="text/css" rel="stylesheet" href="css/sign_in.css">
<link type="text/css" rel="stylesheet" href="css/all.css">
<link type="text/css" rel="stylesheet" href="css/bootstrap.min.css">
<script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="js/jquery.slim.min.js"></script>
<script src="particles.js"></script>

<body>
<div class="container">
    <div class="row">
        <div class="col-sm-9 col-md-7 col-lg-7 mx-auto">
            <div class="card card-sign_in my-5">
                <div class="card-body">
                    <h5 class="card-title text-center">Inscription</h5>
                    <form class="form-sign_in">

                        <div class="form-label-group">
                            <input type="text" id="inputNom" class="form-control" placeholder="Nom"
                                   required autofocus>
                            <label for="inputNom">Votre nom</label>
                        </div>

                        <div class="form-label-group">
                            <input type="text" id="inputPrenom" class="form-control" placeholder="Prénom"
                                   required autofocus>
                            <label for="inputPrenom">Votre prénom</label>
                        </div>

                        <div class="form-label-group">
                            <input type="email" id="inputEmail" class="form-control" placeholder="Email"
                                   required autofocus>
                            <label for="inputEmail">Votre adresse mail</label>
                        </div>

                        <div class="form-label-group">
                            <input type="password" id="inputPassword" class="form-control" placeholder="Mot de passe"
                                   required>
                            <label for="inputPassword">Votre mot de passe</label>
                        </div>

                        <div class="form-label-group">
                            <input type="password" id="inputPasswordConf" class="form-control" placeholder="Confirmer votre mot de passe"
                                   required>
                            <label for="inputPasswordConf">Confirmer votre mot de passe</label>
                        </div>
                        <br>

                        <button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit">S'inscrire</button>
                        <hr class="my-4">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
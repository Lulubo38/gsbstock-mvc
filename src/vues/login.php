<!DOCTYPE html>
<html lang="fr">

<head>
    <title>GSBstok</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./style/style.css">
    <link rel="stylesheet" href="./style/admin.css">
</head>

<body>
    <h1>Connexion Utilisateur</h1>
    <form method="POST" action="index.php?uc=validLogin" align="center">
        <input type="text" name="nom" autocomplete="off" placeholder="Nom d'utilisateur">
        <br>
        <input type="password" name="mot_de_passe" autocomplete="off" placeholder="Mot de passe">
        <br><br>
        <input type="submit" name="envoi" value="Se connecter">
    </form>
    <br>
    <div style="display: flex; justify-content: center; align-items: center;">
        <button>
            <a href="./index.php?uc=inscription" style="color:black; text-decoration:none;">Inscription</a>
        </button>
    </div>
</body>

</html>
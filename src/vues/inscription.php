<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>

<body>
    <h2>Inscription</h2>
    <form method="POST" action="./index.php?uc=validInscription" align="center">
        <label for="nom">Nom d'utilisateur :</label>
        <input type="text" id="nom" name="nom" autocomplete="off" required><br><br>

        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="email" autocomplete="off" required><br><br>

        <label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="off" required><br><br>

        <input type="submit" value="S'inscrire">
    </form>

    <!-- Bouton de redirection vers la page de connexion -->
    <div style="display: flex; justify-content: center; align-items: center;">
        <button>
            <a href="./index.php?uc=login" style="color:black; text-decoration:none;">Se connecter</a>
        </button>
    </div>
</body>

</html>
<?php

//definition de $uc pour connaitre la page souhaitee
$action = $_GET["action"];

switch ($action) {
    case "validLogin":
        if (isset($_POST['envoi'])) {
            if (!empty($_POST['nom']) && !empty($_POST['mot_de_passe'])) {
                $nom = htmlspecialchars($_POST['nom']);
                $mot_de_passe = $_POST['mot_de_passe'];

                // Rechercher l'utilisateur dans la table `utilisateurs` avec son rôle
                $user = $userDataAccess->login($nom);
                // Si l'utilisateur est trouvé dans la table `utilisateurs`, vérifier le mot de passe
                if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    $_SESSION['nom'] = $nom;
                    $_SESSION['id'] = $user['id_utilisateur'];
                    $_SESSION['id_role'] = $user['id_role']; // Utiliser le rôle de l'utilisateur

                    // Redirection en fonction du rôle de l'utilisateur
                    if ($user['id_role'] == 1) {
                        // L'utilisateur est un administrateur
                        header('location: index.php?uc=dashboard_admin');
                        exit();
                    } elseif ($user['id_role'] == 2) {
                        // L'utilisateur est un utilisateur
                        header('location: index.php?uc=dashboard');
                        exit();
                    } elseif ($user['id_role'] == 3) {
                        // L'utilisateur est un super administrateur
                        header('location: index.php?uc=dashboard_superadmin');
                        exit();
                    }
                } else {
                    echo "Identifiant ou mot de passe incorrect.";
                }
            } else {
                echo "Veuillez compléter tous les champs.";
            }
        }
        break;
}

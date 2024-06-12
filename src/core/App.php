<?php
session_start();
require_once("../src/models/User.php");
require_once("../src/models/Stock.php");
require_once("../src/models/Order.php");
$userDataAccess = new User();
$stockDataAccess = new Stock();
$orderDataAccess = new Order();
//definition de $uc pour connaitre la page souhaitee
$uc = empty($_GET["uc"]) ? "login" : $_GET["uc"];

switch ($uc) {
    case "login":
        include "../src/vues/login.php";
        break;
    case "inscription":
        include "../src/vues/inscription.php";
        break;
    case "user":
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id'])) {
            // Rediriger vers la page de connexion
            header("Location: index.php");
            exit;
        } else {
            include "../src/controllers/userController.php";
        }
        break;
    case "order":
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id'])) {
            // Rediriger vers la page de connexion
            header("Location: index.php");
            exit;
        } else {
            include "../src/controllers/orderController.php";
        }
        break;
    case "stock":
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id'])) {
            // Rediriger vers la page de connexion
            header("Location: index.php");
            exit;
        } else {
            include "../src/controllers/stockController.php";
        }
        break;
    case "dashboard_admin":
        // Vérifier si l'utilisateur est connecté et s'il est administrateur
        if (!isset($_SESSION['id']) || $_SESSION['id_role'] != 1) {
            // Rediriger vers la page de connexion
            header("Location: index.php");
            exit;
        } else {
            $stocks = $stockDataAccess->getStocks();
            include "../src/vues/dashboard_admin.php";
        }
        break;
    case "dashboard":
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['id'])) {
            // Rediriger vers la page de connexion
            header("Location: index.php");
            exit;
        } else {
            $stocks = $stockDataAccess->getStocks();
            include "../src/vues/dashboard.php";
        }
        break;
    case "dashboard_superadmin":
        include "../src/vues/dashboard_superadmin.php";
        break;
    case "populaire":
        // Récupérer les produits les plus commandés depuis la base de données

        try {
            $produits_populaires = $stockDataAccess->getPopular();
            include "../src/vues/populaire.php";
        } catch (PDOException $e) {
            // En cas d'erreur, afficher un message d'erreur
            echo "Erreur : " . $e->getMessage();
        }
        break;

    case "logout":
        session_start();
        session_destroy();
        header("Location: index.php");
        exit();
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
    case "validInscription":
        if (!empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['mot_de_passe'])) {
            $nom = htmlspecialchars($_POST['nom']);
            $email = htmlspecialchars($_POST['email']);
            $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT); // Hash du mot de passe

            // Vérification si l'utilisateur existe déjà

            $existingUser = $userDataAccess->checkUser($nom);

            if ($existingUser) {
                echo "Cet utilisateur existe déjà.";
            } else {
                $user = $userDataAccess->createUser($nom, $email, $mot_de_passe);
                if ($user) {
                    $_SESSION['pseudo'] = $nom;
                    $_SESSION['id'] = $user['id_utilisateur'];
                    header('Location: index.php?uc=dashboard'); // Redirection après inscription
                    exit();
                }
            }
        } else {
            echo "Veuillez compléter tous les champs...";
        }
        break;
}

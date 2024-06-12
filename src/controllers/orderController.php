<?php
//definition de $uc pour connaitre la page souhaitee
$action = $_GET["action"];

switch ($action) {
    case "historique-commande":
        $commandes = $orderDataAccess->getOrders();
        include "../src/vues/commandes/historique-commande.php";
        break;
    case "historique_utilisateur":
        //ne fonctionne pas (nécessite une modif de la base)
        // Récupérer les commandes de l'utilisateur actuellement connecté depuis la table demandes_commandes
        $id_utilisateur = $_SESSION['id'];
        $orderDataAccess->getOrdersByUserId($id_utilisateur);
        include "../src/vues/commandes/historique_utilisateur.php";
        break;
    case "passer_commande":
        $stocks = $stockDataAccess->getStocks();
        include "../src/vues/commandes/passer_commande.php";
        break;
    case "traitement_commande":
        // Vérifier si le formulaire a été soumis en méthode POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Récupérer les données du formulaire
            $stock_id = $_POST['produit'];
            $quantite = $_POST['quantite'];

            // Récupérer l'ID utilisateur depuis la session
            $id_utilisateur = $_SESSION['id'];

            try {
                $orderDataAccess->createOrder($stock_id, $quantite);
                // Afficher un message de confirmation
                echo "Votre demande de commande a été enregistrée. Elle est en attente d'approbation de l'administrateur.";
                include "../src/vues/commandes/traitement_commande.php";
            } catch (PDOException $e) {
                // En cas d'erreur, afficher un message d'erreur
                echo "Erreur : " . $e->getMessage();
            }
        }
        break;
    case "valider_demande":
        // Récupérer l'ID de la demande de commande depuis l'URL
        $id_demande = $_GET['id'];

        // Mettre à jour le statut de la demande de commande dans la base de données
        $stockDataAccess->updateStatut($id_demande);

        // Récupérer la quantité demandée et l'ID du stock correspondant à la demande
        $info_demande = $orderDataAccess->getInfoDemande($id_demande);


        $id_stock = $info_demande['id_stock'];
        $quantite_demandee = $info_demande['quantite_demandee'];

        // Mettre à jour la quantité disponible dans le stock correspondant

        $stockDataAccess->updateStock($quantite_demandee, $id_stock, "-");

        // Rediriger vers la page dashboard.php
        header("Location: index.php?uc=dashboard_admin");
        break;
    case "commande-mois":
        // Requête SQL pour récupérer le total des produits commandés par mois
        $resultats = $orderDataAccess->getCommandeMois();
        include "../src/vues/commandes/commande-mois.php";
        break;
    case "refuser_demande":
        // Vérifier si l'ID de la demande est fourni dans l'URL
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            // Rediriger vers une page d'erreur ou à une autre page appropriée
            header("Location: erreur.php");
            exit;
        }

        // Récupérer l'ID de la demande de commande depuis l'URL
        $id_demande = $_GET['id'];

        // Supprimer la demande de commande de la base de données
        $orderDataAccess->refuserDemande($id_demande);

        // Rediriger vers la page dashboard.php
        header("Location: index.php?uc=dashboard_admin");
        break;
}

<?php
session_start();
require_once 'database.php'; // Inclure le fichier de connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id'])) {
    // Rediriger vers la page de connexion
    header("Location: index.php");
    exit;
}

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION['id_role'] != 1) {
    // Rediriger vers la page d'accueil appropriée
    header("Location: dashboard.php");
    exit;
}

// Récupérer les commandes passées par les utilisateurs
$requete_commandes = $pdo->prepare('SELECT * FROM commandes_utilisateurs');
$requete_commandes->execute();
$commandes_utilisateurs = $requete_commandes->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes des Utilisateurs</title>
</head>
<body>
    <h1>Commandes des Utilisateurs</h1>

    <div class="dashboard-container">
        <a href="logout.php">Déconnexion</a>
        <a href="dashboard_admin.php">Retour au Tableau de Bord Administrateur</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Commande</th>
                <th>Utilisateur</th>
                <th>Produit</th>
                <th>Quantité</th>
                <!-- Ajoutez d'autres colonnes au besoin -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commandes_utilisateurs as $commande) : ?>
                <tr>
                    <td><?php echo $commande['id_commande']; ?></td>
                    <td><?php echo $commande['utilisateur']; ?></td>
                    <td><?php echo $commande['produit']; ?></td>
                    <td><?php echo $commande['quantite']; ?></td>
                    <!-- Ajoutez d'autres colonnes au besoin -->
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>

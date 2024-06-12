<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Historique des commandes</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./style/dashboard.css"> <!-- Lien vers le fichier CSS externe -->
</head>

<body>
    <h1>Historique des commandes</h1>

    <div class="dashboard-container">
        <a href="index.php?uc=dashboard">Retour au tableau de bord</a>
        <a href="index.php?uc=logout">Déconnexion</a>
    </div>

    <div id="listeCommandes">
        <?php if (count($commandes) > 0) : ?>
            <table>
                <tr>
                    <th>Date de commande</th>
                    <th>Produit</th>
                    <th>Quantité</th>
                </tr>
                <?php foreach ($commandes as $commande) : ?>
                    <tr>
                        <td><?php echo $commande['date_demande']; ?></td>
                        <td><?php echo $commande['id_stock']; ?></td>
                        <td><?php echo $commande['quantite_demandee']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else : ?>
            <p>Aucune commande trouvée.</p>
        <?php endif; ?>
    </div>
</body>

</html>
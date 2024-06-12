<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des commandes</title>
    <link rel="stylesheet" type="text/css" href="./style/histo.css">
</head>

<body>
    <h1>Historique des commandes</h1>

    <div>
        <a href="index.php?uc=dashboard_admin">Retour au tableau de bord</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Demande</th>
                <th>ID Stock</th>
                <th>Quantité demandée</th>
                <th>Date Demande</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($commandes as $commande) : ?>
                <tr>
                    <td><?php echo $commande['id_demande']; ?></td>
                    <td><?php echo $commande['id_stock']; ?></td>
                    <td><?php echo $commande['quantite_demandee']; ?></td>
                    <td><?php echo $commande['date_demande']; ?></td>
                    <td><?php echo $commande['statut']; ?></td>
                    <td>
                        <a href="index.php?uc=order&action=valider_demande&id=<?php echo $commande['id_demande']; ?>">Valider</a>
                        <!-- Lien pour refuser une commande -->
                        <a href="index.php?uc=order&action=refuser_demande&id=<?php echo $commande['id_demande']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir refuser cette commande ?')">Refuser</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
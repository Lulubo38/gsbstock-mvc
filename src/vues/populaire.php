<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Produits Populaires</title>
    <link rel="stylesheet" href="./style/populaire.css"> <!-- Lien vers le fichier CSS externe -->
</head>

<body>
    <h1>Produits Populaires</h1>
    <table>
        <thead>
            <tr>
                <th>ID Produit</th>
                <th>Total Commandes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits_populaires as $produit) : ?>
                <tr>
                    <td><?php echo $produit['id_stock']; ?></td>
                    <td><?php echo $produit['total_commandes']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
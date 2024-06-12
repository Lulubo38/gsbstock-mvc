<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Total des Produits Commandés par Mois</title>
    <meta charset="utf-8">
</head>

<body>
    <h1>Total des Produits Commandés par Mois</h1>

    <table>
        <tr>
            <th>Mois</th>
            <th>Total</th>
        </tr>
        <?php foreach ($resultats as $resultat) : ?>
            <tr>
                <td><?php echo $resultat['mois']; ?></td>
                <td><?php echo $resultat['total']; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>
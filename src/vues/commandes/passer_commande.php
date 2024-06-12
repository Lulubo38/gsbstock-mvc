<!DOCTYPE html>
<html>

<head>
    <title>Passation de Commandes</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="./style/commande.css">
</head>

<body>
    <h1>Passation de Commandes</h1>

    <form action="index.php?uc=order&action=traitement_commande" method="post">
        <label for="produit">Produit :</label>
        <select name="produit" id="produit">
            <?php
            foreach ($stocks as $stock) {
                echo '<option value="' . $stock['id_stock'] . '">' . $stock['nom'] . ' (Quantité disponible : ' . $stock['quantite_disponible'] . ')</option>';
            }
            ?>
        </select>
        <br><br>
        <label for="quantite">Quantité :</label>
        <input type="number" name="quantite" id="quantite" min="1" required>
        <br><br>
        <input type="submit" value="Passer Commande">
    </form>
</body>

</html>
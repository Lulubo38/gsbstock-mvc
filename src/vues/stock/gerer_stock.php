<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Stocks</title>
    <link rel="stylesheet" type="text/css" href="./style/style.css">
</head>

<body>
    <h1>Gérer les Stocks</h1>

    <div>
        <a href="./index.php?uc=dashboard_admin">Retour au tableau de bord</a>
        <a href="./index.php?uc=logout">Déconnexion</a>
    </div>

    <h2>Ajouter un Nouveau Stock</h2>
    <form action="./index.php?uc=stock&action=ajouter_stock" method="post">
        <label for="nom_nouveau_stock">Nom :</label>
        <input type="text" name="nom_nouveau_stock" id="nom_nouveau_stock" required>
        <label for="quantite_nouveau_stock">Quantité :</label>
        <input type="number" name="quantite_nouveau_stock" id="quantite_nouveau_stock" min="1" required>
        <label for="description_nouveau_stock">Description :</label>
        <input type="text" name="description_nouveau_stock" id="description_nouveau_stock" required>
        <label for="type_nouveau_stock">Type :</label>
        <select name="type_nouveau_stock" id="type_nouveau_stock">
            <option value="medicament">Médicament</option>
            <option value="materiel">Matériel</option>
        </select>
        <input type="submit" name="ajouter_stock" value="Ajouter Stock">
    </form>

    <h2>Liste des Stocks Existants</h2>
    <table>
        <thead>
            <tr>
                <th>ID Stock</th>
                <th>Nom</th>
                <th>Quantité Disponible</th>
                <th>Description</th>
                <th>Type</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stocks as $stock) : ?>
                <tr>
                    <td><?php echo $stock['id_stock']; ?></td>
                    <td><?php echo $stock['nom']; ?></td>
                    <td><?php echo $stock['quantite_disponible']; ?></td>
                    <td><?php echo $stock['description']; ?></td>
                    <td><?php echo $stock['type']; ?></td>
                    <td>
                        <form action="./index.php?uc=stock&action=modifier_quantite" method="post">
                            <input type="hidden" name="id_stock_a_modifier" value="<?php echo $stock['id_stock']; ?>">
                            <input type="number" name="nouvelle_quantite" min="0" required>
                            <input type="submit" name="modifier_quantite" value="Modifier Quantité">
                        </form>
                        <form action="./index.php?uc=stock&action=supprimer_stock" method="post">
                            <input type="hidden" name="id_stock_a_supprimer" value="<?php echo $stock['id_stock']; ?>">
                            <input type="submit" name="supprimer_stock" value="Supprimer">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
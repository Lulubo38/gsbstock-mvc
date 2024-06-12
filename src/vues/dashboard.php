<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Consultation des Stocks</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="./style/dashboard.css"> <!-- Lien vers le fichier CSS externe -->
    <style>
        .stock-bas {
            color: red;
        }
    </style>
</head>

<body>
    <h1>Consultation des Stocks</h1>

    <div class="dashboard-container">
        <a href="index.php?uc=logout">Déconnexion</a>
        <a href="index.php?uc=order&action=passer_commande" class="passer-commande-btn">Passer Commande</a> <!-- Lien vers la page passer_commande.php -->
        <?php if ($_SESSION['id_role'] != 1) : ?>
            <a href="index.php?uc=order&action=historique_utilisateur" class="historique-commande-btn">Historique des commandes</a>
        <?php endif; ?>
        <a href="index.php?uc=populaire" class="produits-populaires-btn">Produits Populaires</a> <!-- Lien vers la page des produits populaires -->
    </div>

    <label for="type">Afficher :</label>
    <select id="type">
        <option value="tous">Tous</option>
        <option value="medicament">Médicaments</option>
        <option value="materiel">Matériel</option>
    </select>

    <div id="listeProduits">
        <!-- Contenu généré dynamiquement en JavaScript -->
    </div>

    <script>
        document.getElementById('type').addEventListener('change', function() {
            var typeSelectionne = this.value;
            var tousLesStocks = <?php echo json_encode($stocks); ?>;

            var stocksFiltres = tousLesStocks.filter(function(stock) {
                if (typeSelectionne === 'tous') {
                    return true;
                } else {
                    return stock.type === typeSelectionne;
                }
            });

            var listeHtml = '<h2>';
            if (typeSelectionne === 'medicament') {
                listeHtml += 'Médicaments';
            } else if (typeSelectionne === 'materiel') {
                listeHtml += 'Matériel';
            } else {
                listeHtml += 'Tous les Produits';
            }
            listeHtml += '</h2><ul>';

            if (stocksFiltres.length > 0) {
                stocksFiltres.forEach(function(stock) {
                    var classeCss = stock.quantite_disponible < 5 ? 'stock-bas' : '';
                    listeHtml += '<li class="' + classeCss + '">' + stock.nom + ' - Quantité disponible : ' + stock.quantite_disponible + '</li>';
                });
            } else {
                listeHtml += '<p>Aucun stock trouvé.</p>';
            }

            listeHtml += '</ul>';

            document.getElementById('listeProduits').innerHTML = listeHtml;
        });
    </script>
</body>

</html>
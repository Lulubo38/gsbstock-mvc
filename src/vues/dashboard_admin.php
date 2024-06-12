<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin</title>
    <link rel="stylesheet" href="./style/admin.css">
</head>

<body>
    <header>
        <h1>Tableau de Bord Admin</h1>
        <nav>
            <ul>
                <li><a href="index.php?uc=logout">Déconnexion</a></li>
                <li><a href="index.php?uc=order&action=historique-commande">Historique des commandes</a></li>
                <li><a href="index.php?uc=stock&action=gerer_stock">Gérer les stocks</a></li>
                <li><a href="index.php?uc=order&action=commande-mois">Commandes par mois</a></li> <!-- Ajout du lien vers la page de commandes par mois -->
            </ul>
        </nav>
    </header>

    <section>
        <h2>Gestion du Stock</h2>
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
                var tousLesProduits = <?php echo json_encode($stocks); ?>;

                var produitsFiltres = tousLesProduits.filter(function(produit) {
                    if (typeSelectionne === 'tous') {
                        return true;
                    } else {
                        return produit.type === typeSelectionne;
                    }
                });

                var listeHtml = '<ul>';

                if (produitsFiltres.length > 0) {
                    produitsFiltres.forEach(function(produit) {
                        listeHtml += '<li>' + produit.nom + ' - Quantité : ' + produit.quantite_disponible + '</li>';
                    });
                } else {
                    listeHtml += '<li>Aucun produit trouvé.</li>';
                }

                listeHtml += '</ul>';

                document.getElementById('listeProduits').innerHTML = listeHtml;
            });
        </script>
    </section>
</body>

</html>
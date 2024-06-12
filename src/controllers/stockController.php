
<?php
//definition de $uc pour connaitre la page souhaitee
$action = $_GET["action"];

switch ($action) {
    case "gerer_stock":
        // Récupérer les stocks depuis la base de données
        $stocks = $stockDataAccess->getStocks();
        include "../src/vues/stock/gerer_stock.php";
        break;
    case 'ajouter_stock':
        // Récupérer les données du formulaire d'ajout de stock
        $nom_nouveau_stock = $_POST['nom_nouveau_stock'];
        $quantite_nouveau_stock = $_POST['quantite_nouveau_stock'];
        $description_nouveau_stock = $_POST['description_nouveau_stock'];
        $type_nouveau_stock = $_POST['type_nouveau_stock'];
        $stockDataAccess->createStock($nom_nouveau_stock, $quantite_nouveau_stock, $description_nouveau_stock, $type_nouveau_stock);
        // Rediriger l'administrateur vers la même page pour éviter le renvoi du formulaire
        header("Location: index.php?uc=stock&action=gerer_stock");
        break;
    case 'supprimer_stock': //ne fonctionne pas, necessite modif de la base(contrainte empechant la suppression)
        // Récupérer l'ID du stock à supprimer
        $id_stock_a_supprimer = $_POST['id_stock_a_supprimer'];
        // Supprimer le stock de la base de données
        $stockDataAccess->deleteStock($id_stock_a_supprimer);
        // Rediriger l'administrateur vers la même page pour éviter le renvoi du formulaire
        header("Location: index.php?uc=stock&action=gerer_stock");
        break;
    case 'modifier_quantite':
        // Récupérer l'ID du stock et la nouvelle quantité
        $id_stock_a_modifier = $_POST['id_stock_a_modifier'];
        $nouvelle_quantite = $_POST['nouvelle_quantite'];

        // Mettre à jour la quantité du stock dans la base de données
        $stockDataAccess->updateStock($nouvelle_quantite, $id_stock_a_modifier, "+");
        // Rediriger l'administrateur vers la même page pour éviter le renvoi du formulaire
        header("Location: index.php?uc=stock&action=gerer_stock");
        break;
}

<?php
require('../src/core/database.php');

class Stock
{
    private $db;

    /**
     * Constructor for the Stock Class
     */
    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }
    public function getStocks()
    {
        $requete_stocks = $this->db->query('SELECT id_stock, nom, quantite_disponible, description type FROM stocks');
        $stocks = $requete_stocks->fetchAll();
        return $stocks;
    }
    public function updateStatut($id_demande)
    {
        $requete_update_statut = $this->db->prepare('UPDATE demandes_commandes SET statut = "validée" WHERE id_demande = ?');
        $requete_update_statut->execute([$id_demande]);
    }
    public function updateStock($quantite_demandee, $id_stock, $action)
    {
        if ($action == "+") {
            $requete_modifier_quantite = $this->db->prepare('UPDATE stocks SET quantite_disponible = ? WHERE id_stock = ?');
        } elseif ($action == "-") {
            $requete_modifier_quantite = $this->db->prepare('UPDATE stocks SET quantite_disponible = quantite_disponible - ? WHERE id_stock = ?');
        }
        $requete_modifier_quantite->execute([$quantite_demandee, $id_stock]);
    }
    //ne fonctionne pas, necessite modif de la base(contrainte empechant la suppression)
    public function deleteStock($id_stock_a_supprimer)
    {
        $requete_supprimer_stock = $this->db->prepare('DELETE FROM stocks WHERE id_stock = ?');
        $requete_supprimer_stock->execute([$id_stock_a_supprimer]);
    }
    public function createStock($nom_nouveau_stock, $quantite_nouveau_stock, $description_nouveau_stock, $type_nouveau_stock)
    {
        // Insérer le nouveau stock dans la base de données
        $requete_insert_stock = $this->db->prepare('INSERT INTO stocks (nom, quantite_disponible, description, type) VALUES (?, ?, ?, ?)');
        $requete_insert_stock->execute([$nom_nouveau_stock, $quantite_nouveau_stock, $description_nouveau_stock, $type_nouveau_stock]);

        // Enregistrer le mouvement correspondant dans la table des mouvements
        $id_stock = $this->db->lastInsertId(); // Obtenez l'ID du nouveau stock inséré
        $type_mouvement = 'entree'; // Type de mouvement pour une nouvelle entrée de stock
        $quantite_mouvement = $quantite_nouveau_stock; // La quantité ajoutée est égale à la quantité du nouveau stock
        $date_mouvement = date("Y-m-d H:i:s"); // Date et heure actuelles

        $requete_insert_mouvement = $this->db->prepare('INSERT INTO mouvements (id_stock, type_mouvement, quantite, date_mouvement) VALUES (?, ?, ?, ?)');
        $requete_insert_mouvement->execute([$id_stock, $type_mouvement, $quantite_mouvement, $date_mouvement]);
    }
    public function getPopular()
    {
        // Requête pour récupérer les produits les plus commandés
        $requete_produits_populaires = $this->db->query('SELECT id_stock, COUNT(*) as total_commandes FROM demandes_commandes GROUP BY id_stock ORDER BY total_commandes DESC');
        // Récupérer les résultats de la requête
        $produits_populaires = $requete_produits_populaires->fetchAll();
        return $produits_populaires;
    }
}

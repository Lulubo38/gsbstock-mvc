
<?php
require('../src/core/database.php');

class Order
{
    private $db;

    /**
     * Constructor for the Order Class
     */
    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }
    public function getOrders()
    {
        $recupCommandes = $this->db->query('SELECT id_demande, id_stock, quantite_demandee, date_demande, statut FROM demandes_commandes');
        $commandes = $recupCommandes->fetchAll();
        return $commandes;
    }
    public function createOrder($stock_id, $quantite)
    {
        $requete_insert_demande = $this->db->prepare('INSERT INTO demandes_commandes (id_stock, quantite_demandee) VALUES (?, ?)');
        $requete_insert_demande->execute(array($stock_id, $quantite));
    }
    public function getInfoDemande($id_demande)
    {
        $requete_info_demande = $this->db->prepare('SELECT id_stock, quantite_demandee FROM demandes_commandes WHERE id_demande = ?');
        $requete_info_demande->execute([$id_demande]);
        $result = $requete_info_demande->fetch();
        return   $result;
    }
    //ne fonctionne pas (nécessite une modif de la base)
    public function getOrdersByUserId($id_utilisateur)
    {
        $requete_commandes = $this->db->prepare('SELECT * FROM demandes_commandes WHERE id_utilisateur = ?');
        $requete_commandes->execute([$id_utilisateur]);
        $commandes = $requete_commandes->fetchAll();
        return $commandes;
    }
    public function getCommandeMois()
    {
        $requete_total_par_mois = $this->db->query('SELECT MONTH(date_demande) AS mois, SUM(quantite_demandee) AS total FROM demandes_commandes GROUP BY MONTH(date_demande)');
        $resultats = $requete_total_par_mois->fetchAll();
        return $resultats;
    }
    public function refuserDemande($id_demande)
    {
        $requete_supprimer_demande = $this->db->prepare('DELETE FROM demandes_commandes WHERE id_demande = ?');
        $requete_supprimer_demande->execute([$id_demande]);
    }
}

<?php
require('../src/core/database.php');

class User
{
    private $db;

    /**
     * Constructor for the User Class
     */
    public function __construct()
    {
        global $pdo;
        $this->db = $pdo;
    }

    public function login($nom)
    {
        $recupUser = $this->db->prepare('SELECT u.id_utilisateur, u.nom, u.mot_de_passe, r.id_role FROM utilisateurs u INNER JOIN roles r ON u.id_role = r.id_role WHERE nom = ?');
        $recupUser->execute(array($nom));
        $user = $recupUser->fetch();
        return $user;
    }
    public function checkUser($nom)
    {
        $checkUser = $this->db->prepare('SELECT id_utilisateur FROM utilisateurs WHERE nom = ?');
        $checkUser->execute(array($nom));
        $existingUser = $checkUser->fetch();
        return $existingUser;
    }
    public function createUser($nom, $email, $mot_de_passe)
    {
        $insertUser = $this->db->prepare('INSERT INTO utilisateurs (nom, email, mot_de_passe,id_role) VALUES (?, ?, ?,2)');
        $insertUser->execute(array($nom, $email, $mot_de_passe));

        // Sélection de l'utilisateur nouvellement inscrit
        $recupUser = $this->db->prepare('SELECT id_utilisateur FROM utilisateurs WHERE nom = ?');
        $recupUser->execute(array($nom));
        $user = $recupUser->fetch();
        return $user;
    }
}

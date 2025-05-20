<?php
/**
 * Classe Transaction - Gestion des transactions bancaires
 */
class Transaction {
    private $id;
    private $client_id;
    private $type;
    private $montant;
    private $destinataire_id;
    private $date_transaction;
    private $db;
    
    /**
     * Constructeur
     * @param int|null $id ID de la transaction (optionnel)
     */
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        
        if ($id !== null) {
            $this->loadById($id);
        }
    }
    
    /**
     * Charger une transaction par son ID
     * @param int $id ID de la transaction
     * @return bool Succès du chargement
     */
    private function loadById($id) {
        $transaction = $this->db->fetch("SELECT * FROM transactions WHERE id = ?", [$id]);
        if ($transaction) {
            $this->id = $transaction['id'];
            $this->client_id = $transaction['client_id'];
            $this->type = $transaction['type'];
            $this->montant = $transaction['montant'];
            $this->destinataire_id = $transaction['destinataire_id'];
            $this->date_transaction = $transaction['date_transaction'];
            return true;
        }
        return false;
    }
    
    /**
     * Créer une nouvelle transaction
     * @param int $client_id ID du client
     * @param string $type Type de transaction (depot, retrait, transfert)
     * @param float $montant Montant de la transaction
     * @param int|null $destinataire_id ID du destinataire (pour les transferts)
     * @return int ID de la transaction créée
     */
    public function create($client_id, $type, $montant, $destinataire_id = null) {
        $this->db->query(
            "INSERT INTO transactions (client_id, type, montant, destinataire_id) VALUES (?, ?, ?, ?)",
            [$client_id, $type, $montant, $destinataire_id]
        );
        
        $this->id = $this->db->getConnection()->lastInsertId();
        $this->loadById($this->id);
        
        return $this->id;
    }
    
    /**
     * Récupérer les détails complets d'une transaction
     * @return array Détails de la transaction
     */
    public function getDetails() {
        return $this->db->fetch(
            "SELECT t.*, 
             c1.nom AS expediteur_nom, c1.prenom AS expediteur_prenom,
             c2.nom AS destinataire_nom, c2.prenom AS destinataire_prenom
             FROM transactions t
             LEFT JOIN clients c1 ON t.client_id = c1.id
             LEFT JOIN clients c2 ON t.destinataire_id = c2.id
             WHERE t.id = ?",
            [$this->id]
        );
    }
    
    /**
     * Récupérer les transactions par client
     * @param int $client_id ID du client
     * @param int $limit Nombre maximum de transactions à récupérer
     * @return array Liste des transactions
     */
    public static function getByClientId($client_id, $limit = 10) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM transactions WHERE client_id = ? ORDER BY date_transaction DESC LIMIT ?",
            [$client_id, $limit]
        );
    }
    
    /**
     * Récupérer les transactions par type
     * @param string $type Type de transaction
     * @param int $limit Nombre maximum de transactions à récupérer
     * @return array Liste des transactions
     */
    public static function getByType($type, $limit = 10) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM transactions WHERE type = ? ORDER BY date_transaction DESC LIMIT ?",
            [$type, $limit]
        );
    }
    
    /**
     * Récupérer toutes les transactions avec pagination
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param string|null $type Type de transaction (optionnel)
     * @return array Liste des transactions
     */
    public static function getAll($page = 1, $limit = 10, $type = null) {
        $db = Database::getInstance();
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT t.*, 
                c1.nom AS expediteur_nom, c1.prenom AS expediteur_prenom,
                c2.nom AS destinataire_nom, c2.prenom AS destinataire_prenom
                FROM transactions t
                LEFT JOIN clients c1 ON t.client_id = c1.id
                LEFT JOIN clients c2 ON t.destinataire_id = c2.id";
        
        $params = [];
        
        if ($type) {
            $sql .= " WHERE t.type = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY t.date_transaction DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $db->fetchAll($sql, $params);
    }
    
    /**
     * Compter le nombre total de transactions
     * @param string|null $type Type de transaction (optionnel)
     * @return int Nombre de transactions
     */
    public static function countAll($type = null) {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) as total FROM transactions";
        $params = [];
        
        if ($type) {
            $sql .= " WHERE type = ?";
            $params[] = $type;
        }
        
        $result = $db->fetch($sql, $params);
        return $result['total'];
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getClientId() {
        return $this->client_id;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function getMontant() {
        return $this->montant;
    }
    
    public function getDestinataireId() {
        return $this->destinataire_id;
    }
    
    public function getDate() {
        return $this->date_transaction;
    }
}

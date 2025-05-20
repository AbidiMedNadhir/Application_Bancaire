<?php
/**
 * Classe Client - Gestion des clients
 */
class Client {
    private $id;
    private $user_id;
    private $nom;
    private $prenom;
    private $numero_compte;
    private $solde;
    private $db;
    
    /**
     * Constructeur
     * @param int|null $id ID du client (optionnel)
     */
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        
        if ($id !== null) {
            $this->loadById($id);
        }
    }
    
    /**
     * Charger un client par son ID
     * @param int $id ID du client
     * @return bool Succès du chargement
     */
    private function loadById($id) {
        $client = $this->db->fetch("SELECT * FROM clients WHERE id = ?", [$id]);
        if ($client) {
            $this->id = $client['id'];
            $this->user_id = $client['user_id'];
            $this->nom = $client['nom'];
            $this->prenom = $client['prenom'];
            $this->numero_compte = $client['numero_compte'];
            $this->solde = $client['solde'];
            return true;
        }
        return false;
    }
    
    /**
     * Récupérer un client par son user_id
     * @param int $user_id ID de l'utilisateur associé
     * @return Client|null Instance de Client si trouvé, sinon null
     */
    public static function getByUserId($user_id) {
        $db = Database::getInstance();
        $client = $db->fetch("SELECT id FROM clients WHERE user_id = ?", [$user_id]);
        
        if ($client) {
            return new self($client['id']);
        }
        
        return null;
    }
    
    /**
     * Créer un nouveau client
     * @param int $user_id ID de l'utilisateur associé
     * @param string $nom Nom du client
     * @param string $prenom Prénom du client
     * @param string $numero_compte Numéro de compte
     * @param float $solde Solde initial
     * @return int ID du client créé
     */
    public function create($user_id, $nom, $prenom, $numero_compte, $solde = 0) {
        $this->db->query(
            "INSERT INTO clients (user_id, nom, prenom, numero_compte, solde) VALUES (?, ?, ?, ?, ?)",
            [$user_id, $nom, $prenom, $numero_compte, $solde]
        );
        
        $this->id = $this->db->getConnection()->lastInsertId();
        $this->loadById($this->id);
        
        return $this->id;
    }
    
    /**
     * Effectuer un dépôt
     * @param float $amount Montant à déposer
     * @return bool Succès de l'opération
     * @throws Exception En cas d'erreur
     */
    public function deposit($amount) {
        if ($amount <= 0) {
            throw new Exception("Le montant doit être supérieur à zéro");
        }
        
        try {
            $this->db->beginTransaction();
            
            // Mettre à jour le solde
            $this->db->query("UPDATE clients SET solde = solde + ? WHERE id = ?", [$amount, $this->id]);
            
            // Créer une transaction
            $transaction = new Transaction();
            $transaction->create($this->id, 'depot', $amount);
            
            $this->db->commit();
            $this->solde += $amount;
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Effectuer un retrait
     * @param float $amount Montant à retirer
     * @return bool Succès de l'opération
     * @throws Exception En cas d'erreur
     */
    public function withdraw($amount) {
        if ($amount <= 0) {
            throw new Exception("Le montant doit être supérieur à zéro");
        }
        
        if ($amount > $this->solde) {
            throw new Exception("Fonds insuffisants. Solde actuel : " . $this->solde . " €");
        }
        
        try {
            $this->db->beginTransaction();
            
            // Mettre à jour le solde
            $this->db->query("UPDATE clients SET solde = solde - ? WHERE id = ?", [$amount, $this->id]);
            
            // Créer une transaction
            $transaction = new Transaction();
            $transaction->create($this->id, 'retrait', $amount);
            
            $this->db->commit();
            $this->solde -= $amount;
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Effectuer un transfert
     * @param string $recipient_account Numéro de compte du destinataire
     * @param float $amount Montant à transférer
     * @return bool Succès de l'opération
     * @throws Exception En cas d'erreur
     */
    public function transfer($recipient_account, $amount) {
        if ($amount <= 0) {
            throw new Exception("Le montant doit être supérieur à zéro");
        }
        
        if ($amount > $this->solde) {
            throw new Exception("Fonds insuffisants. Solde actuel : " . $this->solde . " €");
        }
        
        // Trouver le destinataire
        $recipient = $this->db->fetch(
            "SELECT id FROM clients WHERE numero_compte = ? AND id != ?",
            [$recipient_account, $this->id]
        );
        
        if (!$recipient) {
            throw new Exception("Le compte destinataire est invalide");
        }
        
        $recipient_id = $recipient['id'];
        
        try {
            $this->db->beginTransaction();
            
            // Débiter l'expéditeur
            $this->db->query("UPDATE clients SET solde = solde - ? WHERE id = ?", [$amount, $this->id]);
            
            // Créditer le destinataire
            $this->db->query("UPDATE clients SET solde = solde + ? WHERE id = ?", [$amount, $recipient_id]);
            
            // Créer une transaction
            $transaction = new Transaction();
            $transaction->create($this->id, 'transfert', $amount, $recipient_id);
            
            $this->db->commit();
            $this->solde -= $amount;
            
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Récupérer l'historique des transactions
     * @param string|null $type Type de transaction (optionnel)
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @return array Liste des transactions
     */
    public function getTransactionHistory($type = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $params = [$this->id, $this->id];
        $type_clause = "";
        
        if ($type) {
            $type_clause = "AND type = ?";
            $params[] = $type;
        }
        
        return $this->db->fetchAll(
            "SELECT t.*, 
             c1.nom AS expediteur_nom, c1.prenom AS expediteur_prenom,
             c2.nom AS destinataire_nom, c2.prenom AS destinataire_prenom
             FROM transactions t
             LEFT JOIN clients c1 ON t.client_id = c1.id
             LEFT JOIN clients c2 ON t.destinataire_id = c2.id
             WHERE (t.client_id = ? OR t.destinataire_id = ?) $type_clause
             ORDER BY t.date_transaction DESC
             LIMIT $limit OFFSET $offset",
            $params
        );
    }
    
    /**
     * Compter le nombre total de transactions
     * @param string|null $type Type de transaction (optionnel)
     * @return int Nombre de transactions
     */
    public function countTransactions($type = null) {
        $params = [$this->id, $this->id];
        $type_clause = "";
        
        if ($type) {
            $type_clause = "AND type = ?";
            $params[] = $type;
        }
        
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total FROM transactions 
             WHERE (client_id = ? OR destinataire_id = ?) $type_clause",
            $params
        );
        
        return $result['total'];
    }
    
    /**
     * Récupérer tous les clients
     * @return array Liste des clients
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM clients ORDER BY nom, prenom");
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->user_id;
    }
    
    public function getNom() {
        return $this->nom;
    }
    
    public function getPrenom() {
        return $this->prenom;
    }
    
    public function getNumeroCompte() {
        return $this->numero_compte;
    }
    
    public function getSolde() {
        return $this->solde;
    }
    
    public function getNomComplet() {
        return $this->prenom . ' ' . $this->nom;
    }
}

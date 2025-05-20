<?php
/**
 * Classe User - Gestion des utilisateurs
 */
class User {
    private $id;
    private $username;
    private $password;
    private $role;
    private $actif;
    private $db;
    
    /**
     * Constructeur
     * @param int|null $id ID de l'utilisateur (optionnel)
     */
    public function __construct($id = null) {
        $this->db = Database::getInstance();
        
        if ($id !== null) {
            $this->loadById($id);
        }
    }
    
    /**
     * Charger un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return bool Succès du chargement
     */
    private function loadById($id) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if ($user) {
            $this->id = $user['id'];
            $this->username = $user['username'];
            $this->password = $user['password'];
            $this->role = $user['role'];
            $this->actif = $user['actif'];
            return true;
        }
        return false;
    }
    
    /**
     * Authentifier un utilisateur
     * @param string $username Nom d'utilisateur
     * @param string $password Mot de passe
     * @return User|false Instance de User si authentification réussie, sinon false
     */
    public static function authenticate($username, $password) {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE username = ?", [$username]);
        
        if (!$user) {
            return false;
        }
        
        if (isset($user['actif']) && $user['actif'] !== null && $user['actif'] != 1) {
            throw new Exception("Votre compte est désactivé. Veuillez contacter l'administrateur.");
        }
        
        // Vérification du mot de passe
        if (password_verify($password, $user['password']) || $user['password'] === $password) {
            return new self($user['id']);
        }
        
        return false;
    }
    
    /**
     * Créer un nouvel utilisateur
     * @param string $username Nom d'utilisateur
     * @param string $password Mot de passe
     * @param string $role Rôle (admin ou client)
     * @return int ID de l'utilisateur créé
     */
    public function create($username, $password, $role = 'client') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $this->db->query(
            "INSERT INTO users (username, password, role, actif) VALUES (?, ?, ?, 1)",
            [$username, $hashedPassword, $role]
        );
        
        $this->id = $this->db->getConnection()->lastInsertId();
        $this->loadById($this->id);
        
        return $this->id;
    }
    
    /**
     * Mettre à jour un utilisateur
     * @param array $data Données à mettre à jour
     * @return bool Succès de la mise à jour
     */
    public function update($data) {
        $updates = [];
        $params = [];
        
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $key !== 'id') {
                $updates[] = "$key = ?";
                $params[] = $value;
                $this->$key = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $this->id;
        $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        
        return $this->db->query($sql, $params)->rowCount() > 0;
    }
    
    /**
     * Supprimer un utilisateur
     * @return bool Succès de la suppression
     */
    public function delete() {
        return $this->db->query("DELETE FROM users WHERE id = ?", [$this->id])->rowCount() > 0;
    }
    
    /**
     * Récupérer tous les utilisateurs
     * @return array Liste des utilisateurs
     */
    public static function getAll() {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM users ORDER BY username");
    }
    
    /**
     * Récupérer un utilisateur par son nom d'utilisateur
     * @param string $username Nom d'utilisateur
     * @return User|false Instance de User si trouvé, sinon false
     */
    public static function getByUsername($username) {
        $db = Database::getInstance();
        $user = $db->fetch("SELECT id FROM users WHERE username = ?", [$username]);
        
        if ($user) {
            return new self($user['id']);
        }
        
        return false;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function getRole() {
        return $this->role;
    }
    
    public function isActive() {
        return $this->actif == 1;
    }
    
    public function setActive($active) {
        $this->actif = $active ? 1 : 0;
        return $this->update(['actif' => $this->actif]);
    }
}

<?php
/**
 * Classe Database - Gestion de la connexion à la base de données
 * Pattern Singleton pour une seule instance de connexion
 */
class Database {
    private static $instance = null;
    private $connexion;
    
    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $base = "banque_db";
        
        try {
            $this->connexion = new PDO("mysql:host=$servername;dbname=$base", $username, $password);
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Méthode pour obtenir l'instance unique de la classe
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la connexion PDO
     * @return PDO
     */
    public function getConnection() {
        return $this->connexion;
    }
    
    /**
     * Exécuter une requête préparée
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        $stmt = $this->connexion->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Récupérer un seul enregistrement
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return array|false
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer tous les enregistrements
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Démarrer une transaction
     */
    public function beginTransaction() {
        return $this->connexion->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit() {
        return $this->connexion->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback() {
        return $this->connexion->rollBack();
    }
}

<?php
/**
 * Classe Auth - Gestion de l'authentification et des sessions
 */
class Auth {
    /**
     * Connecter un utilisateur
     * @param string $username Nom d'utilisateur
     * @param string $password Mot de passe
     * @return bool Succès de la connexion
     */
    public static function login($username, $password) {
        try {
            $user = User::authenticate($username, $password);
            
            if ($user) {
                // Démarrer la session si elle n'est pas déjà active
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                
                // Stocker les informations de l'utilisateur en session
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['username'] = $user->getUsername();
                $_SESSION['role'] = $user->getRole();
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Déconnecter un utilisateur
     * @return bool Succès de la déconnexion
     */
    public static function logout() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Détruire toutes les variables de session
        $_SESSION = [];
        
        // Détruire la session
        session_destroy();
        
        return true;
    }
    
    /**
     * Vérifier si un utilisateur est connecté
     * @return bool Utilisateur connecté ou non
     */
    public static function isLoggedIn() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * @param string $role Rôle à vérifier
     * @return bool Utilisateur a le rôle ou non
     */
    public static function hasRole($role) {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Rediriger si l'utilisateur n'est pas connecté
     * @param string $redirect URL de redirection
     */
    public static function requireLogin($redirect = 'signin.php') {
        if (!self::isLoggedIn()) {
            header("Location: $redirect");
            exit;
        }
    }
    
    /**
     * Rediriger si l'utilisateur n'a pas le rôle requis
     * @param string $role Rôle requis
     * @param string $redirect URL de redirection
     */
    public static function requireRole($role, $redirect = 'signin.php') {
        self::requireLogin($redirect);
        
        if (!self::hasRole($role)) {
            header("Location: $redirect");
            exit;
        }
    }
    
    /**
     * Obtenir l'utilisateur actuellement connecté
     * @return User|null Instance de User si connecté, sinon null
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return new User($_SESSION['user_id']);
    }
    
    /**
     * Obtenir le client associé à l'utilisateur connecté
     * @return Client|null Instance de Client si connecté et client trouvé, sinon null
     */
    public static function getCurrentClient() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return Client::getByUserId($_SESSION['user_id']);
    }
    
    /**
     * Définir un message d'erreur en session
     * @param string $message Message d'erreur
     */
    public static function setError($message) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['error'] = $message;
    }
    
    /**
     * Définir un message de succès en session
     * @param string $message Message de succès
     */
    public static function setSuccess($message) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['success'] = $message;
    }
    
    /**
     * Récupérer et effacer le message d'erreur en session
     * @return string|null Message d'erreur ou null
     */
    public static function getError() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $error = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        return $error;
    }
    
    /**
     * Récupérer et effacer le message de succès en session
     * @return string|null Message de succès ou null
     */
    public static function getSuccess() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['success']);
        return $success;
    }
}

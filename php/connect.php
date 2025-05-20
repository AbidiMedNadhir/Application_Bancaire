<?php
// Inclusion de l'autoloader pour charger les classes
require_once __DIR__ . '/../classes/autoload.php';

// Création d'une variable $connexion pour maintenir la compatibilité avec le code existant
try {
    $db = Database::getInstance();
    $connexion = $db->getConnection();
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

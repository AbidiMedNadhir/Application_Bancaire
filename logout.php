<?php
require_once __DIR__ . '/php/connect.php';

// Utilisation de la classe Auth pour la déconnexion
Auth::logout();

// Redirection vers la page de connexion
header("Location: signin.php");
exit;
?>

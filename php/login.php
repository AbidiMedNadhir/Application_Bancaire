<?php
session_start();
require_once 'connect.php'; // Ce fichier inclut maintenant l'autoloader et les classes

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"]; // Ne pas trim le password

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: signin.php");
        exit;
    }

    try {
        // Utilisation de la classe Auth pour l'authentification
        if (Auth::login($username, $password)) {
            // Redirection selon le rôle
            if (Auth::hasRole('admin')) {
                header("Location: /banque_app/php/admin/dashboard.php");
            } elseif (Auth::hasRole('client')) {
                header("Location: /banque_app/php/client/dashboard_user.php");
            } else {
                $_SESSION['error'] = "Unknown user role.";
                header("Location: signin.php");
            }
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: signin.php");
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: signin.php");
        exit;
    }
} else {
    // Accès direct interdit
    header("Location: signin.php");
    exit;
}
?>

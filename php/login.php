<?php
session_start();
require_once 'connect.php'; // Fichier de connexion PDO

if ($_SERVER["REQUEST_METHOD"] == "POST") {   //VVérifie que les données proviennent bien d’un formulaire POST.
    // 	Récupèrent les valeurs envoyées par le formulaire.
    $username = trim($_POST["username"]);
    $password = $_POST["password"]; // Ne pas trim le password

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: signin.php");
        exit;
    }

    try {
        // Vérifie si l'utilisateur existe
        $stmt = $connexion->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stored_password = $user['password']; //  C’est le mot de passe enregistré en base de données

            // Vérifie avec password_verify (si hashé), sinon comparaison directe
            if (password_verify($password, $stored_password) || $stored_password === $password){
                // Authentification réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    header("Location: /banque_app/php/admin/dashboard.php");
                } elseif ($user['role'] === 'client') {
                    header("Location: /banque_app/php/client/dashboard_user.php");
                } else {
                    $_SESSION['error'] = "Unknown user role.";
                    header("Location: signin.php");
                }
                exit;
            } else {
                $_SESSION['error'] = "Incorrect password.";
                header("Location: signin.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "User not found.";
            header("Location: signin.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: signin.php");
        exit;
    }
} else {
    // Accès direct interdit
    header("Location: signin.php");
    exit;
}
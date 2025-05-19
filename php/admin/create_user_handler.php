<?php
session_start();
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Création de l'utilisateur
        $stmtUser = $connexion->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmtUser->execute([
            $_POST['username'],
            password_hash($_POST['password'], PASSWORD_DEFAULT),//PASSWORD_DEFAULT utilise l'algorithme le plus récent et sécurisé (actuellement bcrypt).
            $_POST['role']
        ]);
        $user_id = $connexion->lastInsertId();//Recupere l’identifiant de user qui vient d’être inséré dans la table users.

        // 2. Création du client si le rôle est "client"
        if ($_POST['role'] === 'client') {
            $stmtClient = $connexion->prepare("INSERT INTO clients 
                (user_id, nom, prenom, email, telephone, date_naissance, adresse, numero_compte, solde) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtClient->execute([
                $user_id,
                $_POST['nom'],
                $_POST['prenom'],
                $_POST['email'],
                $_POST['telephone'],
                $_POST['date_naissance'],
                $_POST['adresse'],
                $_POST['numero_compte'],
                $_POST['solde']
            ]);
        }

        $_SESSION['success'] = "L'utilisateur a été créé avec succès !";
        header('Location: dashboard.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la création : " . $e->getMessage();
        header('Location: dashboard.php');
        exit;
    }
} else {
    header('Location: dashboard.php');
    exit;
}

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /banque_app/php/signin.php");
    exit;
}
require_once '../connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sender_id = intval($_POST['sender_id']);       // client_id
    $receiver_id = intval($_POST['receiver_id']);   // destinataire_id
    $amount = floatval($_POST['amount']);

    if ($sender_id === $receiver_id) {
        $_SESSION['error'] = "L'expéditeur et le destinataire doivent être différents.";
        header("Location: make_transaction.php");
        exit;
    }

    if ($amount <= 0) {
        $_SESSION['error'] = "Le montant doit être supérieur à zéro.";
        header("Location: make_transaction.php");
        exit;
    }

    try {
        // Vérifier le solde de l'expéditeur
        $stmt = $connexion->prepare("SELECT solde FROM clients WHERE id = ?");
        $stmt->execute([$sender_id]);
        $sender = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sender || $sender['solde'] < $amount) {
            $_SESSION['error'] = "Fonds insuffisants.";
            header("Location: make_transaction.php");
            exit;
        }

        // Démarrer la transaction SQL
        $connexion->beginTransaction();

        // Débiter l'expéditeur
        $stmt = $connexion->prepare("UPDATE clients SET solde = solde - ? WHERE id = ?");
        $stmt->execute([$amount, $sender_id]);

        // Créditer le destinataire
        $stmt = $connexion->prepare("UPDATE clients SET solde = solde + ? WHERE id = ?");
        $stmt->execute([$amount, $receiver_id]);

        // Insérer dans la table `transactions`
        $stmt = $connexion->prepare("INSERT INTO transactions (client_id, type, montant, destinataire_id) VALUES (?, 'transfert', ?, ?)");
        $stmt->execute([$sender_id, $amount, $receiver_id]);

        $connexion->commit();
        $_SESSION['success'] = "Transaction effectuée avec succès.";
    } catch (Exception $e) {
        $connexion->rollBack();
        $_SESSION['error'] = "Erreur lors de la transaction : " . $e->getMessage();
    }

    header("Location: make_transaction.php");
    exit;
}

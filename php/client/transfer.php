<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../signin.php");
    exit;
}

require_once '../connect.php';

$success = "";
$error = "";

$stmt = $connexion->prepare("SELECT id, solde, numero_compte FROM clients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $error = "Client introuvable.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_account = trim($_POST['recipient_account']);
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Le montant doit être supérieur à zéro.";
    } elseif ($amount > $client['solde']) {
        $error = "Solde insuffisant.";
    } else {
        $stmt = $connexion->prepare("SELECT id FROM clients WHERE numero_compte = ? AND id != ?");
        $stmt->execute([$recipient_account, $client['id']]);
        $recipient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$recipient) {
            $error = "Le compte destinataire est invalide.";
        } else {
            try {
                $connexion->beginTransaction();

                // Débiter l'expéditeur
                $updateSender = $connexion->prepare("UPDATE clients SET solde = solde - ? WHERE id = ?");
                $updateSender->execute([$amount, $client['id']]);

                // Créditer le destinataire
                $updateRecipient = $connexion->prepare("UPDATE clients SET solde = solde + ? WHERE id = ?");
                $updateRecipient->execute([$amount, $recipient['id']]);

                // Enregistrer la transaction
                $insert = $connexion->prepare("INSERT INTO transactions (client_id, type, montant, destinataire_id) VALUES (?, 'transfert', ?, ?)");
                $insert->execute([$client['id'], $amount, $recipient['id']]);

                $connexion->commit();
                $success = "Transfert effectué avec succès.";
            } catch (PDOException $e) {
                $connexion->rollBack();
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Transfert</title>
    <link rel="stylesheet" href="/banque_app/css/transfer.css?v=<?= time(); ?>">
</head>
<body>

<body>
    <div class="container">
        <h1>Faire un Transfert</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="recipient_account">Compte destinataire :</label>
            <input type="text" name="recipient_account" id="recipient_account" required>

            <label for="amount">Montant (€) :</label>
            <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>

            <button type="submit">Transférer</button>
        </form>

        <a class="back-btn" href="dashboard_user.php">← Retour au tableau de bord</a>
    </div>
</body>
</html>

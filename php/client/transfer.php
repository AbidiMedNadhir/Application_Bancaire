<?php
session_start();
require_once '../connect.php'; // Ce fichier inclut maintenant l'autoloader et les classes

// Vérification de l'authentification et du rôle
Auth::requireRole('client', '../signin.php');

$success = "";
$error = "";

try {
    // Récupération du client connecté
    $client = Auth::getCurrentClient();
    
    if (!$client) {
        $error = "Client introuvable.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $recipient_account = trim($_POST['recipient_account']);
        $amount = floatval($_POST['amount']);
        
        try {
            // Utilisation de la méthode transfer de la classe Client
            $client->transfer($recipient_account, $amount);
            $success = "Transfert effectué avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
} catch (Exception $e) {
    $error = "Erreur : " . $e->getMessage();
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
    <script src="/banque_app/js/app.js"></script>

</body>
</html>

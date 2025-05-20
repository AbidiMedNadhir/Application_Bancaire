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
        $error = "Client not found.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = floatval($_POST['amount']);
        
        try {
            // Utilisation de la méthode deposit de la classe Client
            $client->deposit($amount);
            $success = "Dépôt effectué avec succès.";
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
    <title>Dépôt</title>
    <link rel="stylesheet" href="/banque_app/css/deposit.css?v=<?= time(); ?>">
</head>
<body>
    <h1>Faire un dépôt</h1>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" id="deposit-form">
        <label for="amount">Montant (€) :</label>
        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
        <button type="submit">Déposer</button>
    </form>

    <p><a href="dashboard_user.php">← Retour au tableau de bord</a></p>
    <script src="/banque_app/js/app.js"></script>


</body>
</html>

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
        $error = "Erreur : client introuvable.";
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $amount = floatval($_POST['amount']);
        
        try {
            // Utilisation de la méthode withdraw de la classe Client
            $client->withdraw($amount);
            $success = "Retrait de " . number_format($amount, 2, ',', ' ') . " € effectué avec succès.";
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
    <title>Faire un retrait</title>
    <link rel="stylesheet" href="/banque_app/css/withdraw.css?v=<?= time(); ?>">
</head>
<body>
    <div class="container">
        <h1>Faire un retrait</h1>

        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" id="withdraw-form">
            <label for="amount">Montant à retirer (€) :</label>
            <input type="number" name="amount" id="amount" min="0.01" step="0.01" required>
            <button type="submit">Valider le retrait</button>
        </form>

        <a class="back-link" href="dashboard_user.php">← Retour au tableau de bord</a>
    </div>
    <script src="/banque_app/js/app.js"></script>

</body>
</html>

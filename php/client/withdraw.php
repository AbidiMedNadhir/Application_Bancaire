<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../signin.php");
    exit;
}

require_once '../connect.php';

$success = "";
$error = "";

// Récupération du client lié à l'utilisateur connecté
$stmt = $connexion->prepare("SELECT id, solde FROM clients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $error = "Erreur : client introuvable.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Le montant doit être supérieur à zéro.";
    } elseif ($amount > $client['solde']) {
        $error = "Fonds insuffisants. Solde actuel : " . $client['solde'] . " €";
    } else {
        try {
            $connexion->beginTransaction();

            // Déduire le montant du solde
            $update = $connexion->prepare("UPDATE clients SET solde = solde - ? WHERE id = ?");
            $update->execute([$amount, $client['id']]);

            // Enregistrer la transaction de type 'retrait'
            $insert = $connexion->prepare("INSERT INTO transactions (client_id, type, montant) VALUES (?, 'retrait', ?)");
            $insert->execute([$client['id'], $amount]);

            $connexion->commit();
            $success = "Retrait de " . number_format($amount, 2, ',', ' ') . " € effectué avec succès.";
        } catch (PDOException $e) {
            $connexion->rollBack();
            $error = "Une erreur est survenue : " . $e->getMessage();
        }
    }
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

        <form method="POST">
            <label for="amount">Montant à retirer (€) :</label>
            <input type="number" name="amount" id="amount" min="0.01" step="0.01" required>
            <button type="submit">Valider le retrait</button>
        </form>

        <a class="back-link" href="dashboard_user.php">← Retour au tableau de bord</a>
    </div>
</body>
</html>

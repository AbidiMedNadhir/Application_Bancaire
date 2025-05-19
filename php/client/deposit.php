<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../signin.php");
    exit;
}

require_once '../connect.php';

$success = "";
$error = "";

// Récupérer l'id du client lié à user_id
$stmt = $connexion->prepare("SELECT id, solde FROM clients WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $error = "Client not found.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $error = "Le montant doit être supérieur à zéro.";
    } else {
        try {
            $connexion->beginTransaction();

            // Mettre à jour le solde
            $update = $connexion->prepare("UPDATE clients SET solde = solde + ? WHERE id = ?");
            $update->execute([$amount, $client['id']]);

            // Ajouter une transaction de type 'depot'
            $insert = $connexion->prepare("INSERT INTO transactions (client_id, type, montant) VALUES (?, 'depot', ?)");
            $insert->execute([$client['id'], $amount]);

            $connexion->commit();
            $success = "Dépôt effectué avec succès.";
        } catch (PDOException $e) {
            $connexion->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dépôt</title>
    <link rel="stylesheet" href="/banque_app/css/deposit.css?v=<?= time(); ?>">
        <!-- <link rel="stylesheet" href="/banque_app/css/dashboard.css?v=<?= time(); ?>"> -->

    
</head>
<body>
    
<!-- <nav>
    <div class="nav-container">
        <a href="dashboard_user.php" class="logo">BankApp Client</a>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav> -->

    <h1>Faire un dépôt</h1>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="amount">Montant (€) :</label>
        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
        <button type="submit">Déposer</button>
    </form>

    <p><a href="dashboard_user.php">← Retour au tableau de bord</a></p>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: ../signin.php");
    exit;
}

require_once '../connect.php';

// Fetch client information
$clientInfo = [];
try {
    $stmt = $connexion->prepare("SELECT nom, prenom, email, numero_compte, solde FROM clients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $clientInfo = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clientInfo = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="/banque_app/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav>
    <div class="nav-container">
        <a href="dashboard_user.php" class="logo">BankApp Client</a>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<main class="container">
    <h1>Client Area</h1>

    <?php if ($clientInfo): ?>
        <div class="client-card">
            <p><strong>Name:</strong> <?= htmlspecialchars($clientInfo['nom']) ?> <?= htmlspecialchars($clientInfo['prenom']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($clientInfo['email']) ?></p>
            <p><strong>Account Number:</strong> <?= htmlspecialchars($clientInfo['numero_compte']) ?></p>
            <p><strong>Balance:</strong> <?= htmlspecialchars(number_format($clientInfo['solde'], 2)) ?> €</p>
        </div>
    <?php else: ?>
        <p class="alert-error">Unable to load client information.</p>
    <?php endif; ?>

    <div class="card-container">
        <a href="deposit.php" class="card">
            <i class="fas fa-piggy-bank card-icon"></i>
            <span>Make a Deposit</span>
        </a>
        <a href="withdraw.php" class="card">
            <i class="fas fa-money-bill-wave card-icon"></i>
            <span>Make a Withdrawal</span>
        </a>
        <a href="transfer.php" class="card">
            <i class="fas fa-exchange-alt card-icon"></i>
            <span>Make a Transfer</span>
        </a>
        <a href="transaction_history.php" class="card">
            <i class="fas fa-receipt card-icon"></i>
            <span>Transaction History</span>
        </a>
        <!-- <a href="change_password.php" class="card">
            <i class="fas fa-lock card-icon"></i>
            <span>Change Password</span>
        </a> -->
    </div>
</main>

</body>
</html>

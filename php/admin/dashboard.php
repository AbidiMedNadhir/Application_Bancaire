<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../signin.php");
    exit;
}

require_once '../connect.php';

try {
    $stmt = $connexion->query("SELECT COUNT(*) AS total FROM clients");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalClients = $row['total'];  // $row['total'] contient le nbre total de clients.
} catch (PDOException $e) {
    $totalClients = "Erreur de connexion à la base.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="/banque_app/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<nav>
    <div class="nav-container">
        <a href="dashboard.php" class="logo">BankApp Admin</a>
        <div class="user-info">
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../../logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<main class="container">
    <h1>Admin Dashboard</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card-container">
        <a href="create_user.php" class="card">
            <i class="fas fa-user-plus card-icon"></i>
            <span>Create a user</span>
        </a>
        <a href="delete_user.php" class="card">
            <i class="fas fa-user-minus card-icon"></i>
            <span>Delete user</span>
        </a>
        <a href="make_transaction.php" class="card">
            <i class="fas fa-exchange-alt card-icon"></i>
            <span>Make a transaction</span>
        </a>
        <a href="transaction_history.php" class="card">
            <i class="fas fa-history card-icon"></i>
            <span>Transaction history</span>
        </a>
        <a href="manage_accounts.php" class="card">
            <i class="fas fa-university card-icon"></i>
            <span>Manage Accounts</span>
        </a>
        <a href="user_list.php" class="card">
            <i class="fas fa-users card-icon"></i>
            <span>User List</span>
        </a>
    </div>
    <span class="total-client-text">Total clients: <?= htmlspecialchars($totalClients); ?></span>
</main>

<script src="/banque_app/js/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>

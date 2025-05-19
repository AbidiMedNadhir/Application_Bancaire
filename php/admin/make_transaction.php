<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /banque_app/php/signin.php");
    exit;
}
require_once '../connect.php';

// Récupérer tous les clients pour les listes déroulantes
$stmt = $connexion->query("SELECT id, nom, prenom, solde FROM clients");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Faire une transaction</title>
<!-- ou bien si tu mets un fichier séparé -->
<link rel="stylesheet" href="/banque_app/css/transaction.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav>
        <div class="nav-container">
            <a href="dashboard.php" class="logo">BankApp Admin</a>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../../logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <h1>Faire une transaction</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form action="process_transaction.php" method="POST" class="form-card">
            <label>Expéditeur :</label>
            <select name="sender_id" required>
                <option value="">-- Choisir un client --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['id']; ?>">
                     <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom'] . ' (Solde: ' . $client['solde'] . '€)'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Destinataire :</label>
            <select name="receiver_id" required>
                <option value="">-- Choisir un client --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['id']; ?>">
                        <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Montant (€) :</label>
            <input type="number" name="amount" min="1" step="0.01" required>

            <button type="submit" class="btn-create">Envoyer</button>
        </form>

        <a href="dashboard.php" class="back-btn">← Retour au tableau de bord</a>
    </main>
</body>
</html>

<?php
session_start();
require_once '../connect.php';

// Vérification de session admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../signin.php");
    exit();
}

// Gérer les actions : activer / désactiver / modifier / reset mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $user_id = $_POST['user_id'];
        $new_status = $_POST['new_status'] == '1' ? 1 : 0;
        $stmt = $connexion->prepare("UPDATE users SET actif = ? WHERE id = ?");
        $stmt->execute([$new_status, $user_id]);
    } elseif (isset($_POST['update_client'])) {
        $client_id = $_POST['client_id'];
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $solde = $_POST['solde'];
        $stmt = $connexion->prepare("UPDATE clients SET nom = ?, prenom = ?, email = ?, solde = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $solde, $client_id]);
    } 
}

// Récupération des comptes clients
$query = $connexion->query("SELECT clients.*, users.id AS user_id, users.actif FROM clients JOIN users ON clients.user_id = users.id");
$clients = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les Comptes Clients</title>
    <link rel="stylesheet" href="/banque_app/css/manage_accounts.css">
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
    <h1>Gestion des comptes clients</h1>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Solde (€)</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <form method="POST">
                        <td><input type="text" name="nom" value="<?= htmlspecialchars($client['nom']) ?>"></td>
                        <td><input type="text" name="prenom" value="<?= htmlspecialchars($client['prenom']) ?>"></td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>"></td>
                        <td><input type="number" step="0.01" name="solde" value="<?= htmlspecialchars($client['solde']) ?>"></td>
                        <td><?= $client['actif'] ? 'Actif' : 'Inactif' ?></td>
                        <td>
                            <input type="hidden" name="client_id" value="<?= $client['id'] ?>">
                            <input type="hidden" name="user_id" value="<?= $client['user_id'] ?>">

                            <!-- Modifier -->
                            <button type="submit" name="update_client">💾</button>

                            <!-- Activer / Désactiver -->
                            <button type="submit" name="toggle_status" value="1"
                                    onclick="this.form.new_status.value=<?= $client['actif'] ? 0 : 1 ?>;">
                                <?= $client['actif'] ? '🚫' : '✅' ?>
                            </button>
                            <input type="hidden" name="new_status">

                        </td>
                    </form>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="dashboard.php" class="back-btn">← Retour au tableau de bord</a>
</body>
</html>

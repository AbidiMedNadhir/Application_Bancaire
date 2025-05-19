<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /banque_app/php/signin.php");
    exit;
}

require_once '../connect.php';

// Suppression utilisateur si formulaire soumis
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $client_id = intval($_POST['delete_id']);//pour éviter les erreurs ou les injections SQL,


    try {
        // Récupérer le user_id lié au client (il y a une relation client_id → user_id)
        $stmtUser = $connexion->prepare("SELECT user_id FROM clients WHERE id = ?"); //chercher le champ user_id à partir de l'ID du client 
        $stmtUser->execute([$client_id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['user_id'])) {
            // Supprimer d'abord de la table clients
            $stmt1 = $connexion->prepare("DELETE FROM clients WHERE id = ?");
            $stmt1->execute([$client_id]);
        
            // Ensuite supprimer de la table users
            $stmt2 = $connexion->prepare("DELETE FROM users WHERE id = ?");
            $stmt2->execute([$user['user_id']]);
        
            $_SESSION['success'] = "Utilisateur supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Lien avec l'utilisateur introuvable (user_id manquant).";
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }

    header("Location: delete_user.php");
    exit;
}

// Récupération des clients
try {
    $stmt = $connexion->query("SELECT id, nom, prenom, email, solde FROM clients");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clients = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Supprimer un utilisateur</title>
    <link rel="stylesheet" href="/banque_app/css/dashboard.css?v=<?php echo time(); ?>">
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
        <h1>Supprimer un utilisateur</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prenom</th>
                    <th>Email</th>
                    <th>Solde (€)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['id']); ?></td>
                        <td><?php echo htmlspecialchars($client['nom']); ?></td>
                        <td><?php echo htmlspecialchars($client['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['solde']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                <input type="hidden" name="delete_id" value="<?php echo $client['id']; ?>">
                                <button type="submit" class="btn-delete">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                    <tr><td colspan="5">Aucun utilisateur trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="back-btn">← Retour au tableau de bord</a>
    </main>
    <script src="/banque_app/js/dashboard.js?v=<?php echo time(); ?>"></script>

</body>
</html>

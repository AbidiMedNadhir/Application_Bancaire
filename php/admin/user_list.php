<?php
session_start();
require_once '../connect.php';

// Vérification de session admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../signin.php");
    exit();
}

// Préparer les filtres
$whereClauses = [];
$params = [];

if (isset($_GET['role']) && !empty($_GET['role'])) {
    $whereClauses[] = "users.role = :role";
    $params['role'] = $_GET['role'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $whereClauses[] = "users.actif = :status";
    $params['status'] = $_GET['status'];
}

// Récupérer les utilisateurs avec ou sans filtre
$sql = "SELECT users.*, clients.nom, clients.prenom, clients.email, clients.created_at, users.last_login 
        FROM users 
        LEFT JOIN clients ON users.id = clients.user_id";

if (count($whereClauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

try {
    $query = $connexion->prepare($sql);
    $query->execute($params);
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Utilisateurs</title>
    <link rel="stylesheet" href="../../css/manage_accounts.css?v=<?php echo time(); ?>">
</head>
<body>
    <h1>Liste des Utilisateurs</h1>
    
    <!-- Filtrage des utilisateurs -->
    <div class="filter-container">
        <form method="GET" action="user_list.php">
            <label for="role">Filtrer par rôle :</label>
            <select name="role" id="role">
                <option value="">Tous</option>
                <option value="admin" <?= isset($_GET['role']) && $_GET['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="client" <?= isset($_GET['role']) && $_GET['role'] == 'client' ? 'selected' : '' ?>>Client</option>
            </select>

            <label for="status">Filtrer par statut :</label>
            <select name="status" id="status">
                <option value="">Tous</option>
                <option value="1" <?= isset($_GET['status']) && $_GET['status'] == '1' ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= isset($_GET['status']) && $_GET['status'] == '0' ? 'selected' : '' ?>>Inactif</option>
            </select>

            <button type="submit">Filtrer</button>
        </form>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Date d'Inscription</th>
                    <th>Dernière Connexion</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['prenom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><?= $user['actif'] == 1 ? 'Actif' : 'Inactif' ?></td>
                            <td><?= htmlspecialchars($user['created_at']) ?></td>
                            <td><?= htmlspecialchars($user['last_login']) ?></td>
                            <td>
                                <!-- Actions -->
                                <form method="POST" action="delete_user.php" style="display:inline;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="delete-btn">Supprimer</button>
                                </form>
                                <a href="view_user_details.php?user_id=<?= $user['id'] ?>" class="details-btn">Détails</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Aucun utilisateur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a href="dashboard.php" class="back-btn">← Retour au tableau de bord</a>
</body>
</html>

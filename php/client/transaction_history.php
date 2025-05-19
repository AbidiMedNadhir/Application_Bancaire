<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../../signin.php');
    exit();
}

require_once('../connect.php');

// Récupération de l'ID du client connecté
$user_id = $_SESSION['user_id'];

// Trouver l'id de la table `clients` associé à ce user_id
$stmt = $connexion->prepare("SELECT id FROM clients WHERE user_id = ?");
$stmt->execute([$user_id]);
$client = $stmt->fetch();
$client_id = $client['id'] ?? null;

if (!$client_id) {
    echo "Client introuvable.";
    exit();
}

// Pagination
$limit = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filtrage par type
$type = $_GET['type'] ?? '';
$allowed_types = ['depot', 'retrait', 'transfert'];
$type_clause = "";
$params = [$client_id];

if ($type && in_array($type, $allowed_types)) {
    $type_clause = "AND type = ?";
    $params[] = $type;
}

// Nombre total de transactions
$sql_count = "SELECT COUNT(*) FROM transactions 
              WHERE (client_id = ? OR destinataire_id = ?) $type_clause";
$params_count = [$client_id, $client_id];
if ($type_clause) {
    $params_count[] = $type;
}
$stmt = $connexion->prepare($sql_count);
$stmt->execute($params_count);
$total_transactions = $stmt->fetchColumn();
$total_pages = ceil($total_transactions / $limit);

// Récupération des transactions
$sql = "SELECT t.*, 
       c1.nom AS expediteur_nom, c1.prenom AS expediteur_prenom,
       c2.nom AS destinataire_nom, c2.prenom AS destinataire_prenom
FROM transactions t
LEFT JOIN clients c1 ON t.client_id = c1.id
LEFT JOIN clients c2 ON t.destinataire_id = c2.id
WHERE (t.client_id = ? OR t.destinataire_id = ?) $type_clause
ORDER BY t.date_transaction DESC
LIMIT $limit OFFSET $offset";

$params_query = [$client_id, $client_id];
if ($type_clause) {
    $params_query[] = $type;
}

$stmt = $connexion->prepare($sql);
$stmt->execute($params_query);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des transactions</title>
    <link rel="stylesheet" href="../../css/client_transaction_history.css">
</head>
<body>
    <div class="container">
        <h1>Historique de vos transactions</h1>

        <form method="get" class="filter-form">
            <label for="type">Filtrer par type :</label>
            <select name="type" id="type" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="depot" <?= $type === 'depot' ? 'selected' : '' ?>>Dépôt</option>
                <option value="retrait" <?= $type === 'retrait' ? 'selected' : '' ?>>Retrait</option>
                <option value="transfert" <?= $type === 'transfert' ? 'selected' : '' ?>>Transfert</option>
            </select>
        </form>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Expéditeur</th>
                    <th>Destinataire</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $index => $t): ?>
                        <tr>
                            <td><?= $index + 1 + $offset ?></td>
                            <td><?= ucfirst($t['type']) ?></td>
                            <td><?= number_format($t['montant'], 2) ?> DT</td>
                            <td><?= $t['expediteur_nom'] ? htmlspecialchars($t['expediteur_nom'] . ' ' . $t['expediteur_prenom']) : '-' ?></td>
                            <td><?= $t['destinataire_nom'] ? htmlspecialchars($t['destinataire_nom'] . ' ' . $t['destinataire_prenom']) : '-' ?></td>
                            <td><?= $t['date_transaction'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">Aucune transaction trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&type=<?= $type ?>">Précédente</a>
            <?php endif; ?>
            <span>Page <?= $page ?> / <?= $total_pages ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>&type=<?= $type ?>">Suivante</a>
            <?php endif; ?>
        </div>
    </div>
       <p><a href="dashboard_user.php">← Retour au tableau de bord</a></p>

</body>
</html>

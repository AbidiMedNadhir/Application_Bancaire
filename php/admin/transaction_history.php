<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /banque_app/php/signin.php");
    exit;
}

require_once '../connect.php';

// Préparer les filtres
$type = $_GET['type'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';

// Pagination
$transactions_par_page = 6;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $transactions_par_page;

// Calculer le nombre total de résultats pour pagination
$countQuery = "SELECT COUNT(*) FROM transactions t
               JOIN clients c1 ON t.client_id = c1.id
               LEFT JOIN clients c2 ON t.destinataire_id = c2.id
               WHERE 1";

if (!empty($type)) $countQuery .= " AND t.type = '$type'";
if (!empty($date_debut)) $countQuery .= " AND t.date_transaction >= '$date_debut'";
if (!empty($date_fin)) $countQuery .= " AND t.date_transaction <= '$date_fin'";

$total_transactions = $connexion->query($countQuery)->fetchColumn();
$total_pages = ceil($total_transactions / $transactions_par_page);
///////////

// Construction dynamique de la requête
$query = "SELECT t.*, 
            c1.nom AS nom_client, c1.prenom AS prenom_client, 
            c2.nom AS nom_destinataire, c2.prenom AS prenom_destinataire
          FROM transactions t
          JOIN clients c1 ON t.client_id = c1.id
          LEFT JOIN clients c2 ON t.destinataire_id = c2.id
          WHERE 1";

$params = [];

if (!empty($type)) {
    $query .= " AND t.type = ?";
    $params[] = $type;
}

if (!empty($date_debut)) {
    $query .= " AND t.date_transaction >= ?";
    $params[] = $date_debut;
}

if (!empty($date_fin)) {
    $query .= " AND t.date_transaction <= ?";
    $params[] = $date_fin;
}

$query .= " ORDER BY t.date_transaction DESC LIMIT $transactions_par_page OFFSET $offset";

$stmt = $connexion->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des transactions</title>
    <link rel="stylesheet" href="/banque_app/css/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/banque_app/css/transaction_history.css?v=<?php echo time(); ?>">
    <style>

    </style>
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
        <h1>Historique des transactions</h1>

        <form method="GET" class="filters">
            <label>Type :
                <select name="type">
                    <option value="">--Tous--</option>
                    <option value="depot" <?= $type === 'depot' ? 'selected' : '' ?>>Dépôt</option>
                    <option value="retrait" <?= $type === 'retrait' ? 'selected' : '' ?>>Retrait</option>
                    <option value="transfert" <?= $type === 'transfert' ? 'selected' : '' ?>>Transfert</option>
                </select>
            </label>
            <label>Date début :
                <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>">
            </label>
            <label>Date fin :
                <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>">
            </label>
            <button type="submit">Filtrer</button>
        </form>

        <table class="user-table transaction-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Montant (€)</th>
                    <th>Destinataire</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['date_transaction']) ?></td>
                            <td><?= htmlspecialchars($t['prenom_client'] . ' ' . $t['nom_client']) ?></td>
                            <td class="<?= $t['type'] ?>"><?= ucfirst($t['type']) ?></td>
                            <td><?= number_format($t['montant'], 2, ',', ' ') ?></td>
                            <td>
                                <?= $t['type'] === 'transfert' && $t['destinataire_id']
                                    ? htmlspecialchars($t['prenom_destinataire'] . ' ' . $t['nom_destinataire'])
                                    : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5">Aucune transaction trouvée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
            <button>← Précédent</button>
        </a>
    <?php endif; ?>

    <span>Page <?= $page ?> / <?= $total_pages ?></span>

    <?php if ($page < $total_pages): ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
            <button>Suivant →</button>
        </a>
    <?php endif; ?>
</div>
<?php endif; ?>

        <a href="dashboard.php" class="back-btn">← Retour au tableau de bord</a>

    </main>
</body>
</html>

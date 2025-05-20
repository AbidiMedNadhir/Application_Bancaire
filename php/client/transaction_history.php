<?php
session_start();
require_once '../connect.php'; // Ce fichier inclut maintenant l'autoloader et les classes

// Vérification de l'authentification et du rôle
Auth::requireRole('client', '../signin.php');

try {
    // Récupération du client connecté
    $client = Auth::getCurrentClient();
    
    if (!$client) {
        echo "Client introuvable.";
        exit();
    }
    
    // Pagination
    $limit = 6;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    
    // Filtrage par type
    $type = $_GET['type'] ?? '';
    $allowed_types = ['depot', 'retrait', 'transfert'];
    
    if ($type && !in_array($type, $allowed_types)) {
        $type = '';
    }
    
    // Récupération des transactions avec la classe Client
    $transactions = $client->getTransactionHistory($type, $page, $limit);
    $total_transactions = $client->countTransactions($type);
    $total_pages = ceil($total_transactions / $limit);
    $offset = ($page - 1) * $limit;
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
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

    <script src="/banque_app/js/app.js"></script>
</body>
</html>

<?php
session_start();
require_once '../connect.php';

// Vérification de session admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../signin.php");
    exit();
}

// Vérification de l'ID de l'utilisateur
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "Aucun utilisateur trouvé.";
    exit();
}

// Récupérer l'ID de l'utilisateur
$user_id = $_GET['user_id'];

// Récupérer les informations de l'utilisateur
$sql = "SELECT users.*, clients.nom, clients.prenom, clients.email, clients.telephone, clients.date_naissance, clients.adresse, clients.numero_compte, clients.solde, clients.created_at, users.last_login 
        FROM users 
        LEFT JOIN clients ON users.id = clients.user_id 
        WHERE users.id = :user_id";

$query = $connexion->prepare($sql);
$query->execute(['user_id' => $user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur non trouvé.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'utilisateur</title>
    <link rel="stylesheet" href="../../css/user_details.css?v=<?php echo time(); ?>"> <!-- Lien vers le CSS spécifique -->
</head>
<body>
    <h1>Détails de l'utilisateur</h1>

    <div class="user-details">
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Téléphone :</strong> <?= htmlspecialchars($user['telephone']) ?></p>
        <p><strong>Date de naissance :</strong> <?= htmlspecialchars($user['date_naissance']) ?></p>
        <p><strong>Adresse :</strong> <?= htmlspecialchars($user['adresse']) ?></p>
        <p><strong>Numéro de compte :</strong> <?= htmlspecialchars($user['numero_compte']) ?></p>
        <p><strong>Solde :</strong> <?= htmlspecialchars($user['solde']) ?> €</p>
        <p><strong>Date d'inscription :</strong> <?= htmlspecialchars($user['created_at']) ?></p>
        <p><strong>Dernière connexion :</strong> <?= htmlspecialchars($user['last_login']) ?></p>
    </div>

    <a href="user_list.php" class="back-btn">Retour à la liste des utilisateurs</a>
</body>
</html>

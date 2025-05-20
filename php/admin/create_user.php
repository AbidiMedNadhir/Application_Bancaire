<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <link rel="stylesheet" href="/banque_app/css/create_user.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="header-image"></div>

    <main class="container">
        <h2>Créer un nouveau client</h2>
        <form action="/banque_app/php/admin/create_user_handler.php" method="POST">

            <!-- Champs de la table users -->
            <label for="username">Nom d'utilisateur (username):</label>
            <input type="text" id="username" name="username" maxlength="255" required>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" maxlength="255" required>

            <label for="role">Rôle:</label>
            <select id="role" name="role" required>
                <option value="client">Client</option>
                <option value="admin">Admin</option>
            </select>

            <!-- Champs de la table clients -->
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" maxlength="100" required>

            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" maxlength="100" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" maxlength="255" required>

            <label for="telephone">Téléphone:</label>
            <input type="text" id="telephone" name="telephone" maxlength="15" pattern="[0-9+ ]+" required>

            <label for="date_naissance">Date de naissance:</label>
            <input type="date" id="date_naissance" name="date_naissance" required>

            <label for="adresse">Adresse:</label>
            <input type="text" id="adresse" name="adresse" required>

            <label for="numero_compte">Numéro de compte:</label>
            <input type="text" id="numero_compte" name="numero_compte" maxlength="50" required>

            <label for="solde">Solde initial (€):</label>
            <input type="number" id="solde" name="solde" step="0.01" min="0" required>

            <button type="submit">Créer le client</button>
        </form>
    </main>
    <script src="/banque_app/js/app.js"></script>

</body>
</html>

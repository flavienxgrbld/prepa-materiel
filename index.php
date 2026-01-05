<?php
session_start();

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['connected']) && $_SESSION['connected'] === true) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Connexion</h1>
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    Identifiants incorrects
                </div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>

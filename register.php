<?php
session_start();
require_once 'config.php';

// Script pour créer un nouvel utilisateur
// À utiliser uniquement pour ajouter des utilisateurs (à sécuriser en production)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = "Tous les champs sont requis";
    } else {
        try {
            // Vérifier si l'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            
            if ($stmt->fetch()) {
                $error = "Cet utilisateur existe déjà";
            } else {
                // Hash du mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertion du nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
                $stmt->execute([
                    'username' => $username,
                    'password' => $hashedPassword,
                    'email' => $email
                ]);
                
                $success = "Utilisateur créé avec succès !";
            }
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un utilisateur</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Créer un utilisateur</h1>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Créer</button>
            </form>
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php" style="color: #667eea;">Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>

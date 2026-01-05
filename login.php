<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        header('Location: index.php?error=1');
        exit;
    }
    
    try {
        // Recherche de l'utilisateur dans la base de données
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        // Vérification du mot de passe
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['connected'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: home.php');
            exit;
        } else {
            header('Location: index.php?error=1');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        header('Location: index.php?error=2');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}

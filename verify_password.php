<?php
require_once 'config.php';

echo "<h2>Vérification du mot de passe</h2>";

try {
    // Récupérer l'utilisateur admin
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if ($user) {
        echo "Utilisateur trouvé : " . $user['username'] . "<br><br>";
        
        // Tester le mot de passe
        $testPassword = 'admin123';
        echo "Test du mot de passe '$testPassword' : ";
        
        if (password_verify($testPassword, $user['password'])) {
            echo "✓ <strong style='color: green;'>VALIDE</strong><br>";
            echo "Les identifiants <strong>admin / admin123</strong> devraient fonctionner.<br>";
        } else {
            echo "❌ <strong style='color: red;'>INVALIDE</strong><br><br>";
            echo "Le hash du mot de passe ne correspond pas. Je vais le mettre à jour...<br><br>";
            
            // Créer un nouveau hash
            $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe
            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
            $stmt->execute(['password' => $newHash]);
            
            echo "✓ Mot de passe mis à jour avec succès !<br>";
            echo "Nouveau hash : " . $newHash . "<br><br>";
            
            // Vérifier à nouveau
            if (password_verify($testPassword, $newHash)) {
                echo "✓ Vérification : Le nouveau hash fonctionne correctement<br>";
                echo "<strong style='color: green;'>Vous pouvez maintenant vous connecter avec : admin / admin123</strong><br>";
            }
        }
    } else {
        echo "❌ Utilisateur 'admin' non trouvé<br>";
    }
    
    echo "<br><br><a href='index.php'>→ Aller à la page de connexion</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}

<?php
require_once 'config.php';

echo "<h2>Test de connexion à la base de données</h2>";

try {
    // Test de connexion
    echo "✓ Connexion à MySQL réussie<br><br>";
    
    // Vérifier si la table users existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'users' existe<br><br>";
        
        // Compter les utilisateurs
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "Nombre d'utilisateurs dans la base : " . $result['count'] . "<br><br>";
        
        // Afficher les utilisateurs
        $stmt = $pdo->query("SELECT id, username, email, created_at FROM users");
        $users = $stmt->fetchAll();
        
        echo "<h3>Liste des utilisateurs :</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Date de création</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['email'] . "</td>";
            echo "<td>" . $user['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Créer un nouvel utilisateur de test si nécessaire
        echo "<h3>Créer un utilisateur de test :</h3>";
        $testUsername = 'admin';
        $testPassword = 'admin123';
        
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $testUsername]);
        
        if ($stmt->fetch()) {
            echo "ℹ L'utilisateur '$testUsername' existe déjà<br>";
            echo "<strong>Identifiants de test : admin / admin123</strong><br>";
        } else {
            // Créer l'utilisateur
            $hashedPassword = password_hash($testPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
            $stmt->execute([
                'username' => $testUsername,
                'password' => $hashedPassword,
                'email' => 'admin@test.com'
            ]);
            echo "✓ Utilisateur '$testUsername' créé avec succès<br>";
            echo "<strong>Identifiants : admin / admin123</strong><br>";
        }
        
        // Test de vérification du mot de passe
        echo "<br><h3>Vérification du mot de passe :</h3>";
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $testUsername]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "Test du mot de passe '$testPassword' : ";
            
            if (password_verify($testPassword, $user['password'])) {
                echo "✓ <strong style='color: green;'>VALIDE</strong><br>";
                echo "Les identifiants <strong>admin / admin123</strong> fonctionnent correctement.<br>";
            } else {
                echo "❌ <strong style='color: red;'>INVALIDE</strong><br>";
                echo "Le hash du mot de passe ne correspond pas. Mise à jour...<br>";
                
                // Créer un nouveau hash
                $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
                
                // Mettre à jour le mot de passe
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
                $stmt->execute(['password' => $newHash, 'username' => $testUsername]);
                
                echo "✓ Mot de passe mis à jour avec succès !<br>";
                echo "<strong style='color: green;'>Vous pouvez maintenant vous connecter avec : admin / admin123</strong><br>";
            }
        }
        
    } else {
        echo "❌ La table 'users' n'existe pas<br>";
        echo "Veuillez importer le fichier database.sql dans phpMyAdmin<br>";
    }
    
    echo "<br><br><a href='index.php'>← Retour à la page de connexion</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
    echo "<br>Vérifiez que :<br>";
    echo "1. MySQL est démarré dans Laragon<br>";
    echo "2. La base de données 'prepa_materiel' existe<br>";
    echo "3. Les identifiants dans config.php sont corrects<br>";
    echo "4. Le fichier database.sql a été importé<br>";
}

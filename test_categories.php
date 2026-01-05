<?php
require_once 'config.php';

echo "<h2>Test de la base de données</h2>";

try {
    // Vérifier si la table categories existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Table 'categories' existe<br><br>";
        
        // Compter les catégories
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch();
        echo "Nombre de catégories : " . $result['count'] . "<br><br>";
        
        // Afficher les catégories
        $stmt = $pdo->query("SELECT * FROM categories");
        $cats = $stmt->fetchAll();
        
        echo "<h3>Liste des catégories :</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Couleur</th><th>Icône</th><th>Actif</th></tr>";
        foreach ($cats as $cat) {
            echo "<tr>";
            echo "<td>" . $cat['id'] . "</td>";
            echo "<td>" . $cat['nom'] . "</td>";
            echo "<td>" . $cat['couleur'] . "</td>";
            echo "<td>" . $cat['icone'] . "</td>";
            echo "<td>" . ($cat['actif'] ? 'Oui' : 'Non') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    } else {
        echo "❌ La table 'categories' n'existe pas<br>";
        echo "Veuillez importer le fichier database.sql mis à jour dans phpMyAdmin<br>";
    }
    
    echo "<br><a href='materiel.php'>← Retour au matériel</a>";
    
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}

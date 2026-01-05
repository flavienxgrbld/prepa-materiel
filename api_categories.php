<?php
session_start();
require_once 'config.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['connected']) || $_SESSION['connected'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            // Récupérer toutes les catégories
            $stmt = $pdo->query("SELECT * FROM categories ORDER BY nom ASC");
            $categories = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $categories]);
            break;

        case 'add':
            // Ajouter une nouvelle catégorie
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO categories (nom, couleur, icone, actif) 
                VALUES (:nom, :couleur, :icone, :actif)
            ");
            
            $stmt->execute([
                'nom' => $data['nom'],
                'couleur' => $data['couleur'] ?? '#667eea',
                'icone' => $data['icone'] ?? 'fa-box',
                'actif' => isset($data['actif']) ? (int)$data['actif'] : 1
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Catégorie ajoutée avec succès', 'id' => $pdo->lastInsertId()]);
            break;

        case 'update':
            // Modifier une catégorie
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE categories 
                SET nom = :nom, couleur = :couleur, icone = :icone, actif = :actif
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $data['id'],
                'nom' => $data['nom'],
                'couleur' => $data['couleur'] ?? '#667eea',
                'icone' => $data['icone'] ?? 'fa-box',
                'actif' => isset($data['actif']) ? (int)$data['actif'] : 1
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Catégorie modifiée avec succès']);
            break;

        case 'delete':
            // Supprimer une catégorie
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            // Vérifier si la catégorie est utilisée par du matériel
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM materiel m JOIN categories c ON m.categorie = c.nom WHERE c.id = :id");
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                throw new Exception('Cette catégorie est utilisée par ' . $result['count'] . ' matériel(s). Impossible de la supprimer.');
            }
            
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Catégorie supprimée avec succès']);
            break;

        case 'get':
            // Récupérer une catégorie spécifique
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $categorie = $stmt->fetch();
            
            if (!$categorie) {
                throw new Exception('Catégorie non trouvée');
            }
            
            echo json_encode(['success' => true, 'data' => $categorie]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

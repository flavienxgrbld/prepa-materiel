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
            // Récupérer tous les matériels
            $stmt = $pdo->query("SELECT * FROM materiel ORDER BY date_ajout DESC");
            $materiels = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $materiels]);
            break;

        case 'add':
            // Ajouter un nouveau matériel
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO materiel (reference, nom, categorie, statut, localisation, prix_achat, description, created_by) 
                VALUES (:reference, :nom, :categorie, :statut, :localisation, :prix_achat, :description, :created_by)
            ");
            
            $stmt->execute([
                'reference' => $data['reference'],
                'nom' => $data['nom'],
                'categorie' => $data['categorie'],
                'statut' => $data['statut'],
                'localisation' => $data['localisation'] ?? null,
                'prix_achat' => $data['prix_achat'] ?? null,
                'description' => $data['description'] ?? null,
                'created_by' => $_SESSION['user_id']
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Matériel ajouté avec succès', 'id' => $pdo->lastInsertId()]);
            break;

        case 'update':
            // Modifier un matériel
            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE materiel 
                SET reference = :reference, nom = :nom, categorie = :categorie, 
                    statut = :statut, localisation = :localisation, prix_achat = :prix_achat, 
                    description = :description
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $data['id'],
                'reference' => $data['reference'],
                'nom' => $data['nom'],
                'categorie' => $data['categorie'],
                'statut' => $data['statut'],
                'localisation' => $data['localisation'] ?? null,
                'prix_achat' => $data['prix_achat'] ?? null,
                'description' => $data['description'] ?? null
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Matériel modifié avec succès']);
            break;

        case 'delete':
            // Supprimer un matériel
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            $stmt = $pdo->prepare("DELETE FROM materiel WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Matériel supprimé avec succès']);
            break;

        case 'get':
            // Récupérer un matériel spécifique
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM materiel WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $materiel = $stmt->fetch();
            
            if (!$materiel) {
                throw new Exception('Matériel non trouvé');
            }
            
            echo json_encode(['success' => true, 'data' => $materiel]);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

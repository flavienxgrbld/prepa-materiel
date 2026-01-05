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
            // Récupérer tous les utilisateurs
            $stmt = $pdo->query("SELECT id, username, email, nom_complet, role, telephone, actif, created_at FROM users ORDER BY created_at DESC");
            $users = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $users]);
            break;

        case 'add':
            // Ajouter un nouvel utilisateur
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Vérifier si l'utilisateur existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email']
            ]);
            
            if ($stmt->fetch()) {
                throw new Exception('Un utilisateur avec ce nom ou cet email existe déjà');
            }
            
            // Hasher le mot de passe
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, nom_complet, role, telephone, actif) 
                VALUES (:username, :password, :email, :nom_complet, :role, :telephone, :actif)
            ");
            
            $stmt->execute([
                'username' => $data['username'],
                'password' => $hashedPassword,
                'email' => $data['email'],
                'nom_complet' => $data['nom_complet'] ?? null,
                'role' => $data['role'],
                'telephone' => $data['telephone'] ?? null,
                'actif' => isset($data['actif']) ? (int)$data['actif'] : 1
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Utilisateur ajouté avec succès', 'id' => $pdo->lastInsertId()]);
            break;

        case 'update':
            // Modifier un utilisateur
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Vérifier si le username/email n'est pas déjà utilisé par un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
            $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'id' => $data['id']
            ]);
            
            if ($stmt->fetch()) {
                throw new Exception('Ce nom d\'utilisateur ou email est déjà utilisé');
            }
            
            // Si un nouveau mot de passe est fourni, le hasher
            if (!empty($data['password'])) {
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = :username, password = :password, email = :email, 
                        nom_complet = :nom_complet, role = :role, telephone = :telephone, 
                        actif = :actif
                    WHERE id = :id
                ");
                $stmt->execute([
                    'id' => $data['id'],
                    'username' => $data['username'],
                    'password' => $hashedPassword,
                    'email' => $data['email'],
                    'nom_complet' => $data['nom_complet'] ?? null,
                    'role' => $data['role'],
                    'telephone' => $data['telephone'] ?? null,
                    'actif' => isset($data['actif']) ? (int)$data['actif'] : 1
                ]);
            } else {
                // Mise à jour sans changer le mot de passe
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = :username, email = :email, nom_complet = :nom_complet, 
                        role = :role, telephone = :telephone, actif = :actif
                    WHERE id = :id
                ");
                $stmt->execute([
                    'id' => $data['id'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'nom_complet' => $data['nom_complet'] ?? null,
                    'role' => $data['role'],
                    'telephone' => $data['telephone'] ?? null,
                    'actif' => isset($data['actif']) ? (int)$data['actif'] : 1
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Utilisateur modifié avec succès']);
            break;

        case 'delete':
            // Supprimer un utilisateur
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            // Ne pas permettre de supprimer son propre compte
            if ($id == $_SESSION['user_id']) {
                throw new Exception('Vous ne pouvez pas supprimer votre propre compte');
            }
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
            break;

        case 'get':
            // Récupérer un utilisateur spécifique
            $id = $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            $stmt = $pdo->prepare("SELECT id, username, email, nom_complet, role, telephone, actif, created_at FROM users WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            echo json_encode(['success' => true, 'data' => $user]);
            break;

        case 'toggle_status':
            // Activer/Désactiver un utilisateur
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID manquant');
            }
            
            if ($id == $_SESSION['user_id']) {
                throw new Exception('Vous ne pouvez pas modifier votre propre statut');
            }
            
            $stmt = $pdo->prepare("UPDATE users SET actif = NOT actif WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            echo json_encode(['success' => true, 'message' => 'Statut modifié avec succès']);
            break;

        default:
            throw new Exception('Action non reconnue');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

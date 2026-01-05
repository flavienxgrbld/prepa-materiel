-- Création de la base de données
CREATE DATABASE IF NOT EXISTS prepa_materiel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE prepa_materiel;

-- Création de la table users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    nom_complet VARCHAR(255),
    role ENUM('Admin', 'Gestionnaire', 'Technicien', 'Utilisateur') DEFAULT 'Utilisateur',
    telephone VARCHAR(20),
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion d'utilisateurs par défaut
-- Mot de passe par défaut : admin123
INSERT INTO users (username, password, email, nom_complet, role, telephone, actif) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'Administrateur Principal', 'Admin', '0123456789', TRUE),
('jdupont', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'j.dupont@example.com', 'Jean Dupont', 'Gestionnaire', '0612345678', TRUE),
('mmartin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'm.martin@example.com', 'Marie Martin', 'Technicien', '0698765432', TRUE),
('ldurand', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'l.durand@example.com', 'Luc Durand', 'Utilisateur', '0687654321', TRUE);

-- Note: Le mot de passe par défaut est "admin123"
-- Pour créer un nouveau hash, utilisez: password_hash('votre_mot_de_passe', PASSWORD_DEFAULT)

-- Création de la table materiel
CREATE TABLE IF NOT EXISTS materiel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    nom VARCHAR(255) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    statut ENUM('Disponible', 'Maintenance', 'Hors Service', 'En Prêt') DEFAULT 'Disponible',
    localisation VARCHAR(255),
    prix_achat DECIMAL(10, 2),
    description TEXT,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Création de la table categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE,
    couleur VARCHAR(7) DEFAULT '#667eea',
    icone VARCHAR(50) DEFAULT 'fa-box',
    actif BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion des catégories par défaut
INSERT INTO categories (nom, couleur, icone) VALUES
('Informatique', '#667eea', 'fa-laptop'),
('Bureau', '#f39c12', 'fa-briefcase'),
('Audio/Vidéo', '#e74c3c', 'fa-video'),
('Réseau', '#3498db', 'fa-network-wired'),
('Mobilier', '#9b59b6', 'fa-chair');

-- Insertion de données de test
INSERT INTO materiel (reference, nom, categorie, statut, localisation, prix_achat, description) VALUES
('MAT-001', 'Ordinateur Portable HP ProBook 450', 'Informatique', 'Disponible', 'Bureau 201', 799.99, 'Intel Core i5, 8GB RAM, 256GB SSD'),
('MAT-002', 'Imprimante Canon Pixma', 'Bureau', 'Maintenance', 'Salle 103', 149.99, 'Imprimante jet d\'encre couleur'),
('MAT-003', 'Projecteur Epson EB-X41', 'Audio/Vidéo', 'Disponible', 'Salle de réunion A', 449.99, 'Projecteur 3600 lumens, Full HD'),
('MAT-004', 'Routeur TP-Link AC1750', 'Réseau', 'Hors Service', 'Local technique', 89.99, 'Routeur WiFi double bande'),
('MAT-005', 'Écran Dell UltraSharp 24"', 'Informatique', 'Disponible', 'Bureau 205', 299.99, 'Écran IPS Full HD avec pied réglable');

<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connected']) || $_SESSION['connected'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prépa Matériel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-tools"></i> Prépa Matériel</h2>
        </div>
        <nav class="nav-menu">
            <a href="home.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="materiel.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Matériel</span>
            </a>
            <a href="equipes.php" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Équipes</span>
            </a>
            <a href="planning.php" class="nav-item">
                <i class="fas fa-calendar"></i>
                <span>Planning</span>
            </a>
            <a href="statistiques.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Statistiques</span>
            </a>
            <a href="parametres.php" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="logout.php" class="nav-item logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-left">
                <h1>Dashboard</h1>
                <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <div class="header-right">
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total Matériel</h3>
                        <p class="stat-number">245</p>
                        <span class="stat-change positive">+12% ce mois</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Disponible</h3>
                        <p class="stat-number">187</p>
                        <span class="stat-change positive">76%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-details">
                        <h3>En Maintenance</h3>
                        <p class="stat-number">23</p>
                        <span class="stat-change">9%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Hors Service</h3>
                        <p class="stat-number">35</p>
                        <span class="stat-change negative">14%</span>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Quick Actions -->
            <div class="dashboard-grid">
                <!-- Recent Activity -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-history"></i> Activité Récente</h2>
                        <a href="#" class="view-all">Voir tout</a>
                    </div>
                    <div class="card-content">
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon blue">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">Nouveau matériel ajouté</p>
                                    <p class="activity-time">Il y a 2 heures</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon green">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">Maintenance terminée</p>
                                    <p class="activity-time">Il y a 4 heures</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon orange">
                                    <i class="fas fa-wrench"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">Réparation en cours</p>
                                    <p class="activity-time">Il y a 6 heures</p>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon purple">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="activity-details">
                                    <p class="activity-title">Nouvel utilisateur créé</p>
                                    <p class="activity-time">Hier</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-bolt"></i> Actions Rapides</h2>
                    </div>
                    <div class="card-content">
                        <div class="quick-actions">
                            <button class="action-btn primary">
                                <i class="fas fa-plus"></i>
                                Ajouter Matériel
                            </button>
                            <button class="action-btn secondary">
                                <i class="fas fa-file-export"></i>
                                Exporter Données
                            </button>
                            <button class="action-btn success">
                                <i class="fas fa-calendar-plus"></i>
                                Planifier Maintenance
                            </button>
                            <button class="action-btn info">
                                <i class="fas fa-chart-line"></i>
                                Voir Rapports
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Status Table -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Matériel Récent</h2>
                    <a href="#" class="view-all">Voir tout</a>
                </div>
                <div class="card-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>État</th>
                                <th>Dernière MàJ</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#001</td>
                                <td>Ordinateur Portable HP</td>
                                <td>Informatique</td>
                                <td><span class="badge success">Disponible</span></td>
                                <td>05/01/2026</td>
                                <td>
                                    <button class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#002</td>
                                <td>Imprimante Canon</td>
                                <td>Bureau</td>
                                <td><span class="badge warning">Maintenance</span></td>
                                <td>04/01/2026</td>
                                <td>
                                    <button class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#003</td>
                                <td>Projecteur Epson</td>
                                <td>Audio/Vidéo</td>
                                <td><span class="badge success">Disponible</span></td>
                                <td>03/01/2026</td>
                                <td>
                                    <button class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#004</td>
                                <td>Routeur TP-Link</td>
                                <td>Réseau</td>
                                <td><span class="badge danger">Hors Service</span></td>
                                <td>02/01/2026</td>
                                <td>
                                    <button class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>#005</td>
                                <td>Écran Dell 24"</td>
                                <td>Informatique</td>
                                <td><span class="badge success">Disponible</span></td>
                                <td>01/01/2026</td>
                                <td>
                                    <button class="btn-icon" title="Modifier"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connected']) || $_SESSION['connected'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Récupérer les utilisateurs depuis la base de données
$stmt = $pdo->query("SELECT id, username, email, nom_complet, role, telephone, actif, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Rôles disponibles
$roles = ['Admin', 'Gestionnaire', 'Technicien', 'Utilisateur'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Équipes - Prépa Matériel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filters {
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            font-size: 14px;
            min-width: 200px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-add {
            margin-left: auto;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .modal-header h2 {
            font-size: 24px;
            color: #2c3e50;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
        }

        .modal-close:hover {
            color: #e74c3c;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-field label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-field input,
        .form-field select {
            padding: 10px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-field input:focus,
        .form-field select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-field-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .form-field-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
        }

        .btn-cancel,
        .btn-submit {
            padding: 10px 25px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-cancel {
            background: #ecf0f1;
            color: #7f8c8d;
        }

        .btn-cancel:hover {
            background: #bdc3c7;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .role-badge.admin {
            background: #fee;
            color: #e74c3c;
        }

        .role-badge.gestionnaire {
            background: #e8f5e9;
            color: #4caf50;
        }

        .role-badge.technicien {
            background: #e3f2fd;
            color: #2196f3;
        }

        .role-badge.utilisateur {
            background: #f5f5f5;
            color: #9e9e9e;
        }

        .status-toggle {
            cursor: pointer;
            font-size: 18px;
        }

        .status-toggle.active {
            color: #4caf50;
        }

        .status-toggle.inactive {
            color: #e74c3c;
        }

        .password-note {
            font-size: 12px;
            color: #7f8c8d;
            font-style: italic;
            margin-top: 5px;
        }
    </style>
</head>
<body class="dashboard-body">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h2><i class="fas fa-tools"></i> Prépa Matériel</h2>
        </div>
        <nav class="nav-menu">
            <a href="home.php" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="materiel.php" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Matériel</span>
            </a>
            <a href="equipes.php" class="nav-item active">
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
                <h1>Gestion des Équipes</h1>
                <p>Gérez les utilisateurs et les rôles</p>
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
            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label for="search"><i class="fas fa-search"></i></label>
                    <input type="text" id="search" placeholder="Rechercher un utilisateur...">
                </div>
                <div class="filter-group">
                    <label for="role-filter">Rôle :</label>
                    <select id="role-filter">
                        <option value="">Tous</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status-filter">Statut :</label>
                    <select id="status-filter">
                        <option value="">Tous</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
                <button class="btn-add" onclick="openModal()">
                    <i class="fas fa-user-plus"></i>
                    Ajouter un utilisateur
                </button>
            </div>

            <!-- Users Table -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> Liste des Utilisateurs</h2>
                    <span id="count-display"><?php echo count($users); ?> utilisateur<?php echo count($users) > 1 ? 's' : ''; ?></span>
                </div>
                <div class="card-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nom d'utilisateur</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Téléphone</th>
                                <th>Statut</th>
                                <th>Date d'inscription</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr data-id="<?php echo $user['id']; ?>" data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['actif']; ?>">
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['nom_complet'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $roleClass = strtolower($user['role']);
                                            ?>
                                            <span class="role-badge <?php echo $roleClass; ?>"><?php echo htmlspecialchars($user['role']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['telephone'] ?? '-'); ?></td>
                                        <td>
                                            <i class="fas fa-circle status-toggle <?php echo $user['actif'] ? 'active' : 'inactive'; ?>" 
                                               onclick="toggleStatus(<?php echo $user['id']; ?>)" 
                                               title="<?php echo $user['actif'] ? 'Actif' : 'Inactif'; ?>"></i>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button class="btn-icon" onclick="viewUser(<?php echo $user['id']; ?>)" title="Voir"><i class="fas fa-eye"></i></button>
                                            <button class="btn-icon" onclick="editUser(<?php echo $user['id']; ?>)" title="Modifier"><i class="fas fa-edit"></i></button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn-icon" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Supprimer"><i class="fas fa-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                                        <h3>Aucun utilisateur</h3>
                                        <p>Commencez par ajouter des utilisateurs</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal" id="userModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Ajouter un utilisateur</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="userForm">
                <div class="form-row">
                    <div class="form-field">
                        <label for="username">Nom d'utilisateur *</label>
                        <input type="text" id="username" name="username" required placeholder="jdupont">
                    </div>
                    <div class="form-field">
                        <label for="nom_complet">Nom complet</label>
                        <input type="text" id="nom_complet" name="nom_complet" placeholder="Jean Dupont">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="j.dupont@example.com">
                    </div>
                    <div class="form-field">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" placeholder="0123456789">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="password">Mot de passe <span id="password-required">*</span></label>
                        <input type="password" id="password" name="password" placeholder="••••••••">
                        <p class="password-note" id="password-note">Laissez vide pour garder l'actuel</p>
                    </div>
                </div>
                <div class="form-field-checkbox">
                    <input type="checkbox" id="actif" name="actif" checked>
                    <label for="actif">Utilisateur actif</label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentEditId = null;

        function openModal() {
            currentEditId = null;
            document.getElementById('userModal').classList.add('active');
            document.getElementById('modal-title').textContent = 'Ajouter un utilisateur';
            document.getElementById('userForm').reset();
            document.getElementById('actif').checked = true;
            document.getElementById('password').required = true;
            document.getElementById('password-required').style.display = 'inline';
            document.getElementById('password-note').style.display = 'none';
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
            currentEditId = null;
        }

        async function editUser(id) {
            currentEditId = id;
            document.getElementById('userModal').classList.add('active');
            document.getElementById('modal-title').textContent = 'Modifier l\'utilisateur';
            document.getElementById('password').required = false;
            document.getElementById('password-required').style.display = 'none';
            document.getElementById('password-note').style.display = 'block';
            
            try {
                const response = await fetch(`api_equipes.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('username').value = data.username;
                    document.getElementById('nom_complet').value = data.nom_complet || '';
                    document.getElementById('email').value = data.email;
                    document.getElementById('telephone').value = data.telephone || '';
                    document.getElementById('role').value = data.role;
                    document.getElementById('password').value = '';
                    document.getElementById('actif').checked = data.actif == 1;
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                alert('Erreur lors du chargement de l\'utilisateur');
                console.error(error);
            }
        }

        function viewUser(id) {
            fetch(`api_equipes.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const data = result.data;
                        const details = `
Nom d'utilisateur: ${data.username}
Nom complet: ${data.nom_complet || '-'}
Email: ${data.email}
Rôle: ${data.role}
Téléphone: ${data.telephone || '-'}
Statut: ${data.actif ? 'Actif' : 'Inactif'}
Date d'inscription: ${new Date(data.created_at).toLocaleDateString('fr-FR')}
                        `;
                        alert(details);
                    }
                })
                .catch(error => {
                    alert('Erreur lors du chargement des détails');
                    console.error(error);
                });
        }

        async function deleteUser(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                const response = await fetch('api_equipes.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                alert('Erreur lors de la suppression');
                console.error(error);
            }
        }

        async function toggleStatus(id) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_status');
                formData.append('id', id);
                
                const response = await fetch('api_equipes.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                alert('Erreur lors de la modification du statut');
                console.error(error);
            }
        }

        // Fermer le modal en cliquant en dehors
        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Gestion du formulaire
        document.getElementById('userForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                username: document.getElementById('username').value,
                nom_complet: document.getElementById('nom_complet').value,
                email: document.getElementById('email').value,
                telephone: document.getElementById('telephone').value,
                role: document.getElementById('role').value,
                password: document.getElementById('password').value,
                actif: document.getElementById('actif').checked ? 1 : 0
            };
            
            if (currentEditId) {
                formData.id = currentEditId;
            }
            
            const action = currentEditId ? 'update' : 'add';
            
            try {
                const response = await fetch(`api_equipes.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                alert('Erreur lors de l\'enregistrement');
                console.error(error);
            }
        });

        // Filtres en temps réel
        document.getElementById('search').addEventListener('input', filterTable);
        document.getElementById('role-filter').addEventListener('change', filterTable);
        document.getElementById('status-filter').addEventListener('change', filterTable);

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const roleFilter = document.getElementById('role-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            const rows = document.querySelectorAll('#users-table-body tr');
            
            let visibleCount = 0;
            rows.forEach(row => {
                if (row.querySelector('td[colspan]')) {
                    return;
                }
                
                const text = row.textContent.toLowerCase();
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                
                const matchSearch = text.includes(search);
                const matchRole = !roleFilter || role === roleFilter;
                const matchStatus = !statusFilter || status === statusFilter;
                
                if (matchSearch && matchRole && matchStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('count-display').textContent = visibleCount + ' utilisateur' + (visibleCount > 1 ? 's' : '');
        }
    </script>
</body>
</html>

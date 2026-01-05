<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connected']) || $_SESSION['connected'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

// Récupérer le matériel depuis la base de données
$stmt = $pdo->query("SELECT * FROM materiel ORDER BY date_ajout DESC");
$materiels = $stmt->fetchAll();

// Récupérer les catégories depuis la base de données
$stmt = $pdo->query("SELECT * FROM categories WHERE actif = 1 ORDER BY nom ASC");
$categories = $stmt->fetchAll();

$statuts = ['Disponible', 'Maintenance', 'Hors Service', 'En Prêt'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matériel - Prépa Matériel</title>
    <link rel="stylesheet" href="style.css?v=2">
    <link rel="stylesheet" href="dashboard.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="materiel.js?v=2" defer></script>
    <style>
        /* Filters */
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

        /* Modal */
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
        .form-field select,
        .form-field textarea {
            padding: 10px 15px;
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-field textarea {
            resize: vertical;
            min-height: 100px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #2c3e50;
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
            <a href="materiel.php" class="nav-item active">
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
                <h1>Gestion du Matériel</h1>
                <p>Gérez votre inventaire de matériel</p>
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
                    <input type="text" id="search" placeholder="Rechercher du matériel...">
                </div>
                <div class="filter-group">
                    <label for="category">Catégorie :</label>
                    <select id="category">
                        <option value="">Toutes</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['nom']); ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">État :</label>
                    <select id="status">
                        <option value="">Tous</option>
                        <?php foreach ($statuts as $stat): ?>
                            <option value="<?php echo $stat; ?>"><?php echo $stat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn-add" onclick="openModal()" style="margin-left: 0;">
                    <i class="fas fa-plus"></i>
                    Ajouter du matériel
                </button>
                <button class="btn-add" onclick="openCategoryModal()" style="background: linear-gradient(135deg, #f39c12, #e67e22); margin-left: auto;">
                    <i class="fas fa-tags"></i>
                    Modifier les catégories
                </button>
            </div>

            <!-- Equipment Table -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Liste du Matériel</h2>
                    <span id="count-display"><?php echo count($materiels); ?> article<?php echo count($materiels) > 1 ? 's' : ''; ?></span>
                </div>
                <div class="card-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Référence</th>
                                <th>Nom</th>
                                <th>Catégorie</th>
                                <th>État</th>
                                <th>Localisation</th>
                                <th>Date d'ajout</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="equipment-table-body">
                            <?php if (count($materiels) > 0): ?>
                                <?php foreach ($materiels as $mat): ?>
                                    <tr data-id="<?php echo $mat['id']; ?>">
                                        <td><?php echo htmlspecialchars($mat['reference']); ?></td>
                                        <td><?php echo htmlspecialchars($mat['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($mat['categorie']); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = '';
                                            switch($mat['statut']) {
                                                case 'Disponible': $badgeClass = 'success'; break;
                                                case 'Maintenance': $badgeClass = 'warning'; break;
                                                case 'Hors Service': $badgeClass = 'danger'; break;
                                                case 'En Prêt': $badgeClass = 'info'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($mat['statut']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($mat['localisation'] ?? '-'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($mat['date_ajout'])); ?></td>
                                        <td>
                                            <button class="btn-icon" onclick="viewEquipment(<?php echo $mat['id']; ?>)" title="Voir"><i class="fas fa-eye"></i></button>
                                            <button class="btn-icon" onclick="editEquipment(<?php echo $mat['id']; ?>)" title="Modifier"><i class="fas fa-edit"></i></button>
                                            <button class="btn-icon" onclick="deleteEquipment(<?php echo $mat['id']; ?>)" title="Supprimer"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">
                                        <i class="fas fa-box-open"></i>
                                        <h3>Aucun matériel</h3>
                                        <p>Commencez par ajouter du matériel</p>
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
    <div class="modal" id="equipmentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Ajouter du matériel</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form id="equipmentForm">
                <div class="form-row">
                    <div class="form-field">
                        <label for="reference">Référence *</label>
                        <input type="text" id="reference" name="reference" required placeholder="MAT-001">
                    </div>
                    <div class="form-field">
                        <label for="name">Nom *</label>
                        <input type="text" id="name" name="name" required placeholder="Nom du matériel">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="modal-category">Catégorie *</label>
                        <select id="modal-category" name="category" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['nom']); ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="modal-status">État *</label>
                        <select id="modal-status" name="status" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($statuts as $stat): ?>
                                <option value="<?php echo $stat; ?>"><?php echo $stat; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="location">Localisation</label>
                        <input type="text" id="location" name="location" placeholder="Bureau, salle...">
                    </div>
                    <div class="form-field">
                        <label for="price">Prix d'achat (€)</label>
                        <input type="number" id="price" name="price" step="0.01" placeholder="0.00">
                    </div>
                </div>
                <div class="form-field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Description détaillée du matériel..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn-submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Management Modal -->
    <div class="modal" id="categoryModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2><i class="fas fa-tags"></i> Gestion des Catégories</h2>
                <button class="modal-close" onclick="closeCategoryModal()">&times;</button>
            </div>
            <div style="margin-bottom: 20px;">
                <button class="btn-add" onclick="openAddCategoryForm()" style="width: 100%; margin: 0;">
                    <i class="fas fa-plus"></i>
                    Ajouter une catégorie
                </button>
            </div>
            
            <!-- Add/Edit Category Form -->
            <div id="categoryFormContainer" style="display: none; margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <h3 id="category-form-title" style="margin-bottom: 15px; color: #2c3e50;">Nouvelle catégorie</h3>
                <form id="categoryForm">
                    <div class="form-row">
                        <div class="form-field">
                            <label for="cat-name">Nom *</label>
                            <input type="text" id="cat-name" required placeholder="Ex: Informatique">
                        </div>
                        <div class="form-field">
                            <label for="cat-color">Couleur</label>
                            <input type="color" id="cat-color" value="#667eea">
                        </div>
                        <div class="form-field">
                            <label for="cat-icon">Icône (Font Awesome)</label>
                            <input type="text" id="cat-icon" value="fa-box" placeholder="fa-laptop">
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button type="submit" class="btn-submit" style="flex: 1;">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                        <button type="button" class="btn-cancel" onclick="closeCategoryForm()">Annuler</button>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Couleur</th>
                            <th>Icône</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categories-list">
                        <!-- Les catégories seront chargées ici -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let currentEditId = null;
        let currentCategoryEditId = null;

        // Test de chargement du script
        console.log('Script chargé - fonctions disponibles');

        // === GESTION DU MATÉRIEL ===
        function openModal() {
            currentEditId = null;
            document.getElementById('equipmentModal').classList.add('active');
            document.getElementById('modal-title').textContent = 'Ajouter du matériel';
            document.getElementById('equipmentForm').reset();
        }

        function closeModal() {
            document.getElementById('equipmentModal').classList.remove('active');
            currentEditId = null;
        }

        async function editEquipment(id) {
            currentEditId = id;
            document.getElementById('equipmentModal').classList.add('active');
            document.getElementById('modal-title').textContent = 'Modifier le matériel';
            
            try {
                const response = await fetch(`api_materiel.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('reference').value = data.reference;
                    document.getElementById('name').value = data.nom;
                    document.getElementById('modal-category').value = data.categorie;
                    document.getElementById('modal-status').value = data.statut;
                    document.getElementById('location').value = data.localisation || '';
                    document.getElementById('price').value = data.prix_achat || '';
                    document.getElementById('description').value = data.description || '';
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                alert('Erreur lors du chargement du matériel');
                console.error(error);
            }
        }

        function viewEquipment(id) {
            fetch(`api_materiel.php?action=get&id=${id}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const data = result.data;
                        const details = `
                            Référence: ${data.reference}
                            Nom: ${data.nom}
                            Catégorie: ${data.categorie}
                            État: ${data.statut}
                            Localisation: ${data.localisation || '-'}
                            Prix d'achat: ${data.prix_achat ? data.prix_achat + '€' : '-'}
                            Description: ${data.description || '-'}
                            Date d'ajout: ${new Date(data.date_ajout).toLocaleDateString('fr-FR')}
                        `;
                        alert(details);
                    }
                })
                .catch(error => {
                    alert('Erreur lors du chargement des détails');
                    console.error(error);
                });
        }

        async function deleteEquipment(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce matériel ?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                const response = await fetch('api_materiel.php', {
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

        // Fermer le modal en cliquant en dehors
        document.getElementById('equipmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Gestion du formulaire
        document.getElementById('equipmentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                reference: document.getElementById('reference').value,
                nom: document.getElementById('name').value,
                categorie: document.getElementById('modal-category').value,
                statut: document.getElementById('modal-status').value,
                localisation: document.getElementById('location').value,
                prix_achat: document.getElementById('price').value,
                description: document.getElementById('description').value
            };
            
            if (currentEditId) {
                formData.id = currentEditId;
            }
            
            const action = currentEditId ? 'update' : 'add';
            
            try {
                const response = await fetch(`api_materiel.php?action=${action}`, {
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
        document.getElementById('category').addEventListener('change', filterTable);
        document.getElementById('status').addEventListener('change', filterTable);

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;
            const rows = document.querySelectorAll('#equipment-table-body tr');
            
            let visibleCount = 0;
            rows.forEach(row => {
                // Ignorer la ligne vide state
                if (row.querySelector('.empty-state')) {
                    return;
                }
                
                const text = row.textContent.toLowerCase();
                const cells = row.cells;
                
                if (!cells || cells.length < 3) {
                    return;
                }
                
                const rowCategory = cells[2].textContent;
                const badge = row.querySelector('.badge');
                const rowStatus = badge ? badge.textContent : '';
                
                const matchSearch = text.includes(search);
                const matchCategory = !category || rowCategory === category;
                const matchStatus = !status || rowStatus === status;
                
                if (matchSearch && matchCategory && matchStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            document.getElementById('count-display').textContent = visibleCount + ' article' + (visibleCount > 1 ? 's' : '');
        }

        // === GESTION DES CATÉGORIES ===
        function openCategoryModal() {
            document.getElementById('categoryModal').classList.add('active');
            loadCategories();
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').classList.remove('active');
            closeCategoryForm();
        }

        function openAddCategoryForm() {
            currentCategoryEditId = null;
            document.getElementById('categoryFormContainer').style.display = 'block';
            document.getElementById('category-form-title').textContent = 'Nouvelle catégorie';
            document.getElementById('categoryForm').reset();
            document.getElementById('cat-color').value = '#667eea';
            document.getElementById('cat-icon').value = 'fa-box';
        }

        function closeCategoryForm() {
            document.getElementById('categoryFormContainer').style.display = 'none';
            currentCategoryEditId = null;
        }

        async function loadCategories() {
            try {
                const response = await fetch('api_categories.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const tbody = document.getElementById('categories-list');
                    tbody.innerHTML = '';
                    
                    result.data.forEach(cat => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>
                                <i class="fas ${cat.icone}" style="color: ${cat.couleur}; margin-right: 8px;"></i>
                                <strong>${cat.nom}</strong>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 30px; height: 30px; background: ${cat.couleur}; border-radius: 5px;"></div>
                                    <span>${cat.couleur}</span>
                                </div>
                            </td>
                            <td><code>${cat.icone}</code></td>
                            <td>
                                <span class="badge ${cat.actif ? 'success' : 'danger'}">
                                    ${cat.actif ? 'Actif' : 'Inactif'}
                                </span>
                            </td>
                            <td>
                                <button class="btn-icon" onclick="editCategory(${cat.id})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="deleteCategory(${cat.id})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            } catch (error) {
                console.error('Erreur lors du chargement des catégories:', error);
                alert('Erreur lors du chargement des catégories');
            }
        }

        async function editCategory(id) {
            try {
                const response = await fetch(`api_categories.php?action=get&id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    currentCategoryEditId = id;
                    document.getElementById('categoryFormContainer').style.display = 'block';
                    document.getElementById('category-form-title').textContent = 'Modifier la catégorie';
                    document.getElementById('cat-name').value = result.data.nom;
                    document.getElementById('cat-color').value = result.data.couleur;
                    document.getElementById('cat-icon').value = result.data.icone;
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement de la catégorie');
            }
        }

        async function deleteCategory(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                const response = await fetch('api_categories.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    loadCategories();
                    // Recharger la page pour mettre à jour les sélecteurs de catégories
                    location.reload();
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            }
        }

        // Gestion du formulaire de catégorie
        document.getElementById('categoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                nom: document.getElementById('cat-name').value,
                couleur: document.getElementById('cat-color').value,
                icone: document.getElementById('cat-icon').value,
                actif: 1
            };
            
            if (currentCategoryEditId) {
                formData.id = currentCategoryEditId;
            }
            
            const action = currentCategoryEditId ? 'update' : 'add';
            
            try {
                const response = await fetch(`api_categories.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    closeCategoryForm();
                    loadCategories();
                    // Recharger la page pour mettre à jour les sélecteurs
                    location.reload();
                } else {
                    alert('Erreur: ' + result.error);
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'enregistrement');
            }
        });

        // Fermer le modal en cliquant en dehors
        document.getElementById('categoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCategoryModal();
            }
        });
            document.getElementById('count-display').textContent = visibleCount + ' article' + (visibleCount > 1 ? 's' : '');
        }
    </script>
</body>
</html>

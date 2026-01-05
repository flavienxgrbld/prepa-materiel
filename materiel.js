let currentEditId = null;
let currentCategoryEditId = null;

// Test de chargement
console.log('✓ Script externe chargé');

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

// === GESTION DES CATÉGORIES ===
function openCategoryModal() {
    console.log('openCategoryModal appelée');
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

console.log('✓ Toutes les fonctions de catégories définies');

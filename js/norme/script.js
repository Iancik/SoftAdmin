let currentCodVarianta = null;
let currentCategory = null;
let selectedResource = null;

function searchNorma() {
    const symbol = document.getElementById('searchSymbol').value;
    
    if (!symbol) {
        alert('Vă rugăm introduceți un simbol!');
        return;
    }
    
    fetch('pages/norme/search.php?symbol=' + encodeURIComponent(symbol))
        .then(response => {
            if (!response.ok) throw new Error('Eroare rețea: ' + response.status);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                if (data.multiple) {
                    showVariantsSelectionModal(data.variants);
                } else {
                    const variant = data.variants[0];
                    currentCodVarianta = variant.codVarianta;
                    
                    const selectInfoEl = document.getElementById('selectedVariantInfo') || 
                                        document.createElement('div');
                    
                    if (!document.getElementById('selectedVariantInfo')) {
                        selectInfoEl.id = 'selectedVariantInfo';
                        selectInfoEl.className = 'alert alert-info mb-3';
                        document.getElementById('categoryButtons').parentNode.insertBefore(
                            selectInfoEl, 
                            document.getElementById('categoryButtons')
                        );
                    }
                    
                    selectInfoEl.innerHTML = `
                        <strong>Varianta selectată:</strong> ${variant.simbol} - ${variant.Denumire || 'Fără denumire'}
                        <button type="button" class="btn-close float-end" 
                                onclick="this.parentNode.style.display='none'"></button>
                    `;
                    selectInfoEl.style.display = 'block';
                    
                    document.getElementById('categoryButtons').style.display = 'block';
                }
            } else {
                alert(data.error || 'Nu s-a găsit nicio normă cu acest simbol!');
            }
        })
        .catch(error => {
            console.error('Eroare:', error);
            alert(`A apărut o eroare: ${error.message}`);
        });
}

function showVariantsSelectionModal(variants) {
    let existingModal = document.getElementById('variantsModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    const modalHtml = `
        <div class="modal fade" id="variantsModal" tabindex="-1" aria-labelledby="variantsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="variantsModalLabel">Selectați varianta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>S-au găsit ${variants.length} variante. Selectați una din lista de mai jos:</p>
                        <div class="list-group">
                            ${variants.map((variant, index) => `
                                <button type="button" class="list-group-item list-group-item-action variant-item" 
                                        data-cod-varianta="${variant.codVarianta}"
                                        data-simbol="${variant.simbol}"
                                        data-denumire="${variant.Denumire || 'Fără denumire'}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">${variant.simbol}</h5>
                                        <small>Cod: ${variant.codVarianta}</small>
                                    </div>
                                    <p class="mb-1">${variant.Denumire || 'Fără denumire'}</p>
                                </button>
                            `).join('')}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = new bootstrap.Modal(document.getElementById('variantsModal'));
    modal.show();
    
    document.querySelectorAll('.variant-item').forEach(button => {
        button.addEventListener('click', function() {
            const codVarianta = this.getAttribute('data-cod-varianta');
            const simbol = this.getAttribute('data-simbol');
            const denumire = this.getAttribute('data-denumire');
            
            currentCodVarianta = codVarianta;
            
            const selectInfoEl = document.getElementById('selectedVariantInfo') || 
                               document.createElement('div');
            
            if (!document.getElementById('selectedVariantInfo')) {
                selectInfoEl.id = 'selectedVariantInfo';
                selectInfoEl.className = 'alert alert-info mb-3';
                document.getElementById('categoryButtons').parentNode.insertBefore(
                    selectInfoEl, 
                    document.getElementById('categoryButtons')
                );
            }
            
            selectInfoEl.innerHTML = `
                <strong>Varianta selectată:</strong> ${simbol} - ${denumire}
                <button type="button" class="btn-close float-end" 
                        onclick="this.parentNode.style.display='none'"></button>
            `;
            selectInfoEl.style.display = 'block';
            
            document.getElementById('categoryButtons').style.display = 'block';
            modal.hide();
        });
    });
}

function showAddResourceModal() {
    if (!currentCodVarianta) {
        alert('Vă rugăm să selectați mai întâi o normă!');
        return;
    }
    const modal = new bootstrap.Modal(document.getElementById('addResourceModal'));
    modal.show();
}

function searchResource() {
    const searchTerm = document.getElementById('resourceSearch').value;
    const resourceType = document.getElementById('resourceType').value;
    
    if (!searchTerm || !resourceType) {
        alert('Vă rugăm completați toate câmpurile!');
        return;
    }
    
    fetch(`pages/norme/getData.php?category=${resourceType}&search=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            if (!response.ok) throw new Error('Eroare rețea: ' + response.status);
            return response.json();
        })
        .then(data => {
            const resourceList = document.getElementById('resourceList');
            resourceList.innerHTML = '';
            
            if (data.results && data.results.length > 0) {
                data.results.forEach(resource => {
                    resourceList.innerHTML += `
                        <button type="button" class="list-group-item list-group-item-action" 
                                onclick="selectResource('${resource.id}', '${resource.text}')">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${resource.id}</h6>
                            </div>
                            <p class="mb-1">${resource.text}</p>
                        </button>
                    `;
                });
                document.getElementById('searchResults').style.display = 'block';
            } else {
                resourceList.innerHTML = '<div class="list-group-item">Nu s-au găsit resurse</div>';
                document.getElementById('searchResults').style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Eroare:', error);
            alert(`A apărut o eroare: ${error.message}`);
        });
}

function selectResource(cod, denumire) {
    selectedResource = { cod, denumire };
    document.getElementById('resourceSearch').value = `${cod} - ${denumire}`;
    document.getElementById('searchResults').style.display = 'none';
}

function addNewResource() {
    if (!selectedResource) {
        alert('Vă rugăm selectați o resursă!');
        return;
    }
    
    const position = document.getElementById('resourcePosition').value;
    const quantity = document.getElementById('resourceQuantity').value;
    const resourceType = document.getElementById('resourceType').value;
    
    if (!position || !quantity) {
        alert('Vă rugăm completați toate câmpurile!');
        return;
    }
    
    const data = {
        codVarianta: currentCodVarianta,
        codItem: selectedResource.cod,
        pozitie: position,
        cantitate: quantity,
        tip: resourceType
    };
    
    fetch('pages/norme/addResource.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) throw new Error('Eroare rețea: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Resursa a fost adăugată cu succes!');
            bootstrap.Modal.getInstance(document.getElementById('addResourceModal')).hide();
            showData(resourceType); // Reîncarcă datele pentru a afișa noua resursă
            
            // Resetează formularul
            document.getElementById('resourceSearch').value = '';
            document.getElementById('resourcePosition').value = '';
            document.getElementById('resourceQuantity').value = '';
            selectedResource = null;
        } else {
            throw new Error(data.error || 'Eroare la adăugarea resursei');
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
        alert(`A apărut o eroare: ${error.message}`);
    });
}

function showData(category) {
    currentCategory = category;
    
    fetch(`pages/norme/getData.php?category=${category}&codVarianta=${currentCodVarianta}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Eroare rețea: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Date primite de la server:', data);
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';
            
            data.forEach(row => {
                console.log('Procesare rând:', row);
                tableBody.innerHTML += `
                    <tr data-cod-varianta="${row.codVarianta || ''}" 
                        data-cod-item="${row.codItem || ''}">
                        <td>${row.codVarianta || ''}</td>
                        <td>${row.codItem || ''}</td>
                        <td>${row.denumire || ''}</td>
                        <td>${row.pozitie || ''}</td>
                        <td>
                            <input type="number" class="form-control" 
                                   value="${row.cantitate || 0}" 
                                   onchange="updateCantitate(this)">
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="deleteResource('${row.codVarianta}', '${row.codItem}')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            document.getElementById('dataTable').style.display = 'block';
        })
        .catch(error => {
            console.error('Eroare:', error);
            alert(`A apărut o eroare: ${error.message}`);
        });
}

function deleteResource(codVarianta, codItem) {
    if (!confirm('Sigur doriți să ștergeți această resursă?')) {
        return;
    }
    
    fetch('pages/norme/deleteResource.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            codVarianta,
            codItem,
            tip: currentCategory
        })
    })
    .then(response => {
        if (!response.ok) throw new Error('Eroare rețea: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Resursa a fost ștearsă cu succes!');
            showData(currentCategory); // Reîncarcă datele
        } else {
            throw new Error(data.error || 'Eroare la ștergerea resursei');
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
        alert(`A apărut o eroare: ${error.message}`);
    });
}

function saveChanges() {
    const inputs = document.querySelectorAll('#tableBody input');
    const data = Array.from(inputs).map(input => {
        const row = input.closest('tr');
        return {
            codVarianta: row.cells[0].textContent.trim(),
            codItem: row.cells[1].textContent.trim(),
            cantitate: input.value
        };
    });

    console.log('Date pentru salvare:', {
        category: currentCategory,
        data: data
    });
    
    fetch('pages/norme/saveChanges.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            category: currentCategory,
            data: data
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Eroare rețea: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Răspuns server:', data);
        if (data.success) {
            alert(data.message || 'Modificările au fost salvate cu succes!');
        } else {
            throw new Error(data.error || 'A apărut o eroare la salvarea modificărilor!');
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
        alert(`A apărut o eroare la salvarea modificărilor: ${error.message}`);
    });
}

function updateCantitate(input) {
    const value = parseFloat(input.value);
    if (isNaN(value) || value < 0) {
        input.value = 0;
    }
} 
<?php
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ștergere Norme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #37517e;
            --success-color: #2ecc71;
            --warning-color: #f1c40f;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-bg: #2c3e50;
        }
        .search-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .norma-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .norma-card:hover {
            transform: translateY(-2px);
        }
        .delete-btn {
            opacity: 0;
            transition: opacity 0.2s;
        }
        .norma-card:hover .delete-btn {
            opacity: 1;
        }
        .alert {
            display: none;
            margin-top: 20px;
        }
        .btn-primary {
            background: #37517e;
            border: none;
            border-radius: 8px;
            color: #fff;
        }
        .btn-primary:hover {
            background: #22375a;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-4 text-primary">
                            <i class="bi bi-trash me-2"></i>Ștergere Norme
                        </h2>

                        <!-- Search Container -->
                        <div class="search-container mb-4">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" id="searchInput" class="form-control" 
                                               placeholder="Introduceți simbolul sau denumirea normei...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-primary w-100" onclick="searchNorma()">
                                        <i class="bi bi-search me-2"></i>Caută
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Results Container -->
                        <div id="resultsContainer" class="d-none">
                            <h5 class="mb-3">Rezultate căutare</h5>
                            <div id="resultsList" class="row g-3">
                                <!-- Results will be inserted here -->
                            </div>
                        </div>

                        <!-- Alert Messages -->
                        <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>
                            <span id="successMessage"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            <span id="errorMessage"></span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmare ștergere</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Sunteți sigur că doriți să ștergeți norma <strong id="normaToDelete"></strong>?</p>
                    <p class="text-danger mb-0">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Această acțiune este ireversibilă!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                        <i class="bi bi-trash me-2"></i>Șterge
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedNorma = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        function searchNorma() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            
            if (!searchTerm) {
                showError('Vă rugăm introduceți un termen de căutare!');
                return;
            }

            fetch(`pages/norme/search.php?symbol=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResults(data.variants);
                    } else {
                        showError(data.error || 'Nu s-au găsit norme!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('A apărut o eroare la căutare!');
                });
        }

        function displayResults(variants) {
            const container = document.getElementById('resultsContainer');
            const list = document.getElementById('resultsList');
            
            if (!variants || variants.length === 0) {
                showError('Nu s-au găsit norme!');
                container.classList.add('d-none');
                return;
            }

            list.innerHTML = variants.map(variant => `
                <div class="col-md-6">
                    <div class="card norma-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title mb-1">${variant.simbol}</h5>
                                    <p class="card-text text-muted mb-0">${variant.Denumire || 'Fără denumire'}</p>
                                </div>
                                <button class="btn btn-outline-danger btn-sm delete-btn" 
                                        onclick="showDeleteModal('${variant.codVarianta}', '${variant.simbol}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');

            container.classList.remove('d-none');
        }

        function showDeleteModal(codVarianta, simbol) {
            selectedNorma = { codVarianta, simbol };
            document.getElementById('normaToDelete').textContent = simbol;
            deleteModal.show();
        }

        function confirmDelete() {
            if (!selectedNorma) return;

            fetch('pages/norme/deleteNorma.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    codVarianta: selectedNorma.codVarianta
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Norma a fost ștearsă cu succes!');
                    deleteModal.hide();
                    searchNorma(); // Refresh results
                } else {
                    showError(data.error || 'Eroare la ștergerea normei!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('A apărut o eroare la ștergere!');
            });
        }

        function showSuccess(message) {
            const alert = document.getElementById('successAlert');
            document.getElementById('successMessage').textContent = message;
            alert.style.display = 'block';
            setTimeout(() => alert.style.display = 'none', 5000);
        }

        function showError(message) {
            const alert = document.getElementById('errorAlert');
            document.getElementById('errorMessage').textContent = message;
            alert.style.display = 'block';
            setTimeout(() => alert.style.display = 'none', 5000);
        }

        // Allow search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchNorma();
            }
        });
    </script>
</body>
</html> 
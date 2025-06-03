<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitorizare Devize</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Monitorizare Devize</h2>
            <button class="btn btn-success" onclick="showAddResourceModal()">
                <i class="bi bi-plus-circle"></i> Adaugă Resursă Nouă
            </button>
        </div>
        
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" id="searchSymbol" class="form-control" placeholder="Introduceți simbolul...">
                    <button class="btn btn-primary" onclick="searchNorma()">Caută</button>
                </div>
            </div>
        </div>

        <!-- Butoane pentru categorii -->
        <div id="categoryButtons" style="display: none;" class="mb-4">
            <button class="btn btn-success me-2" onclick="showData('material')">Material</button>
            <button class="btn btn-info me-2" onclick="showData('manopera')">Manoperă</button>
            <button class="btn btn-warning" onclick="showData('utilaj')">Utilaj</button>
        </div>

        <!-- Tabel pentru date -->
        <div id="dataTable" style="display: none;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Cod Variantă</th>
                        <th>Cod Material/Manoperă/Utilaj</th>
                        <th>Denumire</th>
                        <th>Poziție</th>
                        <th>Cantitate</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
            <button class="btn btn-primary" onclick="saveChanges()">Salvează Modificările</button>
        </div>
    </div>

    <!-- Modal pentru adăugare resursă nouă -->
    <div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addResourceModalLabel">Adaugă Resursă Nouă</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addResourceForm">
                        <div class="mb-3">
                            <label for="resourceType" class="form-label">Tip Resursă</label>
                            <select class="form-select" id="resourceType" required>
                                <option value="">Selectează tipul...</option>
                                <option value="material">Material</option>
                                <option value="manopera">Manoperă</option>
                                <option value="utilaj">Utilaj</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="resourceSearch" class="form-label">Caută Resursă</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="resourceSearch" placeholder="Introduceți codul sau denumirea...">
                                <button class="btn btn-outline-secondary" type="button" onclick="searchResource()">Caută</button>
                            </div>
                        </div>
                        <div id="searchResults" class="mb-3" style="display: none;">
                            <label class="form-label">Rezultate căutare</label>
                            <div class="list-group" id="resourceList">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="resourcePosition" class="form-label">Poziție</label>
                            <input type="text" class="form-control" id="resourcePosition" required>
                        </div>
                        <div class="mb-3">
                            <label for="resourceQuantity" class="form-label">Cantitate</label>
                            <input type="number" class="form-control" id="resourceQuantity" step="0.01" min="0" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="button" class="btn btn-primary" onclick="addNewResource()">Adaugă</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/norme/script.js"></script>
</body>
</html> 
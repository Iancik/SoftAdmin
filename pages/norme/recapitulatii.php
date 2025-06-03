<?php
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Recapitulări</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .recapitulari-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .table th {
            background-color: #f8f9fa;
        }
        .editable:hover {
            background-color: #e9ecef;
            cursor: pointer;
        }
        .alert {
            display: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-4 text-primary">
                            <i class="bi bi-table me-2"></i>Gestionare Recapitulări
                        </h2>

                        <div class="recapitulari-container">
                            <div class="table-responsive">
                                <table id="recapitulariTable" class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Cod Indice</th>
                                            <th>Denumire Indice</th>
                                            <th>Cod Recapitulatie</th>
                                            <th>Denumire Recapitulatie</th>
                                            <th>Valoare</th>
                                            <th>Acțiuni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Datele vor fi încărcate dinamic -->
                                    </tbody>
                                </table>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editare Valoare</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="editCodIndice">
                        <input type="hidden" id="editCodRecapitulatie">
                        <div class="mb-3">
                            <label class="form-label">Denumire Recapitulatie</label>
                            <input type="text" class="form-control" id="editDenumire" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valoare</label>
                            <input type="number" class="form-control" id="editValoare" step="0.01" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="button" class="btn btn-primary" onclick="saveEdit()">Salvează</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let dataTable;
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));

        document.addEventListener('DOMContentLoaded', function() {
            loadRecapitulari();
        });

        function loadRecapitulari() {
            fetch('pages/norme/getRecapitulari.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        initializeTable(data.recapitulari);
                    } else {
                        showError(data.error || 'Eroare la încărcarea recapitulărilor!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Eroare la încărcarea recapitulărilor!');
                });
        }

        function initializeTable(data) {
            if (dataTable) {
                dataTable.destroy();
            }

            dataTable = $('#recapitulariTable').DataTable({
                data: data,
                columns: [
                    { data: 'CodIndice' },
                    { data: 'DenumireIndice' },
                    { data: 'CodRecapitulatie' },
                    { data: 'DenumireRecapitulatie' },
                    { 
                        data: 'Valoare',
                        render: function(data, type, row) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <button class="btn btn-sm btn-primary" onclick="editRecapitulatie('${row.CodIndice}', '${row.CodRecapitulatie}', '${row.DenumireRecapitulatie}', ${row.Valoare})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            `;
                        }
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ro.json'
                },
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Toate"]]
            });
        }

        function editRecapitulatie(codIndice, codRecapitulatie, denumire, valoare) {
            document.getElementById('editCodIndice').value = codIndice;
            document.getElementById('editCodRecapitulatie').value = codRecapitulatie;
            document.getElementById('editDenumire').value = denumire;
            document.getElementById('editValoare').value = valoare;
            editModal.show();
        }

        function saveEdit() {
            const codIndice = document.getElementById('editCodIndice').value;
            const codRecapitulatie = document.getElementById('editCodRecapitulatie').value;
            const valoare = document.getElementById('editValoare').value;

            fetch('pages/norme/updateRecapitulare.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    codIndice: codIndice,
                    codRecapitulatie: codRecapitulatie,
                    valoare: valoare
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Valoarea a fost actualizată cu succes!');
                    editModal.hide();
                    loadRecapitulari();
                } else {
                    showError(data.error || 'Eroare la actualizarea valorii!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Eroare la actualizarea valorii!');
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
    </script>
</body>
</html> 
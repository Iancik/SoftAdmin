<?php
require_once __DIR__ . '/../../config.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Curs Valutar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .curs-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .curs-history {
            max-height: 400px;
            overflow-y: auto;
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
            <div class="col-12 col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 mb-4 text-primary">
                            <i class="bi bi-currency-exchange me-2"></i>Gestionare Curs Valutar
                        </h2>

                        <!-- Formular Adăugare Curs -->
                        <div class="curs-container mb-4">
                            <form id="cursForm" onsubmit="return addCurs(event)">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Cod Curs</label>
                                        <input type="text" class="form-control" id="codCurs" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Curs Euro</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="cursEuro" 
                                                   step="0.0001" min="0" required>
                                            <span class="input-group-text">EUR</span>
                                            <button type="button" class="btn btn-outline-secondary" onclick="getRealTimeRate()">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted" id="lastUpdate"></small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Data</label>
                                        <input type="date" class="form-control" id="dataCurs" readonly>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Adaugă Curs
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Istoric Cursuri -->
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Istoric Cursuri</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive curs-history">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Cod Curs</th>
                                                <th>Curs Euro</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cursHistory">
                                            <!-- Istoricul va fi încărcat aici -->
                                        </tbody>
                                    </table>
                                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Setează data curentă la încărcarea paginii
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dataCurs').value = today;
            loadLastCodCurs();
            loadCursHistory();
            getRealTimeRate(); // Get initial rate
        });

        function loadLastCodCurs() {
            fetch('pages/norme/getLastCodCurs.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const nextCod = parseInt(data.lastCod) + 1;
                        document.getElementById('codCurs').value = nextCod.toString().padStart(4, '0');
                    } else {
                        document.getElementById('codCurs').value = '0001';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Eroare la încărcarea codului curs!');
                });
        }

        function loadCursHistory() {
            fetch('pages/norme/getCursHistory.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('cursHistory');
                        console.log('Total entries:', data.count); // Debug information
                        tbody.innerHTML = data.cursuri.map(curs => `
                            <tr>
                                <td>${curs.CodCurs}</td>
                                <td>${parseFloat(curs.Curs).toFixed(4)} EUR</td>
                                <td>${curs.Data}</td>
                            </tr>
                        `).join('');
                    } else {
                        showError(data.error || 'Eroare la încărcarea istoricului!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Eroare la încărcarea istoricului!');
                });
        }

        function addCurs(event) {
            event.preventDefault();

            const data = {
                codCurs: document.getElementById('codCurs').value,
                cursEuro: document.getElementById('cursEuro').value,
                dataCurs: document.getElementById('dataCurs').value
            };

            fetch('pages/norme/addCurs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Cursul a fost adăugat cu succes!');
                    document.getElementById('cursForm').reset();
                    document.getElementById('dataCurs').value = new Date().toISOString().split('T')[0];
                    loadLastCodCurs();
                    loadCursHistory();
                } else {
                    showError(data.error || 'Eroare la adăugarea cursului!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('A apărut o eroare la adăugarea cursului!');
            });

            return false;
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

        function getRealTimeRate() {
            fetch('pages/norme/getRealTimeRate.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('cursEuro').value = data.rate.toFixed(4);
                        document.getElementById('lastUpdate').textContent = 
                            `Ultima actualizare: ${new Date().toLocaleTimeString()} (Sursa: ${data.source})`;
                        showSuccess('Cursul a fost actualizat cu succes!');
                    } else {
                        showError(data.error || 'Eroare la obținerea cursului în timp real!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('Eroare la obținerea cursului în timp real!');
                });
        }

        // Auto-update rate every 5 minutes
        setInterval(getRealTimeRate, 300000);
    </script>
</body>
</html> 
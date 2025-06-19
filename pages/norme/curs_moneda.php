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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--accent-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .curs-container {
            background: var(--light-bg);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .curs-history {
            max-height: 400px;
            overflow-y: auto;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 1rem;
        }

        .alert-success {
            background-color: var(--success-color);
            color: white;
        }

        .alert-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #217dbb;
            border-color: #217dbb;
            color: #fff;
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-outline-secondary:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.15);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--light-bg);
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--accent-color);
            padding: 1rem;
            text-align: left;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .input-group-text {
            background-color: var(--light-bg);
            border-color: #dee2e6;
            color: var(--primary-color);
        }

        .small-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h2>
                <i class="fas fa-exchange-alt me-2"></i>
                Gestionare Curs Valutar
            </h2>
        </div>

        <!-- Formular Adăugare Curs -->
        <div class="curs-container">
            <form id="cursForm" onsubmit="return addCurs(event)">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-hashtag me-2"></i>
                            Cod Curs
                        </label>
                        <input type="text" class="form-control" id="codCurs" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-euro-sign me-2"></i>
                            Curs Euro
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="cursEuro" 
                                   step="0.0001" min="0" required>
                            <span class="input-group-text">EUR</span>
                            <button type="button" class="btn btn-outline-secondary" onclick="getRealTimeRate()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                        <small class="text-muted" id="lastUpdate"></small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="fas fa-calendar me-2"></i>
                            Data
                        </label>
                        <input type="date" class="form-control" id="dataCurs" readonly>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>
                        Adaugă Curs
                    </button>
                </div>
            </form>
        </div>

        <!-- Istoric Cursuri -->
        <div class="table-container">
            <div class="table-responsive curs-history">
                <table class="table">
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

        <!-- Alert Messages -->
        <div id="successAlert" class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
            <i class="fas fa-check-circle me-2"></i>
            <span id="successMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
            <i class="fas fa-exclamation-circle me-2"></i>
            <span id="errorMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            const msgSpan = document.getElementById('errorMessage');
            if (alert && msgSpan) {
                msgSpan.textContent = message;
                alert.style.display = 'block';
                if (alert.hideTimeout) clearTimeout(alert.hideTimeout);
                alert.hideTimeout = setTimeout(() => alert.style.display = 'none', 5000);
            } else {
                window.alert('Eroare: ' + message);
            }
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
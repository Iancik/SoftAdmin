<?php
require_once __DIR__ . '/../../config/database.php';

// Get the next variant code
try {
    // Get the highest codVarianta from articolevariante table
    $query = "SELECT MAX(codVarianta) as max_cod FROM articolevariante";
    
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $nextCod = 1; // Default value if no records exist
    if ($result && $result['max_cod']) {
        $nextCod = intval($result['max_cod']) + 1;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $nextCod = 1; // Default to 1 if there's an error
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adăugare Norme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
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
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary, .btn-edit {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: #fff;
        }
        .btn-primary:hover, .btn-edit:hover {
            background-color: #217dbb;
            border-color: #217dbb;
            color: #fff;
        }
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
            color: white;
        }
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .alert-success {
            background-color: var(--success-color);
            color: white;
        }
        .alert-danger {
            background-color: var(--danger-color);
            color: white;
        }
        .form-label {
            font-weight: 500;
            color: var(--primary-color);
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
        .description-cell {
            max-width: 500px;
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Adăugare Normă Nouă</h2>
        <form id="normaForm" class="mt-4">
            <div class="row">
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="codVarianta" class="form-label">Cod Variantă</label>
                        <input type="text" class="form-control" id="codVarianta" name="codVarianta" value="<?php echo $nextCod; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="simbol" class="form-label">Simbol Normă</label>
                        <input type="text" class="form-control" id="simbol" name="simbol" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="denumire" class="form-label">Denumire Normă</label>
                        <input type="text" class="form-control" id="denumire" name="denumire" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="unitate" class="form-label">Unitatea de Măsură</label>
                        <input type="text" class="form-control" id="unitate" name="unitate" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="codArticol" class="form-label">Cod Articol</label>
                        <select class="form-select" id="codArticol" name="codArticol" style="width: 100%" required>
                            <option value="">Căutați un articol...</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="codCapitol" class="form-label">Capitol</label>
                        <select class="form-select" id="codCapitol" name="codCapitol" style="width: 100%" required>
                            <option value="">Căutați un capitol...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="codIndicator" class="form-label">Indicator</label>
                        <select class="form-select" id="codIndicator" name="codIndicator" style="width: 100%" required>
                            <option value="">Căutați un indicator...</option>
                        </select>
                        <!-- La selectarea indicatorului, capitolul asociat va fi completat automat -->
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h4>Resurse</h4>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="tipResursa">
                            <option value="manopera">Manoperă</option>
                            <option value="material">Material</option>
                            <option value="utilaj">Utilaj</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="resursaSelect" style="width: 100%">
                            <option value="">Căutați o resursă...</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control" id="pozitie" placeholder="Poz.">
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control" id="cantitate" placeholder="Cant.">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-primary" id="adaugaResursa">+</button>
                    </div>
                </div>

                <div id="resurseLista" class="mt-3">
                    <!-- Lista resurselor adăugate va apărea aici -->
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Salvează Norma</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/adaugare_norme.js"></script>
</body>
</html> 

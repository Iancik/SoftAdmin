<?php
require_once 'config.php';

// Procesare formular manoperă
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pret_manopera'])) {
    try {
        $pdo->beginTransaction();
        
        $pret = $_POST['pret_manopera'];
        $stmt = $pdo->prepare("UPDATE manopera SET pret = ?");
        $stmt->execute([$pret]);
        
        $pdo->commit();
        $success_message = "Prețul manoperei a fost actualizat cu succes pentru toate înregistrările!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "A apărut o eroare la actualizarea prețului manoperei: " . $e->getMessage();
    }
}

// Obținere preț curent manoperă
$stmt = $pdo->query("SELECT pret FROM manopera LIMIT 1");
$current_price = $stmt->fetch(PDO::FETCH_ASSOC)['pret'];
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare Preț Manoperă</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px;
            font-size: 1.2em;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(75, 108, 183, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1.1em;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #182848 0%, #4b6cb7 100%);
        }
        .alert {
            border-radius: 8px;
        }
        .price-input-group {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><i class="fas fa-tools me-2"></i>Editare Preț Manoperă</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="price-input-group">
                                <label for="pret_manopera" class="form-label">Preț pentru toate înregistrările:</label>
                                <div class="input-group">
                                    <input type="number" 
                                           step="0.01" 
                                           class="form-control" 
                                           id="pret_manopera"
                                           name="pret_manopera" 
                                           value="<?php echo htmlspecialchars($current_price); ?>"
                                           required>
                                    <span class="input-group-text">LEI</span>
                                </div>
                                <div class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Acest preț va fi aplicat pentru toate înregistrările din tabelul manoperă.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvează Prețul
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
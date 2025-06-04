<?php
require_once 'config.php';

$search_results = [];
$start_date = '';
$end_date = '';
$selected_offers = [];
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Procesare ștergere
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    try {
        $pdo->beginTransaction();
        
        if (isset($_POST['select_all']) && $_POST['select_all'] === '1') {
            // Șterge toate ofertele din intervalul de date
            $stmt = $pdo->prepare("DELETE FROM materialoferta WHERE Data BETWEEN ? AND ?");
            $stmt->execute([$_POST['start_date'], $_POST['end_date']]);
            $success_message = "Toate ofertele din intervalul selectat au fost șterse cu succes!";
        } else if (isset($_POST['selected_offers']) && is_array($_POST['selected_offers'])) {
            // Șterge doar ofertele selectate
            $placeholders = str_repeat('?,', count($_POST['selected_offers']) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM materialoferta WHERE codMaterialOferta IN ($placeholders)");
            $stmt->execute($_POST['selected_offers']);
            $success_message = "Ofertele selectate au fost șterse cu succes!";
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "A apărut o eroare la ștergerea ofertelor: " . $e->getMessage();
    }
}

// Procesare căutare după interval de date
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    
    try {
        // Query pentru numărul total de rezultate
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM materialoferta WHERE Data BETWEEN ? AND ?");
        $stmt->execute([$start_date, $end_date]);
        $total_records = $stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);

        // Query pentru rezultatele paginate
        $stmt = $pdo->prepare("
            SELECT * FROM materialoferta 
            WHERE Data BETWEEN ? AND ? 
            ORDER BY Data DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $start_date, PDO::PARAM_STR);
        $stmt->bindValue(2, $end_date, PDO::PARAM_STR);
        $stmt->bindValue(3, $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(4, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_message = "Eroare la căutare: " . $e->getMessage();
    }
}

// Funcție pentru generarea link-urilor de paginare
function generatePaginationLinks($current_page, $total_pages, $start_date, $end_date) {
    $links = [];
    $date_params = "&start_date=" . urlencode($start_date) . "&end_date=" . urlencode($end_date);
    
    // Link către pagina anterioară
    if ($current_page > 1) {
        $links[] = '<li class="page-item"><a class="page-link" href="?action=furnizori_stergere&page=' . ($current_page - 1) . $date_params . '">Anterior</a></li>';
    }
    
    // Link-uri pentru pagini
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        $active = $i == $current_page ? ' active' : '';
        $links[] = '<li class="page-item' . $active . '"><a class="page-link" href="?action=furnizori_stergere&page=' . $i . $date_params . '">' . $i . '</a></li>';
    }
    
    // Link către pagina următoare
    if ($current_page < $total_pages) {
        $links[] = '<li class="page-item"><a class="page-link" href="?action=furnizori_stergere&page=' . ($current_page + 1) . $date_params . '">Următor</a></li>';
    }
    
    return implode('', $links);
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ștergere Oferte Materiale</title>
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
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-size: 1.1em;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #a71d2a 0%, #dc3545 100%);
        }
        .alert {
            border-radius: 8px;
        }
        .search-results {
            margin-top: 20px;
            max-height: 600px;
            overflow-y: auto;
        }
        .offer-item {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: white;
            transition: all 0.3s ease;
        }
        .offer-item:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .offer-date {
            color: #6c757d;
            font-size: 0.9em;
        }
        .offer-price {
            font-size: 1.1em;
            color: #28a745;
            font-weight: bold;
        }
        .search-container {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            padding: 20px 0;
            z-index: 100;
        }
        .form-check-input:checked {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .select-all-container {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><i class="fas fa-trash-alt me-2"></i>Ștergere Oferte Materiale</h2>
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

                        <!-- Formular căutare după interval de date -->
                        <div class="search-container">
                            <form method="GET" action="index.php" class="mb-4">
                                <input type="hidden" name="action" value="furnizori_stergere">
                                <div class="row">
                                    <div class="col-md-5">
                                        <label class="form-label">Data început:</label>
                                        <input type="date" 
                                               class="form-control" 
                                               name="start_date"
                                               value="<?php echo $start_date; ?>"
                                               required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">Data sfârșit:</label>
                                        <input type="date" 
                                               class="form-control" 
                                               name="end_date"
                                               value="<?php echo $end_date; ?>"
                                               required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button class="btn btn-primary w-100" type="submit">
                                            <i class="fas fa-search"></i> Caută
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Rezultate căutare -->
                        <?php if (!empty($search_results)): ?>
                            <form method="POST" action="?action=furnizori_stergere">
                                <div class="select-all-container">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll" name="select_all" value="1">
                                        <label class="form-check-label" for="selectAll">
                                            Selectează toate ofertele din intervalul de date
                                        </label>
                                    </div>
                                </div>

                                <div class="search-results">
                                    <?php foreach ($search_results as $offer): ?>
                                        <div class="offer-item">
                                            <div class="form-check">
                                                <input class="form-check-input offer-checkbox" 
                                                       type="checkbox" 
                                                       name="selected_offers[]" 
                                                       value="<?php echo $offer['codMaterialOferta']; ?>"
                                                       id="offer_<?php echo $offer['codMaterialOferta']; ?>">
                                                <label class="form-check-label w-100" for="offer_<?php echo $offer['codMaterialOferta']; ?>">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($offer['Denumire']); ?></strong>
                                                            <div class="offer-date">
                                                                <span>Cod: <?php echo htmlspecialchars($offer['codEx']); ?></span>
                                                                <span class="ms-3">UM: <?php echo htmlspecialchars($offer['UM']); ?></span>
                                                                <span class="ms-3">Furnizor: <?php echo htmlspecialchars($offer['codPartener']); ?></span>
                                                                <span class="ms-3">Data: <?php echo date('d.m.Y', strtotime($offer['Data'])); ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="offer-price">
                                                            <?php echo number_format($offer['Pret'], 2); ?> <?php echo htmlspecialchars($offer['Moneda']); ?>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Paginare -->
                                <?php if ($total_pages > 1): ?>
                                    <nav aria-label="Navigare pagină" class="mt-4">
                                        <ul class="pagination justify-content-center">
                                            <?php echo generatePaginationLinks($page, $total_pages, $start_date, $end_date); ?>
                                        </ul>
                                    </nav>
                                <?php endif; ?>

                                <div class="d-grid gap-2 mt-4">
                                    <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                                    <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                                    <button type="submit" name="delete_selected" class="btn btn-danger" onclick="return confirm('Sigur doriți să ștergeți ofertele selectate?')">
                                        <i class="fas fa-trash-alt me-2"></i>Șterge Ofertele Selectate
                                    </button>
                                </div>
                            </form>
                        <?php elseif ($start_date && $end_date): ?>
                            <div class="alert alert-info">
                                Nu s-au găsit oferte în intervalul de date selectat.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const offerCheckboxes = document.querySelectorAll('.offer-checkbox');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const isChecked = this.checked;
                    offerCheckboxes.forEach(checkbox => {
                        checkbox.checked = isChecked;
                    });
                });
            }

            // Actualizează checkbox-ul "Selectează toate" când se schimbă selecția individuală
            if (offerCheckboxes.length > 0) {
                offerCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(offerCheckboxes).every(cb => cb.checked);
                        if (selectAllCheckbox) {
                            selectAllCheckbox.checked = allChecked;
                        }
                    });
                });
            }
        });
    </script>
</body>
</html> 
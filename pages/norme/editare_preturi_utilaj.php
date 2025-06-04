<?php
require_once 'config.php';

$search_results = [];
$search_term = '';
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Procesare căutare
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    $search = '%' . $search_term . '%';
    try {
        // Query pentru numărul total de rezultate
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilaj WHERE denumire LIKE ?");
        $stmt->execute([$search]);
        $total_records = $stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);

        // Query pentru rezultatele paginate
        $stmt = $pdo->prepare("SELECT codUtilaj, denumire, pret, UM FROM utilaj WHERE denumire LIKE ? ORDER BY denumire LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $search, PDO::PARAM_STR);
        $stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_message = "Eroare la căutare: " . $e->getMessage();
    }
} else {
    // Afișează toate utilajele dacă nu există termen de căutare
    try {
        // Query pentru numărul total de rezultate
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilaj");
        $stmt->execute();
        $total_records = $stmt->fetchColumn();
        $total_pages = ceil($total_records / $records_per_page);

        // Query pentru rezultatele paginate
        $stmt = $pdo->prepare("SELECT codUtilaj, denumire, pret, UM FROM utilaj ORDER BY denumire LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $records_per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $error_message = "Eroare la încărcarea utilajelor: " . $e->getMessage();
    }
}

// Procesare formular utilaj
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pret_utilaj'])) {
    try {
        $pdo->beginTransaction();
        
        $pret = $_POST['pret_utilaj'];
        $codUtilaj = $_POST['cod_utilaj'];
        $stmt = $pdo->prepare("UPDATE utilaj SET pret = ? WHERE codUtilaj = ?");
        $stmt->execute([$pret, $codUtilaj]);
        
        $pdo->commit();
        $success_message = "Prețul utilajului a fost actualizat cu succes!";
        
        // Reîncarcă lista după actualizare
        if (isset($_POST['search'])) {
            $search = '%' . $_POST['search'] . '%';
            $stmt = $pdo->prepare("SELECT codUtilaj, denumire, pret, UM FROM utilaj WHERE denumire LIKE ? ORDER BY denumire LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $search, PDO::PARAM_STR);
            $stmt->bindValue(2, $records_per_page, PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        } else {
            $stmt = $pdo->prepare("SELECT codUtilaj, denumire, pret, UM FROM utilaj ORDER BY denumire LIMIT ? OFFSET ?");
            $stmt->bindValue(1, $records_per_page, PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "A apărut o eroare la actualizarea prețului utilajului: " . $e->getMessage();
    }
}

// Funcție pentru generarea link-urilor de paginare
function generatePaginationLinks($current_page, $total_pages, $search_term = '') {
    $links = [];
    $search_param = $search_term ? '&search=' . urlencode($search_term) : '';
    
    // Link către pagina anterioară
    if ($current_page > 1) {
        $links[] = '<li class="page-item"><a class="page-link" href="?action=editare_preturi_utilaj&page=' . ($current_page - 1) . $search_param . '">Anterior</a></li>';
    }
    
    // Link-uri pentru pagini
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        $active = $i == $current_page ? ' active' : '';
        $links[] = '<li class="page-item' . $active . '"><a class="page-link" href="?action=editare_preturi_utilaj&page=' . $i . $search_param . '">' . $i . '</a></li>';
    }
    
    // Link către pagina următoare
    if ($current_page < $total_pages) {
        $links[] = '<li class="page-item"><a class="page-link" href="?action=editare_preturi_utilaj&page=' . ($current_page + 1) . $search_param . '">Următor</a></li>';
    }
    
    return implode('', $links);
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editare Preț Utilaj</title>
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
        .search-results {
            margin-top: 20px;
            max-height: 600px;
            overflow-y: auto;
        }
        .search-item {
            cursor: pointer;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: white;
            transition: all 0.3s ease;
        }
        .search-item:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .current-price {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
        }
        .price-change {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .price-change i {
            font-size: 1.5em;
            color: #6c757d;
        }
        .utilaj-code {
            color: #6c757d;
            font-size: 0.9em;
        }
        .utilaj-um {
            color: #6c757d;
            font-size: 0.9em;
            margin-left: 10px;
        }
        .search-container {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            padding: 20px 0;
            z-index: 100;
        }
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        .pagination .page-link {
            color: #4b6cb7;
            border: 1px solid #dee2e6;
            padding: 8px 16px;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            border-color: #4b6cb7;
        }
        .pagination .page-link:hover {
            background-color: #f8f9fa;
            color: #182848;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0"><i class="fas fa-tools me-2"></i>Editare Preț Utilaj</h2>
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

                        <!-- Formular căutare -->
                        <div class="search-container">
                            <form method="GET" action="?action=editare_preturi_utilaj" class="mb-4">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search"
                                           value="<?php echo htmlspecialchars($search_term); ?>"
                                           placeholder="Introduceți denumirea utilajului..."
                                           required>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Caută
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Rezultate căutare -->
                        <?php if (!empty($search_results)): ?>
                            <div class="search-results">
                                <?php foreach ($search_results as $utilaj): ?>
                                    <div class="search-item" onclick="selectUtilaj('<?php echo htmlspecialchars($utilaj['codUtilaj']); ?>', '<?php echo htmlspecialchars($utilaj['denumire']); ?>', <?php echo $utilaj['pret']; ?>)">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($utilaj['denumire']); ?></strong>
                                                <div>
                                                    <span class="utilaj-code">Cod: <?php echo htmlspecialchars($utilaj['codUtilaj']); ?></span>
                                                    <span class="utilaj-um">UM: <?php echo htmlspecialchars($utilaj['UM']); ?></span>
                                                </div>
                                            </div>
                                            <span class="text-muted"><?php echo number_format($utilaj['pret'], 2); ?> LEI</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Paginare -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Navigare pagină">
                                    <ul class="pagination">
                                        <?php echo generatePaginationLinks($page, $total_pages, $search_term); ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php elseif ($search_term): ?>
                            <div class="alert alert-info">
                                Nu s-au găsit rezultate pentru căutarea "<?php echo htmlspecialchars($search_term); ?>"
                            </div>
                        <?php endif; ?>

                        <!-- Formular editare preț -->
                        <form method="POST" action="?action=editare_preturi_utilaj<?php echo $search_term ? '&search=' . urlencode($search_term) : ''; ?>&page=<?php echo $page; ?>" id="editUtilajForm" style="display: none;">
                            <input type="hidden" name="action" value="editare_preturi_utilaj">
                            <input type="hidden" name="cod_utilaj" id="cod_utilaj">
                            
                            <div class="mb-4">
                                <label class="form-label">Utilaj selectat:</label>
                                <input type="text" class="form-control" id="utilaj_name" readonly>
                            </div>

                            <div class="price-change">
                                <div>
                                    <label class="form-label">Preț curent:</label>
                                    <div class="current-price" id="current_price"></div>
                                </div>
                                <i class="fas fa-arrow-right"></i>
                                <div>
                                    <label for="pret_utilaj" class="form-label">Preț nou:</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               step="0.01" 
                                               class="form-control" 
                                               id="pret_utilaj"
                                               name="pret_utilaj" 
                                               required>
                                        <span class="input-group-text">LEI</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvează Prețul Nou
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectUtilaj(codUtilaj, name, price) {
            document.getElementById('cod_utilaj').value = codUtilaj;
            document.getElementById('utilaj_name').value = name;
            document.getElementById('current_price').textContent = parseFloat(price).toFixed(2) + ' LEI';
            document.getElementById('pret_utilaj').value = price;
            document.getElementById('editUtilajForm').style.display = 'block';
            
            // Scroll to the form
            document.getElementById('editUtilajForm').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html> 
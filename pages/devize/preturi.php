<?php
error_log('--- START preturi.php ---');
require_once __DIR__ . '/../../config.php';

// Funcție pentru înlocuirea caracterelor speciale românești
function replaceRomanianChars($text) {
    $replacements = [
        'ă' => '&#259;',
        'Ă' => '&#258;',
        'î' => '&#238;',
        'Î' => '&#206;',
        'â' => '&#226;',
        'Â' => '&#194;',
        'ț' => '&#355;',
        'Ț' => '&#354;',
        'ş' => '&#351;',
        'Ş' => '&#350;'
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

// Conectare la baza de date principală
try {
    $pdo_main = new PDO(
        "mysql:host=" . DB_MAIN_HOST . ";dbname=" . DB_MAIN_NAME . ";charset=utf8mb4",
        DB_MAIN_USER,
        DB_MAIN_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Eroare conexiune baza de date: " . $e->getMessage());
}

// Configurare paginare
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Obține numărul total de înregistrări
$stmt = $pdo_main->query("SELECT COUNT(*) FROM deviz_oferte");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Obține înregistrările pentru pagina curentă
$stmt = $pdo_main->prepare("
    SELECT * FROM deviz_oferte 
    ORDER BY Pozitie ASC, CodArticol DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$items_per_page, $offset]);
$oferte = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prețuri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: var(--accent-color);
            color: white;
            font-weight: 500;
            border: none;
        }

        .table td {
            vertical-align: middle;
            border-color: #dee2e6;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-primary:hover {
            background-color: #2c3e50;
            border-color: #2c3e50;
            transform: translateY(-2px);
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: #000;
        }

        .btn-warning:hover {
            background-color: #f39c12;
            border-color: #f39c12;
            color: #000;
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }

        .pagination {
            margin-top: 2rem;
            justify-content: center;
        }

        .pagination .page-link {
            color: var(--accent-color);
            border: 1px solid #dee2e6;
            padding: 0.5rem 1rem;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
            color: var(--accent-color);
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: var(--success-color);
            color: white;
        }

        .alert-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .badge {
            padding: 0.5em 0.75em;
            font-weight: 500;
        }

        .badge.bg-danger {
            background-color: var(--danger-color) !important;
        }

        .price-cell {
            font-weight: 600;
            color: var(--accent-color);
        }

        .price-reduced {
            color: var(--danger-color);
            text-decoration: line-through;
            font-size: 0.9em;
            margin-right: 0.5rem;
        }

        .special-price {
            background-color: #fff3cd;
            color: #856404;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h2>
                <i class="fas fa-tags me-2"></i>
                Lista Prețurilor
            </h2>
            <a href="?action=preturi_form" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Adaugă Preț Nou
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cod</th>
                        <th>Titlu</th>
                        <th>Tip Articol</th>
                        <th>Preț</th>
                        <th>Preț Redus</th>
                        <th>Frecvență</th>
                        <th>Poziție</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($oferte)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Nu există prețuri înregistrate</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($oferte as $oferta): ?>
                            <tr>
                                <td><?= htmlspecialchars($oferta['CodArticol']) ?></td>
                                <td>
                                    <?= htmlspecialchars($oferta['Titlu']) ?>
                                    <?php if ($oferta['Vizibil'] == 0): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-eye-slash me-1"></i>
                                            Invizibil
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($oferta['TipArticol']) ?></td>
                                <td class="price-cell">
                                    <?= number_format($oferta['Pret'], 2) ?> LEI
                                </td>
                                <td class="price-cell">
                                    <?php if ($oferta['PretRedus'] > 0): ?>
                                        <?= number_format($oferta['PretRedus'], 2) ?> LEI
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($oferta['Frecventa']) ?></td>
                                <td><?= htmlspecialchars($oferta['Pozitie']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=preturi_form&cod=<?= $oferta['CodArticol'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Editează">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?= $oferta['CodArticol'] ?>)"
                                                title="Șterge">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Navigare paginare">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=preturi&page=1">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?action=preturi&page=<?= $page - 1 ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?action=preturi&page=1">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="?action=preturi&page=' . $i . '">' . $i . '</a>';
                        echo '</li>';
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?action=preturi&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=preturi&page=<?= $page + 1 ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?action=preturi&page=<?= $total_pages ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(cod) {
        Swal.fire({
            title: 'Sigur doriți să ștergeți acest preț?',
            text: "Această acțiune nu poate fi anulată!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Da, șterge!',
            cancelButtonText: 'Anulează'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('cod', cod);
                
                fetch('/SoftAdmin/pages/devize/preturi_delete.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Succes!',
                            text: data.message,
                            icon: 'success'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Eroare!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Eroare!',
                        text: 'A apărut o eroare la ștergerea prețului',
                        icon: 'error'
                    });
                });
            }
        });
    }
    </script>
</body>
</html> 
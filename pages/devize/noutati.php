<?php
error_log('--- START noutati.php ---');
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

// Procesare ștergere
if (isset($_POST['delete']) && isset($_POST['cod'])) {
    try {
        $stmt = $pdo_main->prepare("DELETE FROM deviz_noutati WHERE codBuletin = ?");
        $stmt->execute([$_POST['cod']]);
        $_SESSION['success_message'] = "Noutatea a fost ștearsă cu succes";
    } catch (PDOException $e) {
        error_log('Eroare la ștergerea noutății: ' . $e->getMessage());
        $_SESSION['error_message'] = "A apărut o eroare la ștergerea noutății";
    }
    header("Location: /SoftAdmin/index.php?action=noutati");
    exit;
}

// Configurare paginare
$items_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Obține numărul total de înregistrări
$stmt = $pdo_main->query("SELECT COUNT(*) FROM deviz_noutati");
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Obținere noutăți pentru pagina curentă
error_log('Obținere noutăți din baza de date');
$stmt = $pdo_main->prepare("SELECT * FROM deviz_noutati ORDER BY Data DESC LIMIT ? OFFSET ?");
$stmt->execute([$items_per_page, $offset]);
$noutati = $stmt->fetchAll();
error_log('Noutăți obținute: ' . count($noutati));
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Noutăți</title>
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
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            color: white;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
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

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
        }

        .badge.bg-danger {
            background-color: var(--danger-color) !important;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .pagination {
            margin-top: 2rem;
            justify-content: center;
            gap: 0.5rem;
        }

        .page-link {
            border-radius: 6px;
            padding: 0.5rem 1rem;
            color: var(--primary-color);
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
        }

        .page-item.active .page-link {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .page-info {
            text-align: center;
            color: var(--primary-color);
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .description-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h2>
                <i class="fas fa-newspaper me-2"></i>
                Lista Noutăților
            </h2>
            <a href="?action=noutati_form" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>
                Adaugă Noutate Nouă
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

        <div class="table-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Denumire</th>
                            <th>Descriere</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($noutati)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Nu s-au găsit noutăți
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($noutati as $noutate): ?>
                            <tr<?= $noutate['Vizibil'] == 0 ? ' style="background-color:#ffeaea;"' : '' ?>>
                                <td><?= replaceRomanianChars(htmlspecialchars($noutate['Data'])) ?></td>
                                <td><?= replaceRomanianChars(htmlspecialchars($noutate['Denumire'])) ?></td>
                                <td class="description-cell" title="<?= htmlspecialchars($noutate['Descriere']) ?>">
                                    <?= replaceRomanianChars($noutate['Descriere']) ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?action=noutati_form&cod=<?= $noutate['codBuletin'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Editează">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete(<?= $noutate['codBuletin'] ?>)"
                                                title="Șterge">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php if ($noutate['Vizibil'] == 0): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-eye-slash me-1"></i>
                                                Invizibil
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="page-info">
                Afișare <?= ($offset + 1) ?> - <?= min($offset + $items_per_page, $total_items) ?> din <?= $total_items ?> înregistrări
            </div>
            <nav aria-label="Navigare pagină">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=noutati&page=1">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?action=noutati&page=<?= $page - 1 ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?action=noutati&page=1">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="?action=noutati&page=' . $i . '">' . $i . '</a>';
                        echo '</li>';
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?action=noutati&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=noutati&page=<?= $page + 1 ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?action=noutati&page=<?= $total_pages ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="cod" id="deleteCod">
        <input type="hidden" name="delete" value="1">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function confirmDelete(cod) {
        Swal.fire({
            title: 'Sigur doriți să ștergeți această noutate?',
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
                
                fetch('/SoftAdmin/pages/devize/noutati_delete.php', {
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
                        text: 'A apărut o eroare la ștergerea noutății',
                        icon: 'error'
                    });
                });
            }
        });
    }
    </script>
</body>
</html> 
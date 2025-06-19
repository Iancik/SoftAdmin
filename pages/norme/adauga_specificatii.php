<?php
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

// Procesare ștergere
if (isset($_POST['delete']) && isset($_POST['cod'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM specificatii WHERE codSpecificatie = ?");
        $stmt->execute([$_POST['cod']]);
        $_SESSION['success_message'] = "Specificația a fost ștearsă cu succes";
    } catch (PDOException $e) {
        error_log('Eroare la ștergerea specificației: ' . $e->getMessage());
        $_SESSION['error_message'] = "A apărut o eroare la ștergerea specificației";
    }
    header("Location: /SoftAdmin/index.php?action=adauga_specificatii");
    echo '<meta http-equiv="refresh" content="0;url=/SoftAdmin/index.php?action=adauga_specificatii">';
    exit;
}

// Configurare paginare
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Configurare căutare
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE Simbol LIKE ? OR Descriere LIKE ?";
    $params = ["%$search%", "%$search%"];
}

try {
    // Obține numărul total de înregistrări
    $count_sql = "SELECT COUNT(*) FROM specificatii " . $where_clause;
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_items = $stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);

    // Obține datele pentru pagina curentă
    $sql = "SELECT codSpecificatie, Simbol, Descriere FROM specificatii " . $where_clause . " ORDER BY Simbol LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($sql);
    $params[] = $items_per_page;
    $params[] = $offset;
    $stmt->execute($params);
    $specificatii = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Eroare la obținerea specificațiilor: ' . $e->getMessage());
    $_SESSION['error_message'] = "A apărut o eroare la încărcarea specificațiilor";
    $specificatii = [];
    $total_pages = 0;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specificații</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .add-btn {
            background-color: var(--accent-color);
            color: #fff;
            font-size: 1rem;
            padding: 0.55rem 1.3rem;
            border-radius: 8px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.15);
            border: none;
            transition: background 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        .add-btn:hover {
            background-color: #217dbb;
            color: #fff;
            box-shadow: 0 4px 16px rgba(52, 152, 219, 0.25);
        }

        .search-container {
            background: var(--light-bg);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .search-input {
            flex: 1;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
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
        }

        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: #2c3e50;
            border-color: #2c3e50;
            transform: translateY(-2px);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .table-scroll {
            max-height: 600px;
            overflow-y: auto;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
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
            vertical-align: top;
            border-bottom: 1px solid #dee2e6;
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-start;
            align-items: center;
        }

        .btn-icon {
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-edit {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-edit:hover {
            background-color: #217dbb;
            color: white;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            color: white;
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

        .description-cell {
            max-width: 500px;
            overflow-wrap: break-word;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h2>
                <i class="fas fa-list me-2"></i>
                Specificații
            </h2>
            <a href="/SoftAdmin/index.php?action=specificatii_form" class="add-btn">
                <i class="fas fa-plus me-2"></i> Adaugă Specificație Nouă
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

        <div class="search-container">
            <form action="/SoftAdmin/index.php" method="GET" class="search-form">
                <input type="hidden" name="action" value="adauga_specificatii">
                <input type="text" name="search" class="form-control search-input" 
                       placeholder="Caută după simbol sau descriere..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>
                    Caută
                </button>
                <?php if (!empty($search)): ?>
                    <a href="/SoftAdmin/index.php?action=adauga_specificatii" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>
                        Resetează
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <div class="table-scroll">
                <table class="table">
                <thead>
                    <tr>
                            <th style="width: 15%">Simbol</th>
                            <th style="width: 70%">Descriere</th>
                            <th style="width: 15%">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                        <?php if (empty($specificatii)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Nu s-au găsit specificații
                                </td>
                            </tr>
                        <?php else: ?>
                    <?php foreach ($specificatii as $specificatie): ?>
                    <tr>
                                    <td>
                                        <?php echo isset($specificatie['Simbol']) ? htmlspecialchars($specificatie['Simbol']) : ''; ?>
                                    </td>
                                    <td class="description-cell">
                                        <?php echo isset($specificatie['Descriere']) ? $specificatie['Descriere'] : ''; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($specificatie['codSpecificatie'])): ?>
                                            <div class="action-buttons">
                                                <a href="/SoftAdmin/index.php?action=specificatii_form&cod=<?php echo htmlspecialchars($specificatie['codSpecificatie']); ?>" 
                                                   class="btn btn-icon btn-edit" 
                                                   title="Editează">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="/SoftAdmin/index.php?action=adauga_specificatii" method="POST" style="display: inline-block;" class="delete-form">
                                                    <input type="hidden" name="cod" value="<?php echo htmlspecialchars($specificatie['codSpecificatie']); ?>">
                                                    <input type="hidden" name="delete" value="1">
                                                    <button type="button" class="btn btn-icon btn-danger delete-btn" title="Șterge">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                        <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Navigare pagină">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for ($i = $start_page; $i <= $end_page; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                        echo '<a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $i . '</a>';
                        echo '</li>';
                    }

                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                    }
                    ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="/SoftAdmin/index.php?action=adauga_specificatii&page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = btn.closest('form');
        Swal.fire({
                    title: 'Ești sigur?',
                    text: 'Specificația va fi ștearsă definitiv!',
            icon: 'warning',
            showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
            confirmButtonText: 'Da, șterge!',
                    cancelButtonText: 'Renunță',
                    reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                        form.submit();
            }
                });
            });
        });
    });
    </script>
</body>
</html> 
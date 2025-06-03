<?php
// require_once 'auth.php';
// require_login();

// Listele bazelor de date
session_start();
$dbs = [
    'devize' => 'Devize',
    'site_content' => 'Site Content'
];
if (isset($_POST['selected_db']) && isset($dbs[$_POST['selected_db']])) {
    $_SESSION['selected_db'] = $_POST['selected_db'];
}
$selected_db = $_SESSION['selected_db'] ?? 'devize';

// Sidebar actions
$actions = [
    ['icon' => 'bi-journal-bookmark', 'label' => 'Cursuri', 'action' => 'cursuri'],
    ['icon' => 'bi-newspaper', 'label' => 'Noutăți', 'action' => 'noutati'],
    ['icon' => 'bi-cash-coin', 'label' => 'Prețuri', 'action' => 'preturi'],
    ['icon' => 'bi-life-preserver', 'label' => 'Asistență', 'action' => 'asistenta'],
    ['icon' => 'bi-telephone', 'label' => 'Contacte', 'action' => 'contacte'],
];

$norme_md = [
    [
        'label' => 'Editare norme',
        'icon' => 'bi-pencil',
        'children' => [
            ['label' => 'Editare text normă', 'icon' => 'bi-fonts', 'action' => 'editare_text_norma'],
            ['label' => 'Editare resurse', 'icon' => 'bi-tools', 'action' => 'editare_res'],
           
        ]
    ],
    ['label' => 'Adăugare norme', 'icon' => 'bi-plus-circle', 'action' => 'adaugare_norme'],
    ['label' => 'Ștergere norme', 'icon' => 'bi-trash', 'action' => 'stergere_norme'],
    [
        'label' => 'Editare prețuri',
        'icon' => 'bi-currency-exchange',
        'children' => [
            ['label' => 'Material', 'icon' => 'bi-box', 'action' => 'editare_preturi_material'],
            ['label' => 'Manoperă', 'icon' => 'bi-person-workspace', 'action' => 'editare_preturi_manopera'],
            ['label' => 'Utilaj', 'icon' => 'bi-truck', 'action' => 'editare_preturi_utilaj'],
        ]
    ],
    [
        'label' => 'Furnizori',
        'icon' => 'bi-truck-front',
        'children' => [
            ['label' => 'Adăugarea', 'icon' => 'bi-plus-circle', 'action' => 'furnizori_adaugare'],
            ['label' => 'Ștergerea', 'icon' => 'bi-trash', 'action' => 'furnizori_stergere'],
        ]
    ],
    ['label' => 'Recapitulatii', 'icon' => 'bi-list-check', 'action' => 'recapitulatii'],
    ['label' => 'Cursul monedei', 'icon' => 'bi-currency-dollar', 'action' => 'curs_moneda'],
    ['label' => 'Adaugă specificații', 'icon' => 'bi-file-earmark-plus', 'action' => 'adauga_specificatii'],
];

// Sidebar menu items (customize as needed)
$menu = [
    ['icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'active' => true, 'page' => 'dashboard'],
    ['icon' => 'bi-file-earmark-text', 'label' => 'Devize.md', 'actions' => true],
    ['icon' => 'bi-diagram-3', 'label' => 'Norme MD', 'norme' => true],
    ['icon' => 'bi-people', 'label' => 'Utilizatori', 'page' => 'utilizatori'],
    ['icon' => 'bi-gear', 'label' => 'Setări', 'page' => 'setari'],
];

// Detect page and action
$page = $_GET['page'] ?? 'dashboard';
$action = is_string($_GET['action'] ?? '') ? ($_GET['action'] ?? '') : '';

// Liste acțiuni pentru devize și norme
$devize_actions = [
    'cursuri', 'noutati', 'preturi', 'asistenta', 'contacte', 'dashboard', 'setari', 'utilizatori'
];
$norme_actions = [
    'editare_norme', 'editare_text_norma', 'editare_res', 'adaugare_norme', 'stergere_norme',
    'editare_preturi', 'editare_preturi_material', 'editare_preturi_manopera', 'editare_preturi_utilaj',
    'furnizori', 'furnizori_adaugare', 'furnizori_stergere',
    'recapitulatii', 'curs_moneda', 'adauga_specificatii'
];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>AdminSite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f6f8; }
        .sidebar-wp { width: 220px; min-height: 100vh; background: #23282d; color: #fff; }
        .sidebar-wp .nav-link { color: #bfc5ce; font-size: 1.1rem; }
        .sidebar-wp .nav-link.active, .sidebar-wp .nav-link:hover { background: #191c20; color: #fff; }
        .sidebar-wp .nav-link i { font-size: 1.3rem; margin-right: 10px; }
        .sidebar-wp .sidebar-logo { font-size: 1.5rem; font-weight: 700; padding: 18px 0 18px 24px; letter-spacing: 1px; background: none; border: none; color: #fff; width: 100%; text-align: left; }
        .sidebar-wp .actions-list, .sidebar-wp .norme-list { background: #23282d; padding-left: 48px; margin-bottom: 10px; }
        .sidebar-wp .actions-list .action-link, .sidebar-wp .norme-list .norme-link { color: #bfc5ce; display: flex; align-items: center; padding: 6px 0; font-size: 1.05rem; text-decoration: none; cursor:pointer; }
        .sidebar-wp .actions-list .action-link:hover, .sidebar-wp .norme-list .norme-link:hover { color: #fff; }
        .sidebar-wp .actions-list .action-link i, .sidebar-wp .norme-list .norme-link i { font-size: 1.1rem; margin-right: 8px; }
        .sidebar-wp .norme-list .dropdown-norme { padding-left: 24px; }
        .sidebar-wp .norme-list .dropdown-norme .norme-link { font-size: 0.98rem; }
        .admin-header { background: #fff; border-bottom: 1px solid #e3e6ea; height: 56px; display: flex; align-items: center; justify-content: flex-end; padding: 0 32px; }
        .admin-header .user-info { font-weight: 500; }
        .main-content-wp { padding: 32px 40px; }
        .main-content-wp .page-title { font-size: 2rem; font-weight: 600; margin-bottom: 24px; }
        .main-content-wp .table thead { background: #f8fafb; }
        .main-content-wp .btn-add { float: right; margin-bottom: 16px; }
        @media (max-width: 900px) {
            .main-content-wp { padding: 16px 4vw; }
            .sidebar-wp { width: 100px; }
            .sidebar-wp .nav-link span { display: none; }
        }
        .dropdown-arrow { transition: transform 0.2s; display: inline-block; }
        .dropdown-arrow.rotate { transform: rotate(90deg); }
        .dropdown-arrow.down { transform: rotate(180deg); }
    </style>
    <script>
        function toggleActions() {
            var el = document.getElementById('actions-list');
            el.style.display = (el.style.display === 'block') ? 'none' : 'block';
        }
        function toggleNorme() {
            var el = document.getElementById('norme-list');
            var arrow = document.getElementById('norme-arrow');
            var open = el.style.display === 'block';
            el.style.display = open ? 'none' : 'block';
            if (arrow) arrow.classList.toggle('rotate', !open);
        }
        function toggleSubNorme(id, arrowId) {
            var el = document.getElementById(id);
            var arrow = document.getElementById(arrowId);
            var open = el.style.display === 'block';
            el.style.display = open ? 'none' : 'block';
            if (arrow) arrow.classList.toggle('rotate', !open);
        }
    </script>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar-wp d-flex flex-column p-0">
        <div class="sidebar-logo">AdminSite</div>
        <ul class="nav flex-column mb-auto">
            <?php foreach ($menu as $item): ?>
                <li>
                    <?php if (!empty($item['actions'])): ?>
                        <a href="#" class="nav-link py-3 px-4" onclick="toggleActions(); return false;">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <span><?= $item['label'] ?></span>
                        </a>
                        <div id="actions-list" class="actions-list" style="display:none;">
                            <?php foreach ($actions as $devize_action): ?>
                                <a href="?action=<?= $devize_action['action'] ?>" class="action-link"><i class="bi <?= $devize_action['icon'] ?>"></i><?= $devize_action['label'] ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($item['norme'])): ?>
                        <a href="#" class="nav-link py-3 px-4" onclick="toggleNorme(); return false;">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <span><?= $item['label'] ?> <i id="norme-arrow" class="bi bi-caret-right dropdown-arrow"></i></span>
                        </a>
                        <div id="norme-list" class="norme-list" style="display:none;">
                            <?php foreach ($norme_md as $idx => $norma): ?>
                                <?php if (!empty($norma['children'])): ?>
                                    <div class="norme-link" onclick="toggleSubNorme('subnorme-<?= $idx ?>','subarrow-<?= $idx ?>'); event.stopPropagation();">
                                        <i class="bi <?= $norma['icon'] ?>"></i><?= $norma['label'] ?> <i id="subarrow-<?= $idx ?>" class="bi bi-caret-right dropdown-arrow"></i>
                                    </div>
                                    <div id="subnorme-<?= $idx ?>" class="dropdown-norme" style="display:none;">
                                        <?php foreach ($norma['children'] as $sub): ?>
                                            <a href="?action=<?= $sub['action'] ?>" class="norme-link"><i class="bi <?= $sub['icon'] ?>"></i><?= $sub['label'] ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <a href="?action=<?= $norma['action'] ?>" class="norme-link"><i class="bi <?= $norma['icon'] ?>"></i><?= $norma['label'] ?></a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($item['page'])): ?>
                        <a href="?action=<?= $item['page'] ?>" class="nav-link py-3 px-4<?= !empty($item['active']) ? ' active' : '' ?>">
                            <i class="bi <?= $item['icon'] ?>"></i>
                            <span><?= $item['label'] ?></span>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <!-- Main -->
    <div class="flex-grow-1">
        <div class="admin-header">
            <div class="user-info">
                <i class="bi bi-person-circle me-2"></i> Admin
            </div>
        </div>
        <div class="main-content-wp">
            <?php
            // Includ pagina din devize sau norme, sau dashboard
            if ($action && is_string($action) && preg_match('/^[a-z0-9_]+$/i', $action)) {
                if (in_array($action, $devize_actions)) {
                    $file = "pages/devize/{$action}.php";
                } elseif (in_array($action, $norme_actions)) {
                    $file = "pages/norme/{$action}.php";
                } else {
                    $file = null;
                }
                if ($file && file_exists($file)) {
                    include $file;
                } else {
                    echo "<div class='alert alert-warning'>Pagina nu există încă.</div>";
                }
            } else {
                include 'pages/devize/dashboard.php';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>

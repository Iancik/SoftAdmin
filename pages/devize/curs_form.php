<?php
error_log('--- START curs_form.php ---');
require_once __DIR__ . '/../../config.php';

// Funcție pentru conversia caracterelor speciale românești în coduri HTML
function convertRomanianChars($text) {
    $replacements = [
        'ă' => '&#259;',
        'Ă' => '&#258;',
        'î' => '&#238;',
        'Î' => '&#206;',
        'â' => '&#226;',
        'Â' => '&#194;',
        'ţ' => '&#355;',
        'Ţ' => '&#354;',
        'ț' => '&#355;',
        'Ț' => '&#354;',
        'ş' => '&#537;',
        'Ş' => '&#536;',
        'ș' => '&#537;',
        'Ș' => '&#536;'
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

$curs = [
    'codCalendar' => '',
    'Localitate' => '',
    'Judet' => '',
    'Data' => '',
    'Poza' => '',
    'TipEveniment' => '',
    'Locatia' => '',
    'Ora' => '',
    'Detalii' => '',
    'Vizibil' => 1
];

// Dacă este editare, încărcăm datele
if (isset($_GET['cod'])) {
    error_log('Încărcare date pentru editare cod=' . $_GET['cod']);
    $stmt = $pdo_main->prepare("SELECT * FROM deviz_evenimente WHERE codCalendar = ?");
    $stmt->execute([$_GET['cod']]);
    $curs = $stmt->fetch();
    error_log('Date încărcate: ' . print_r($curs, true));
}

error_log('Afișare formular curs_form.php');
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($_GET['cod']) ? 'Editare Curs' : 'Adăugare Curs Nou' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h2><?= isset($_GET['cod']) ? 'Editare Curs' : 'Adăugare Curs Nou' ?></h2>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['form_errors'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($_SESSION['form_errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['form_errors']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <form method="POST" action="pages/devize/curs_form_save.php" class="needs-validation" novalidate>
            <input type="hidden" name="cod" value="<?= htmlspecialchars($curs['codCalendar']) ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Localitate" class="form-label">Localitate</label>
                    <input type="text" class="form-control" id="Localitate" name="Localitate" value="<?= htmlspecialchars($curs['Localitate']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="Judet" class="form-label">Județ</label>
                    <input type="text" class="form-control" id="Judet" name="Judet" value="<?= htmlspecialchars($curs['Judet']) ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="Data" class="form-label">Data</label>
                    <input type="date" class="form-control" id="Data" name="Data" value="<?= htmlspecialchars($curs['Data']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="Ora" class="form-label">Ora</label>
                    <input type="text" class="form-control" id="Ora" name="Ora" value="<?= htmlspecialchars($curs['Ora']) ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="TipEveniment" class="form-label">Tip Curs</label>
                    <input type="text" class="form-control" id="TipEveniment" name="TipEveniment" value="<?= htmlspecialchars($curs['TipEveniment']) ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="Locatia" class="form-label">Locația</label>
                    <input type="text" class="form-control" id="Locatia" name="Locatia" value="<?= htmlspecialchars($curs['Locatia']) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="Poza" class="form-label">Poza (URL)</label>
                <input type="text" class="form-control" id="Poza" name="Poza" value="<?= htmlspecialchars($curs['Poza']) ?>">
            </div>

            <div class="mb-3">
                <label for="Detalii" class="form-label">Detalii</label>
                <textarea class="form-control" id="Detalii" name="Detalii" style="min-height: 300px;"><?= htmlspecialchars($curs['Detalii']) ?></textarea>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="vizibil" name="vizibil" <?= $curs['Vizibil'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="vizibil">Vizibil</label>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Salvează</button>
                <a href="?action=cursuri&message=updated" class="btn btn-secondary">Anulează</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Inițializare CKEditor
    ClassicEditor
        .create(document.querySelector('#Detalii'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            language: 'ro'
        })
        .catch(error => {
            console.error(error);
        });

    // Validare formular
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html> 
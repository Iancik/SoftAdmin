<?php
error_log('--- START preturi_form.php ---');
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

$oferta = null;
$isEdit = false;

// Verifică dacă este editare
if (isset($_GET['cod'])) {
    $isEdit = true;
    $stmt = $pdo_main->prepare("SELECT * FROM deviz_oferte WHERE CodArticol = ?");
    $stmt->execute([$_GET['cod']]);
    $oferta = $stmt->fetch();
    
    if (!$oferta) {
        $_SESSION['error_message'] = "Oferta nu a fost găsită";
        header("Location: /SoftAdmin/index.php?action=preturi");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editare Preț' : 'Adăugare Preț Nou' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
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
            max-width: 800px;
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

        .form-label {
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(55, 81, 126, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
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

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #2c3e50;
            border-color: #2c3e50;
            color: #fff;
        }

        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background-color: var(--danger-color);
            color: white;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .special-price-badge {
            background-color: #fff3cd;
            color: #856404;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header">
            <h2>
                <i class="fas fa-tag me-2"></i>
                <?= $isEdit ? 'Editare Preț' : 'Adăugare Preț Nou' ?>
            </h2>
            <a href="?action=preturi" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                Înapoi la Listă
            </a>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form method="POST" action="/SoftAdmin/pages/devize/preturi_form_save.php" class="needs-validation" novalidate>
            <?php if ($isEdit): ?>
                <input type="hidden" name="cod" value="<?= htmlspecialchars($oferta['CodArticol']) ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="titlu" class="form-label">Titlu</label>
                    <input type="text" 
                           class="form-control" 
                           id="titlu" 
                           name="titlu" 
                           value="<?= $oferta ? htmlspecialchars($oferta['Titlu']) : '' ?>" 
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tip_articol" class="form-label">Tip Articol</label>
                    <input type="text" 
                           class="form-control" 
                           id="tip_articol" 
                           name="tip_articol" 
                           value="<?= $oferta ? htmlspecialchars($oferta['TipArticol']) : '' ?>" 
                           required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="pret" class="form-label">Preț (RON)</label>
                    <input type="number" 
                           class="form-control" 
                           id="pret" 
                           name="pret" 
                           step="0.01" 
                           min="0" 
                           value="<?= $oferta ? htmlspecialchars($oferta['Pret']) : '' ?>" 
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="pret_reducere" class="form-label">Preț Redus (RON)</label>
                    <input type="number" 
                           class="form-control" 
                           id="pret_reducere" 
                           name="pret_reducere" 
                           step="0.01" 
                           min="0" 
                           value="<?= $oferta ? htmlspecialchars($oferta['PretRedus']) : '' ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="frecventa" class="form-label">Frecvență</label>
                    <input type="text" 
                           class="form-control" 
                           id="frecventa" 
                           name="frecventa" 
                           value="<?= $oferta ? htmlspecialchars($oferta['Frecventa']) : '' ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="pozitie" class="form-label">Poziție</label>
                    <input type="number" 
                           class="form-control" 
                           id="pozitie" 
                           name="pozitie" 
                           min="0" 
                           value="<?= $oferta ? htmlspecialchars($oferta['Pozitie']) : '0' ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="imagine" class="form-label">URL Imagine</label>
                <input type="text" 
                       class="form-control" 
                       id="imagine" 
                       name="imagine" 
                       value="<?= $oferta ? htmlspecialchars($oferta['Imagine']) : '' ?>">
            </div>

            <div class="mb-3">
                <label for="continut" class="form-label">Conținut</label>
                <textarea class="form-control" 
                          id="continut" 
                          name="continut" 
                          required><?= $oferta ? $oferta['Continut'] : '' ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="vizibil" 
                               name="vizibil" 
                               <?= (!$oferta || $oferta['Vizibil'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="vizibil">Vizibil</label>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="special_price" 
                               name="special_price" 
                               <?= ($oferta && $oferta['Special_Price'] == 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="special_price">
                            Preț Special
                            <span class="special-price-badge">
                                <i class="fas fa-star me-1"></i>
                                Oferta Specială
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>
                    <?= $isEdit ? 'Salvează Modificările' : 'Adaugă Prețul' ?>
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Inițializare CKEditor
    let editor;
    ClassicEditor
        .create(document.querySelector('#continut'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            language: 'ro'
        })
        .then(newEditor => {
            editor = newEditor;
        })
        .catch(error => {
            console.error(error);
        });

    // Validare și trimitere formular
    document.querySelector('form').addEventListener('submit', function(event) {
        event.preventDefault();
        
        if (!this.checkValidity()) {
            event.stopPropagation();
            this.classList.add('was-validated');
            return;
        }

        // Afișează animația de salvare
        Swal.fire({
            title: 'Se salvează...',
            html: 'Vă rugăm să așteptați',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(this);
        if (editor) {
            formData.set('continut', editor.getData());
        }
        
        fetch('/SoftAdmin/pages/devize/preturi_form_save.php', {
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
                    icon: 'success',
                    title: 'Succes!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = '/SoftAdmin/index.php?action=preturi';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare!',
                    text: data.message || 'A apărut o eroare la salvare',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Eroare!',
                text: 'A apărut o eroare la salvare: ' + error.message,
                confirmButtonText: 'OK'
            });
        });
    });
    </script>
</body>
</html> 
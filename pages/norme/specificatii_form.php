<?php
require_once __DIR__ . '/../../config.php';

// Verificare dacă este editare
$edit = false;
$specificatie = null;

if (isset($_GET['cod'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM specificatii WHERE codSpecificatie = ?");
        $stmt->execute([$_GET['cod']]);
        $specificatie = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($specificatie) {
            $edit = true;
        }
    } catch (PDOException $e) {
        error_log('Eroare la obținerea specificației: ' . $e->getMessage());
        $_SESSION['error_message'] = "A apărut o eroare la încărcarea specificației";
        header("Location: /SoftAdmin/index.php?action=adauga_specificatii");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit ? 'Editare Specificație' : 'Adăugare Specificație Nouă' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
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
    <div class="page-container">
        <div class="page-header">
            <h2>
                <i class="fas <?= $edit ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                <?= $edit ? 'Editare Specificație' : 'Adăugare Specificație Nouă' ?>
            </h2>
        </div>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form id="specificatiiForm" method="POST" action="/SoftAdmin/api/save_specificatii.php">
            <?php if ($edit): ?>
                <input type="hidden" name="cod" value="<?= htmlspecialchars($specificatie['codSpecificatie']) ?>">
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="simbol" class="form-label">
                    <i class="fas fa-hashtag me-2"></i>Simbol
                </label>
                <input type="text" class="form-control" id="simbol" name="simbol" 
                       value="<?= htmlspecialchars($specificatie['Simbol'] ?? '') ?>" required>
            </div>
            
            <div class="mb-4">
                <label for="descriere" class="form-label">
                    <i class="fas fa-align-left me-2"></i>Descriere
                </label>
                <div class="editor-container">
                    <div class="editor-toggle">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="editorModeToggle">
                            <label class="form-check-label" for="editorModeToggle">Mod HTML</label>
                        </div>
                    </div>
                    <textarea class="form-control" id="descriere" name="descriere"><?= htmlspecialchars($specificatie['Descriere'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="file-upload-container mb-4">
                <label for="htmlFile" class="form-label">
                    <i class="fas fa-file-upload me-2"></i>Încarcă fișier HTML
                </label>
                <input type="file" class="form-control" id="htmlFile" name="htmlFile" accept=".html,.htm">
                <div class="small-text">
                    <i class="fas fa-info-circle me-1"></i>
                    Poți încărca doar fișiere .html sau .htm. Diacriticele vor fi convertite automat în coduri HTML.
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="/SoftAdmin/index.php?action=adauga_specificatii" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Anulează
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Salvează
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let editor;
        let isSourceMode = false;
        
        // Funcție pentru înlocuirea diacriticelor cu coduri HTML
        function replaceRomanianChars(text) {
            const map = {
                'ă': '&#259;', 'Ă': '&#258;',
                'î': '&#238;', 'Î': '&#206;',
                'â': '&#226;', 'Â': '&#194;',
                'ș': '&#351;', 'Ș': '&#350;',
                'ş': '&#351;', 'Ş': '&#350;',
                'ț': 't', 'Ț': 'T',
                'ţ': 't', 'Ţ': 'T'
            };
            return text.replace(/[ăĂîÎâÂșȘşŞțȚţŢ]/g, m => map[m] || m);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Inițializare CKEditor
            editor = CKEDITOR.replace('descriere', {
                height: 300,
                removePlugins: 'elementspath,resize',
                toolbar: [
                    { name: 'document', items: [ 'Source' ] },
                    { name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', '-', 'Undo', 'Redo' ] },
                    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
                    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                    { name: 'links', items: [ 'Link', 'Unlink' ] },
                    { name: 'insert', items: [ 'Table', 'HorizontalRule', 'SpecialChar' ] },
                    { name: 'styles', items: [ 'Styles', 'Format', 'Font', 'FontSize' ] },
                    { name: 'colors', items: [ 'TextColor', 'BGColor' ] }
                ],
                allowedContent: true,
                fullPage: false,
                enterMode: CKEDITOR.ENTER_BR,
                shiftEnterMode: CKEDITOR.ENTER_P,
                autoParagraph: false,
                entities: false,
                entities_latin: false,
                entities_greek: false,
                basicEntities: false
            });

            // Gestionare toggle editor mode
            const editorModeToggle = document.getElementById('editorModeToggle');
            editorModeToggle.addEventListener('change', function() {
                if (editor) {
                    if (this.checked) {
                        // Switch to source mode
                        editor.setMode('source');
                        isSourceMode = true;
                    } else {
                        // Switch to WYSIWYG mode
                        editor.setMode('wysiwyg');
                        isSourceMode = false;
                    }
                }
            });

            // Gestionare încărcare fișier HTML
            const htmlFileInput = document.getElementById('htmlFile');
            htmlFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (fileExtension !== 'html' && fileExtension !== 'htm') {
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger mt-3';
                    errorAlert.textContent = 'Vă rugăm să încărcați doar fișiere .html sau .htm';
                    htmlFileInput.parentNode.appendChild(errorAlert);
                    setTimeout(() => errorAlert.remove(), 5000);
                    htmlFileInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    let content = e.target.result;
                    if (editor) {
                        editor.setData(content);
                        // Verifică dacă conținutul conține HTML complex
                        if (content.includes('<') && content.includes('>') && 
                            (content.includes('<div') || content.includes('<table') || 
                             content.includes('<ul') || content.includes('<ol'))) {
                            editorModeToggle.checked = true;
                            editor.setMode('source');
                            isSourceMode = true;
                        }
                    }
                };
                reader.onerror = function() {
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger mt-3';
                    errorAlert.textContent = 'Eroare la citirea fișierului HTML';
                    htmlFileInput.parentNode.appendChild(errorAlert);
                    setTimeout(() => errorAlert.remove(), 5000);
                    htmlFileInput.value = '';
                };
                reader.readAsText(file, 'UTF-8');
            });

            // Gestionare trimitere formular
            const form = document.getElementById('specificatiiForm');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                // Obține conținutul din CKEditor
                const descriereContent = editor.getData();
                // Înlocuiește diacriticele doar la salvare
                const processedContent = replaceRomanianChars(descriereContent);
                const descriereInput = document.getElementById('descriere');
                descriereInput.value = processedContent;
                // Creează FormData din formular
                const formData = new FormData(this);
                // Afișează mesaj de încărcare
                const loadingAlert = document.createElement('div');
                loadingAlert.className = 'alert alert-info mt-3';
                loadingAlert.textContent = 'Se salvează specificația...';
                form.parentNode.appendChild(loadingAlert);
                // Trimite datele
                fetch('/SoftAdmin/api/save_specificatii.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    loadingAlert.remove();
                    if (data.success) {
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert alert-success mt-3';
                        successAlert.textContent = data.message;
                        form.parentNode.appendChild(successAlert);
                        setTimeout(() => {
                            window.location.href = '/SoftAdmin/index.php?action=adauga_specificatii';
                        }, 1000);
                    } else {
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger mt-3';
                        errorAlert.textContent = data.message;
                        form.parentNode.appendChild(errorAlert);
                        setTimeout(() => errorAlert.remove(), 5000);
                    }
                })
                .catch(error => {
                    loadingAlert.remove();
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger mt-3';
                    errorAlert.textContent = 'A apărut o eroare la comunicarea cu serverul';
                    form.parentNode.appendChild(errorAlert);
                    setTimeout(() => errorAlert.remove(), 5000);
                });
            });
        });
    </script>
</body>
</html> 
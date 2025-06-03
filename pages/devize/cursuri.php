<?php
error_log('--- START cursuri.php ---');
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
        $stmt = $pdo_main->prepare("DELETE FROM deviz_evenimente WHERE codCalendar = ?");
        $stmt->execute([$_POST['cod']]);
        $_SESSION['success_message'] = "Cursul a fost șters cu succes";
    } catch (PDOException $e) {
        error_log('Eroare la ștergerea cursului: ' . $e->getMessage());
        $_SESSION['error_message'] = "A apărut o eroare la ștergerea cursului";
    }
    header("Location: /SoftAdmin/index.php?action=cursuri");
    exit;
}

// Obținere evenimente
error_log('Obținere evenimente din baza de date');
$stmt = $pdo_main->query("SELECT * FROM deviz_evenimente ORDER BY Data DESC");
$evenimente = $stmt->fetchAll();
error_log('Evenimente obținute: ' . count($evenimente));
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Cursuri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Lista Cursurilor</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error_message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="mb-3">
            <a href="?action=curs_form" class="btn btn-primary">Adaugă Curs Nou</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Localitate</th>
                        <th>Județ</th>
                        <th>Tip Curs</th>
                        <th>Locația</th>
                        <th>Ora</th>
                        <th>Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evenimente as $eveniment): ?>
                    <?php error_log('Afișare eveniment cod=' . $eveniment['codCalendar']); ?>
                    <tr<?= $eveniment['Vizibil'] == 0 ? ' style="background-color:#ffeaea;"' : '' ?>>
                        <td><?= replaceRomanianChars(htmlspecialchars($eveniment['Data'])) ?></td>
                        <td><?= replaceRomanianChars(htmlspecialchars($eveniment['Localitate'])) ?></td>
                        <td><?= replaceRomanianChars(htmlspecialchars($eveniment['Judet'])) ?></td>
                        <td><?= replaceRomanianChars(htmlspecialchars($eveniment['TipEveniment'])) ?></td>
                        <td><?= replaceRomanianChars(htmlspecialchars($eveniment['Locatia'] ?? '')) ?></td>
                        <td><?= replaceRomanianChars(htmlspecialchars($eveniment['Ora'])) ?></td>
                        <td>
                            <a href="?action=curs_form&cod=<?= $eveniment['codCalendar'] ?>" class="btn btn-sm btn-warning">Editează</a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(<?= $eveniment['codCalendar'] ?>)">Șterge</button>
                            <?php if ($eveniment['Vizibil'] == 0): ?>
                                <span class="badge bg-danger ms-2">Invizibil</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
            title: 'Sigur doriți să ștergeți acest curs?',
            text: "Această acțiune nu poate fi anulată!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Da, șterge!',
            cancelButtonText: 'Anulează'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteCod').value = cod;
                document.getElementById('deleteForm').submit();
            }
        });
    }
    </script>
</body>
</html> 
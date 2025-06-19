<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../config.php';

// Funcție pentru înlocuirea caracterelor speciale românești
function convertRomanianChars($text) {
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
    error_log("Eroare conexiune baza de date: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Eroare conexiune baza de date']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validare câmpuri obligatorii
    $required_fields = ['titlu', 'tip_articol', 'pret', 'continut'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        error_log("Câmpuri obligatorii lipsă: " . implode(', ', $missing_fields));
        echo json_encode([
            'success' => false,
            'message' => 'Toate câmpurile marcate cu * sunt obligatorii'
        ]);
        exit;
    }

    // Procesare date
    $titlu = trim($_POST['titlu']);
    $tip_articol = trim($_POST['tip_articol']);
    $pret = floatval($_POST['pret']);
    $pret_reducere = !empty($_POST['pret_reducere']) ? floatval($_POST['pret_reducere']) : null;
    $frecventa = trim($_POST['frecventa'] ?? '');
    $pozitie = intval($_POST['pozitie'] ?? 0);
    $imagine = trim($_POST['imagine'] ?? '');
    $continut = $_POST['continut'];
    $vizibil = isset($_POST['vizibil']) ? 1 : 0;
    $special_price = isset($_POST['special_price']) ? 1 : 0;

    // Logging pentru debugging
    error_log("Date primite: " . print_r($_POST, true));
    error_log("Variabile procesate: " . print_r([
        'Titlu' => $titlu,
        'TipArticol' => $tip_articol,
        'Pret' => $pret,
        'PretRedus' => $pret_reducere,
        'Frecventa' => $frecventa,
        'Pozitie' => $pozitie,
        'Imagine' => $imagine,
        'Vizibil' => $vizibil,
        'Special_Price' => $special_price
    ], true));

    try {
        if (isset($_POST['cod']) && is_numeric($_POST['cod']) && $_POST['cod'] > 0) {
            // Update
            error_log("Încercare update pentru codul: " . $_POST['cod']);
            
            // Verifică dacă înregistrarea există
            $check = $pdo_main->prepare("SELECT COUNT(*) FROM deviz_oferte WHERE CodArticol = ?");
            $check->execute([$_POST['cod']]);
            if ($check->fetchColumn() == 0) {
                throw new Exception("Oferta nu a fost găsită");
            }

            $stmt = $pdo_main->prepare("
                UPDATE deviz_oferte 
                SET Titlu = ?, 
                    TipArticol = ?, 
                    Pret = ?, 
                    PretRedus = ?, 
                    Frecventa = ?, 
                    Pozitie = ?, 
                    Imagine = ?, 
                    Continut = ?, 
                    Vizibil = ?, 
                    Special_Price = ?
                WHERE CodArticol = ?
            ");

            $stmt->execute([
                $titlu,
                $tip_articol,
                $pret,
                $pret_reducere,
                $frecventa,
                $pozitie,
                $imagine,
                $continut,
                $vizibil,
                $special_price,
                $_POST['cod']
            ]);

            error_log("Update reușit pentru codul: " . $_POST['cod']);
            echo json_encode([
                'success' => true,
                'message' => 'Prețul a fost actualizat cu succes'
            ]);
        } else {
            // Insert
            error_log("Încercare insert nou preț");
            
            $stmt = $pdo_main->prepare("
                INSERT INTO deviz_oferte 
                (Titlu, TipArticol, Pret, PretRedus, Frecventa, Pozitie, Imagine, Continut, Vizibil, Special_Price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $titlu,
                $tip_articol,
                $pret,
                $pret_reducere,
                $frecventa,
                $pozitie,
                $imagine,
                $continut,
                $vizibil,
                $special_price
            ]);

            error_log("Insert reușit pentru noul preț");
            echo json_encode([
                'success' => true,
                'message' => 'Prețul a fost adăugat cu succes'
            ]);
        }
    } catch (Exception $e) {
        error_log("Eroare la salvare: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'A apărut o eroare la salvare: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metoda de request invalidă'
    ]);
} 
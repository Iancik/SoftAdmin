<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

error_log('--- INTRARE curs_form_save.php ---');
error_log('POST primit: ' . print_r($_POST, true));

function convertRomanianChars($text) {
    $replacements = [
        'ă' => '&#259;', 'Ă' => '&#258;', 'î' => '&#238;', 'Î' => '&#206;',
        'â' => '&#226;', 'Â' => '&#194;',
        'ţ' => '&#355;', 'Ţ' => '&#354;', 'ț' => '&#355;', 'Ț' => '&#354;',
        'ş' => '&#537;', 'Ş' => '&#536;', 'ș' => '&#537;', 'Ș' => '&#536;'
    ];
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

try {
    // Conectare la baza de date principală
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('--- START curs_form_save.php ---');
        error_log('POST primit: ' . print_r($_POST, true));

        // Validare date de intrare
        $required_fields = ['Localitate', 'Judet', 'Data', 'TipEveniment', 'Locatia', 'Ora'];
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Câmpul {$field} este obligatoriu";
                error_log("Eroare validare: Câmpul {$field} este gol");
            }
        }

        if (!empty($errors)) {
            error_log('Erori de validare: ' . implode(', ', $errors));
            echo json_encode([
                'success' => false,
                'message' => implode("\n", $errors)
            ]);
            exit;
        }

        $data = $_POST['Data'];
        $localitate = $_POST['Localitate'];
        $judet = $_POST['Judet'];
        $tip_eveniment = $_POST['TipEveniment'];
        $locatia = $_POST['Locatia'];
        $ora = $_POST['Ora'];
        $poza = $_POST['Poza'] ?? '';
        $detalii = convertRomanianChars($_POST['Detalii'] ?? '');
        $vizibil = isset($_POST['vizibil']) ? 1 : 0;
        
        error_log("Variabile procesate: Data=$data, Localitate=$localitate, Judet=$judet, TipEveniment=$tip_eveniment, Locatia=$locatia, Ora=$ora, Poza=$poza, Vizibil=$vizibil");

        error_log('Valoare cod primită: ' . var_export($_POST['cod'], true));
        
        if (isset($_POST['cod']) && is_numeric($_POST['cod']) && intval($_POST['cod']) > 0) {
            error_log('INTRARE BRANCH UPDATE');
            error_log('Se face UPDATE pentru cod=' . $_POST['cod']);
            
            // Verificare dacă înregistrarea există
            $check = $pdo_main->prepare("SELECT COUNT(*) FROM deviz_evenimente WHERE codCalendar = ?");
            $check->execute([$_POST['cod']]);
            if ($check->fetchColumn() == 0) {
                error_log('Eroare: Înregistrarea cu cod=' . $_POST['cod'] . ' nu există');
                throw new Exception('Înregistrarea nu a fost găsită');
            }
            
            $stmt = $pdo_main->prepare("UPDATE deviz_evenimente SET Data = ?, Localitate = ?, Judet = ?, TipEveniment = ?, Locatia = ?, Ora = ?, Poza = ?, Detalii = ?, Vizibil = ? WHERE codCalendar = ?");
            $ok = $stmt->execute([$data, $localitate, $judet, $tip_eveniment, $locatia, $ora, $poza, $detalii, $vizibil, $_POST['cod']]);
            
            if (!$ok) {
                $err = $stmt->errorInfo();
                error_log('Eroare UPDATE PDO: ' . print_r($err, true));
                throw new Exception('Eroare la actualizarea înregistrării: ' . $err[2]);
            }
            error_log('UPDATE executat cu succes');
            
        } else {
            error_log('INTRARE BRANCH INSERT');
            error_log('Se face INSERT nou (cod invalid sau lipsă)');
            
            $stmt = $pdo_main->prepare("INSERT INTO deviz_evenimente (Data, Localitate, Judet, TipEveniment, Locatia, Ora, Poza, Detalii, Vizibil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ok = $stmt->execute([$data, $localitate, $judet, $tip_eveniment, $locatia, $ora, $poza, $detalii, $vizibil]);
            
            if (!$ok) {
                $err = $stmt->errorInfo();
                error_log('Eroare INSERT PDO: ' . print_r($err, true));
                throw new Exception('Eroare la inserarea înregistrării: ' . $err[2]);
            }
            error_log('INSERT executat cu succes');
        }
        
        echo json_encode([
            'success' => true,
            'message' => isset($_POST['cod']) ? 'Evenimentul a fost actualizat cu succes' : 'Evenimentul a fost adăugat cu succes'
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Metoda de request invalidă'
    ]);
    exit;

} catch (Exception $e) {
    error_log('Eroare: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Eroare: ' . $e->getMessage()
    ]);
    exit;
} 
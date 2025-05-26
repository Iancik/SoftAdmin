<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Activăm logging pentru debug
error_log("POST data: " . print_r($_POST, true));

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $simbol = trim($_POST['simbol'] ?? '');
    $denumire_veche = trim($_POST['denumire_veche'] ?? '');
    $denumire_noua = trim($_POST['denumire'] ?? '');
    $um = trim($_POST['um'] ?? '');

    error_log("Procesare date: Simbol=$simbol, Denumire veche=$denumire_veche, Denumire noua=$denumire_noua, UM=$um");

    if (!empty($simbol) && !empty($denumire_veche) && !empty($denumire_noua) && !empty($um)) {
        try {
            // Verificăm mai întâi dacă există înregistrarea
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM ArticoleVariante WHERE Simbol = ? AND Denumire = ?");
            $checkStmt->execute([$simbol, $denumire_veche]);
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                // Actualizăm doar înregistrarea care are exact același Simbol și Denumire
                $stmt = $pdo->prepare("UPDATE ArticoleVariante SET Denumire = ?, Um = ? WHERE Simbol = ? AND Denumire = ?");
                if ($stmt->execute([$denumire_noua, $um, $simbol, $denumire_veche])) {
                    $response['success'] = true;
                    $response['message'] = 'Modificările au fost salvate cu succes!';
                    error_log("Salvare reușită pentru Simbol=$simbol și Denumire=$denumire_veche");
                } else {
                    $response['message'] = 'Eroare la executarea query';
                    error_log("Eroare la executarea query pentru Simbol=$simbol și Denumire=$denumire_veche");
                }
            } else {
                $response['message'] = 'Nu s-a găsit înregistrarea cu simbolul și denumirea specificate';
                error_log("Nu s-a găsit înregistrarea pentru Simbol=$simbol și Denumire=$denumire_veche");
            }
        } catch (PDOException $e) {
            $response['message'] = 'Eroare la baza de date: ' . $e->getMessage();
            error_log("Eroare PDO: " . $e->getMessage());
        }
    } else {
        $response['message'] = 'Date incomplete';
        error_log("Date incomplete: Simbol=$simbol, Denumire veche=$denumire_veche, Denumire noua=$denumire_noua, UM=$um");
    }
} else {
    $response['message'] = 'Metodă invalidă';
    error_log("Metodă invalidă: " . $_SERVER['REQUEST_METHOD']);
}

error_log("Răspuns: " . json_encode($response));
echo json_encode($response); 
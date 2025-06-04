<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cod'])) {
        $stmt = $pdo_main->prepare("DELETE FROM deviz_evenimente WHERE codCalendar = ?");
        $stmt->execute([$_POST['cod']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Cursul a fost șters cu succes'
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Parametri invalizi'
    ]);
    exit;

} catch (Exception $e) {
    error_log('Eroare la ștergerea cursului: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A apărut o eroare la ștergerea cursului: ' . $e->getMessage()
    ]);
    exit;
} 
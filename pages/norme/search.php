<?php
require_once 'config.php';

// Afișează erorile pentru depanare
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$symbol = $_GET['symbol'] ?? '';

if (empty($symbol)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Simbol lipsă']);
    exit;
}

// Modificat pentru a returna toate înregistrările care corespund simbolului
$stmt = $pdo->prepare("SELECT codVarianta, simbol, Denumire FROM ArticoleVariante WHERE simbol = ?");
$stmt->execute([$symbol]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($results && count($results) > 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'multiple' => count($results) > 1,
        'count' => count($results),
        'variants' => $results
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Nu s-a găsit nicio normă cu acest simbol!'
    ]);
}
?> 
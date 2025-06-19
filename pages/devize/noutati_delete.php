<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cod'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Cerere invalidă'
    ]);
    exit;
}

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

    $stmt = $pdo_main->prepare("DELETE FROM deviz_noutati WHERE codBuletin = ?");
    $stmt->execute([$_POST['cod']]);

    echo json_encode([
        'success' => true,
        'message' => 'Noutatea a fost ștearsă cu succes'
    ]);
} catch (PDOException $e) {
    error_log('Eroare la ștergerea noutății: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A apărut o eroare la ștergerea noutății'
    ]);
} 
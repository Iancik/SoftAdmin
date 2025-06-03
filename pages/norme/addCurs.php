<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['codCurs']) || !isset($data['cursEuro']) || !isset($data['dataCurs'])) {
    echo json_encode(['success' => false, 'error' => 'Date invalide']);
    exit;
}

$codCurs = $data['codCurs'];
$cursEuro = $data['cursEuro'];
$dataCurs = $data['dataCurs'];

try {
    // Verifică dacă există deja un curs pentru această dată
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM CursEuro 
        WHERE Data = ?
    ");
    $stmt->execute([$dataCurs]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Există deja un curs pentru această dată!']);
        exit;
    }

    // Adaugă noul curs
    $stmt = $pdo->prepare("
        INSERT INTO CursEuro (CodCurs, Curs, Data)
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$codCurs, $cursEuro, $dataCurs]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
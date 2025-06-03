<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['codIndice']) || !isset($data['codRecapitulatie']) || !isset($data['valoare'])) {
    echo json_encode(['success' => false, 'error' => 'Date invalide']);
    exit;
}

$codIndice = $data['codIndice'];
$codRecapitulatie = $data['codRecapitulatie'];
$valoare = $data['valoare'];

try {
    $stmt = $pdo->prepare("
        UPDATE LinkRecapitulatiiIndici 
        SET Valoare = ? 
        WHERE CodIndice = ? AND CodRecapitulatie = ?
    ");
    
    $stmt->execute([$valoare, $codIndice, $codRecapitulatie]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Înregistrarea nu a fost găsită']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
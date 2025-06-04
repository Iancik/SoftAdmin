<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Date invalide']);
    exit;
}

$codVarianta = $data['codVarianta'] ?? '';
$codItem = $data['codItem'] ?? '';
$tip = $data['tip'] ?? '';

if (empty($codVarianta) || empty($codItem) || empty($tip)) {
    echo json_encode(['success' => false, 'error' => 'Toate câmpurile sunt obligatorii']);
    exit;
}

try {
    // Selectează tabela și coloana corectă în funcție de tip
    $table = '';
    $codColumn = '';
    switch ($tip) {
        case 'material':
            $table = 'Link_Variante_Material';
            $codColumn = 'CodMaterial';
            break;
        case 'manopera':
            $table = 'Link_Variante_Manopera';
            $codColumn = 'codManopera';
            break;
        case 'utilaj':
            $table = 'Link_Variante_Utilaj';
            $codColumn = 'codUtilaj';
            break;
        default:
            throw new Exception('Tip resursă invalid');
    }

    $stmt = $pdo->prepare("
        DELETE FROM $table 
        WHERE codVarianta = ? AND $codColumn = ?
    ");
    
    $stmt->execute([$codVarianta, $codItem]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Resursa nu a fost găsită']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
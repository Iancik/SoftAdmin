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
$pozitie = $data['pozitie'] ?? '';
$cantitate = $data['cantitate'] ?? '';
$tip = $data['tip'] ?? '';

if (empty($codVarianta) || empty($codItem) || empty($pozitie) || empty($cantitate) || empty($tip)) {
    echo json_encode(['success' => false, 'error' => 'Toate câmpurile sunt obligatorii']);
    exit;
}

try {
    // Verifică dacă varianta există în ArticoleVariante
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM ArticoleVariante 
        WHERE codVarianta = ?
    ");
    $stmt->execute([$codVarianta]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'error' => 'Varianta nu există în baza de date']);
        exit;
    }

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

    // Verifică dacă resursa există deja în tabela de legătură
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM $table 
        WHERE codVarianta = ? AND $codColumn = ?
    ");
    $stmt->execute([$codVarianta, $codItem]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Această resursă există deja în normă']);
        exit;
    }

    // Adaugă resursa nouă în tabela de legătură
    $stmt = $pdo->prepare("
        INSERT INTO $table (codVarianta, $codColumn, Pozitie, Cantitate)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$codVarianta, $codItem, $pozitie, $cantitate]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
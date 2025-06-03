<?php
require_once 'config.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$term = $_GET['term'] ?? '';

if (empty($type) || empty($term)) {
    echo json_encode(['error' => 'Parametri lipsă']);
    exit;
}

try {
    $table = '';
    switch ($type) {
        case 'material':
            $table = 'MaterialTehnologic';
            break;
        case 'manopera':
            $table = 'Manopera';
            break;
        case 'utilaj':
            $table = 'Utilaj';
            break;
        default:
            throw new Exception('Tip resursă invalid');
    }

    $stmt = $pdo->prepare("
        SELECT cod as cod, denumire as denumire 
        FROM $table 
        WHERE denumire LIKE ?
        ORDER BY denumire
        LIMIT 10
    ");
    
    $searchTerm = "%$term%";
    $stmt->execute([$searchTerm]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
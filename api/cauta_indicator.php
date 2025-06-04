<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['search'])) {
    echo json_encode(['error' => 'Parametru lipsă']);
    exit;
}

$search = $_GET['search'];

try {
    $query = "SELECT codIndicator, denumire 
              FROM Indicatoare 
              WHERE denumire LIKE :search 
              ORDER BY denumire 
              LIMIT 10";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "%$search%"]);
    $results = $stmt->fetchAll();

    // Format results for Select2
    $formattedResults = array_map(function($item) {
        return [
            'id' => $item['codIndicator'],  // This will be the value stored
            'text' => $item['denumire']     // This will be shown in dropdown
        ];
    }, $results);

    echo json_encode($formattedResults);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 
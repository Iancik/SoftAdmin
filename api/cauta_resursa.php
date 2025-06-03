<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['search']) || !isset($_GET['tip'])) {
    echo json_encode(['error' => 'Parametri lipsă']);
    exit;
}

$search = $_GET['search'];
$tip = $_GET['tip'];

try {
    $table = '';
    $idField = '';
    $nameField = '';

    switch ($tip) {
        case 'manopera':
            $table = 'Manopera';
            $idField = 'codManopera';
            $nameField = 'denumire';
            break;
        case 'material':
            $table = 'MaterialTehnologic';
            $idField = 'codMaterial';
            $nameField = 'denumire';
            break;
        case 'utilaj':
            $table = 'Utilaj';
            $idField = 'codUtilaj';
            $nameField = 'denumire';
            break;
        default:
            throw new Exception('Tip de resursă invalid');
    }

    $query = "SELECT $idField as id, $nameField as text 
              FROM $table 
              WHERE $nameField LIKE :search 
              ORDER BY $nameField 
              LIMIT 10";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "%$search%"]);
    $results = $stmt->fetchAll();

    echo json_encode($results);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 
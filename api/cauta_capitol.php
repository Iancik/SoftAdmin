<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['search'])) {
    echo json_encode(['error' => 'Parametru lipsă']);
    exit;
}

$search = $_GET['search'];

try {
    $query = "SELECT codCapitol as id, denumire as text 
              FROM Capitole 
              WHERE denumire LIKE :search 
              ORDER BY denumire 
              LIMIT 10";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "%$search%"]);
    $results = $stmt->fetchAll();

    echo json_encode($results);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 
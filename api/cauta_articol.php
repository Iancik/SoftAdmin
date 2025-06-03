<?php
require_once '../config.php';
header('Content-Type: application/json');

$search = isset($_GET['search']) ? $_GET['search'] : '';

if (empty($search)) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT codArticol as id, denumire as text 
                          FROM Articole 
                          WHERE denumire LIKE :search 
                          ORDER BY denumire 
                          LIMIT 10");
    $stmt->execute(['search' => "%$search%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 
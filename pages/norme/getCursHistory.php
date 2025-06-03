<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Query to get all currency rates without any limit
    $stmt = $pdo->query("
        SELECT CodCurs, Curs, Data 
        FROM CursEuro 
        ORDER BY Data DESC, CodCurs DESC
    ");
    
    $cursuri = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'cursuri' => $cursuri,
        'count' => count($cursuri) // Adding count for debugging
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 
<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['codIndicator'])) {
        throw new Exception('Parametrul codIndicator este obligatoriu');
    }

    $codIndicator = $_GET['codIndicator'];
    
    $stmt = $pdo->prepare("SELECT c.codCapitol, c.denumire as denumireCapitol 
                          FROM Capitole c 
                          INNER JOIN Indicatoare i ON c.codCapitol = i.codCapitol 
                          WHERE i.codIndicator = :codIndicator");
    
    $stmt->execute([':codIndicator' => $codIndicator]);
    $result = $stmt->fetch();

    if ($result) {
        echo json_encode($result);
    } else {
        echo json_encode(['error' => 'Nu s-a găsit capitolul pentru indicatorul specificat']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 
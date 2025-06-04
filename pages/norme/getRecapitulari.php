<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            l.CodIndice,
            i.Denumire as DenumireIndice,
            l.CodRecapitulatie,
            r.Denumire as DenumireRecapitulatie,
            l.Valoare
        FROM LinkRecapitulatiiIndici l
        JOIN Recapitulatii r ON l.CodRecapitulatie = r.CodRecapitulatie
        JOIN Indici i ON l.CodIndice = i.CodIndice
        ORDER BY l.CodIndice, l.CodRecapitulatie
    ");
    
    $recapitulari = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'recapitulari' => $recapitulari
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 
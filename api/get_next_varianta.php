<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getConnection();
    
    // Get the highest cod_varianta from all three tables
    $query = "SELECT MAX(cod_varianta) as max_cod FROM (
        SELECT cod_varianta FROM link_variante_manopera
        UNION ALL
        SELECT cod_varianta FROM link_variante_utilaj
        UNION ALL
        SELECT cod_varianta FROM link_variante_material
    ) as combined_tables";
    
    $stmt = $pdo->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $nextCod = 1; // Default value if no records exist
    if ($result && $result['max_cod']) {
        $nextCod = intval($result['max_cod']) + 1;
    }
    
    // Log the result for debugging
    error_log("Next cod_varianta: " . $nextCod);
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'next_cod' => $nextCod
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in get_next_varianta.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la obținerea codului variantei: ' . $e->getMessage()
    ]);
} 
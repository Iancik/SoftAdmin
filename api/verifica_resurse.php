<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

try {
    $results = [];
    
    // Verifică resursele din link_variante_manopera
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM link_variante_manopera
    ");
    $results['manopera'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Verifică resursele din link_variante_material
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM link_variante_material
    ");
    $results['material'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Verifică resursele din link_variante_utilaj
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM link_variante_utilaj
    ");
    $results['utilaj'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Verifică dacă există resurse orfane (coduri care nu există în tabelele principale)
    $stmt = $pdo->query("
        SELECT lvm.codVarianta, lvm.codManopera, m.denumire as denumire_manopera
        FROM link_variante_manopera lvm
        LEFT JOIN manopera m ON lvm.codManopera = m.codManopera
        WHERE m.codManopera IS NULL
    ");
    $results['manopera_orfane'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT lvm.codVarianta, lvm.CodMaterial, m.denumire as denumire_material
        FROM link_variante_material lvm
        LEFT JOIN materialtehnologic m ON lvm.CodMaterial = m.codMaterial
        WHERE m.codMaterial IS NULL
    ");
    $results['material_orfane'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT lvu.codVarianta, lvu.codUtilaj, u.denumire as denumire_utilaj
        FROM link_variante_utilaj lvu
        LEFT JOIN utilaj u ON lvu.codUtilaj = u.codUtilaj
        WHERE u.codUtilaj IS NULL
    ");
    $results['utilaj_orfane'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verifică dacă există resurse pentru variante care nu există
    $stmt = $pdo->query("
        SELECT lvm.codVarianta, lvm.codManopera
        FROM link_variante_manopera lvm
        LEFT JOIN articolevariante av ON lvm.codVarianta = av.codVarianta
        WHERE av.codVarianta IS NULL
    ");
    $results['manopera_variante_inexistente'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT lvm.codVarianta, lvm.CodMaterial
        FROM link_variante_material lvm
        LEFT JOIN articolevariante av ON lvm.codVarianta = av.codVarianta
        WHERE av.codVarianta IS NULL
    ");
    $results['material_variante_inexistente'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("
        SELECT lvu.codVarianta, lvu.codUtilaj
        FROM link_variante_utilaj lvu
        LEFT JOIN articolevariante av ON lvu.codVarianta = av.codVarianta
        WHERE av.codVarianta IS NULL
    ");
    $results['utilaj_variante_inexistente'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
} 
<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['codVarianta'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Codul variantei este obligatoriu'
    ]);
    exit;
}

$codVarianta = $_GET['codVarianta'];

try {
    $resurse = [];

    // Încarcă manopera
    $stmt = $pdo->prepare("
        SELECT m.codManopera as id, m.denumire, lvm.Pozitie as pozitie, lvm.Cantitate as cantitate
        FROM Link_Variante_Manopera lvm
        JOIN Manopera m ON m.codManopera = lvm.codManopera
        WHERE lvm.codVarianta = ?
        ORDER BY lvm.Pozitie
    ");
    $stmt->execute([$codVarianta]);
    while ($row = $stmt->fetch()) {
        $row['tip'] = 'manopera';
        $resurse[] = $row;
    }

    // Încarcă materiale
    $stmt = $pdo->prepare("
        SELECT mt.codMaterial as id, mt.denumire, lvm.Pozitie as pozitie, lvm.Cantitate as cantitate
        FROM Link_Variante_Material lvm
        JOIN MaterialTehnologic mt ON mt.codMaterial = lvm.CodMaterial
        WHERE lvm.codVarianta = ?
        ORDER BY lvm.Pozitie
    ");
    $stmt->execute([$codVarianta]);
    while ($row = $stmt->fetch()) {
        $row['tip'] = 'material';
        $resurse[] = $row;
    }

    // Încarcă utilaje
    $stmt = $pdo->prepare("
        SELECT u.codUtilaj as id, u.denumire, lvu.Pozitie as pozitie, lvu.Cantitate as cantitate
        FROM Link_Variante_Utilaj lvu
        JOIN Utilaj u ON u.codUtilaj = lvu.codUtilaj
        WHERE lvu.codVarianta = ?
        ORDER BY lvu.Pozitie
    ");
    $stmt->execute([$codVarianta]);
    while ($row = $stmt->fetch()) {
        $row['tip'] = 'utilaj';
        $resurse[] = $row;
    }

    echo json_encode([
        'success' => true,
        'resurse' => $resurse
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la încărcarea resurselor: ' . $e->getMessage()
    ]);
} 
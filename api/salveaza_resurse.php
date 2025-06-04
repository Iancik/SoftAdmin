<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Metoda HTTP invalidă'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

if (!isset($data['simbol']) || !isset($data['resurse'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Date incomplete'
    ]);
    exit;
}

$simbol = $data['simbol'];
$resurse = $data['resurse'];

try {
    $pdo->beginTransaction();

    // Șterge resursele existente
    $pdo->exec("DELETE FROM Link_Variante_Manopera WHERE codVarianta = " . $pdo->quote($simbol));
    $pdo->exec("DELETE FROM Link_Variante_Material WHERE codVarianta = " . $pdo->quote($simbol));
    $pdo->exec("DELETE FROM Link_Variante_Utilaj WHERE codVarianta = " . $pdo->quote($simbol));

    // Inserează resursele noi
    foreach ($resurse as $resursa) {
        switch ($resursa['tip']) {
            case 'manopera':
                $stmt = $pdo->prepare("
                    INSERT INTO Link_Variante_Manopera (codVarianta, codManopera, Pozitie, Cantitate)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$simbol, $resursa['id'], $resursa['pozitie'], $resursa['cantitate']]);
                break;

            case 'material':
                $stmt = $pdo->prepare("
                    INSERT INTO Link_Variante_Material (codVarianta, CodMaterial, Pozitie, Cantitate)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$simbol, $resursa['id'], $resursa['pozitie'], $resursa['cantitate']]);
                break;

            case 'utilaj':
                $stmt = $pdo->prepare("
                    INSERT INTO Link_Variante_Utilaj (codVarianta, codUtilaj, Pozitie, Cantitate)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$simbol, $resursa['id'], $resursa['pozitie'], $resursa['cantitate']]);
                break;
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Resursele au fost salvate cu succes'
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la salvarea resurselor: ' . $e->getMessage()
    ]);
} 
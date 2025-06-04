<?php
require_once 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['codVarianta'])) {
    echo json_encode(['success' => false, 'error' => 'Date invalide']);
    exit;
}

$codVarianta = $data['codVarianta'];

try {
    $pdo->beginTransaction();

    // Șterge resursele asociate
    $pdo->exec("DELETE FROM Link_Variante_Material WHERE codVarianta = " . $pdo->quote($codVarianta));
    $pdo->exec("DELETE FROM Link_Variante_Manopera WHERE codVarianta = " . $pdo->quote($codVarianta));
    $pdo->exec("DELETE FROM Link_Variante_Utilaj WHERE codVarianta = " . $pdo->quote($codVarianta));

    // Șterge norma din ArticoleVariante
    $stmt = $pdo->prepare("DELETE FROM ArticoleVariante WHERE codVarianta = ?");
    $stmt->execute([$codVarianta]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Norma nu a fost găsită în baza de date');
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 
<?php
// Afișează erorile pentru depanare
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Verifică dacă request-ul este de tip POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Metoda HTTP greșită. Utilizați POST.']);
    exit;
}

// Citește datele trimise ca JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Verifică dacă JSON-ul a fost parsat corect
if (json_last_error() !== JSON_ERROR_NONE) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Eroare la parsarea JSON: ' . json_last_error_msg(),
        'input' => substr($input, 0, 255) // Afișează primele 255 caractere din input pentru debugging
    ]);
    exit;
}

// Verifică dacă toate datele necesare sunt prezente
if (!isset($data['category']) || !isset($data['data']) || !is_array($data['data'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Date incomplete sau incorecte',
        'received' => $data
    ]);
    exit;
}

$category = $data['category'];

$tableMap = [
    'material' => 'Link_Variante_Material',
    'manopera' => 'Link_Variante_Manopera',
    'utilaj' => 'Link_Variante_Utilaj'
];

$codeColumnMap = [
    'material' => 'codMaterial',
    'manopera' => 'codManopera',
    'utilaj' => 'codUtilaj'
];

// Verifică dacă categoria este validă
if (!array_key_exists($category, $tableMap)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Categorie invalidă: ' . $category]);
    exit;
}

$table = $tableMap[$category];
$codeColumn = $codeColumnMap[$category];

try {
    $pdo->beginTransaction();
    
    $updateCount = 0;
    
    foreach ($data['data'] as $item) {
        // Verifică dacă itemul are codVarianta, codItem și cantitate
        if (!isset($item['codVarianta']) || !isset($item['codItem']) || !isset($item['cantitate'])) {
            continue;
        }
        
        $stmt = $pdo->prepare("UPDATE $table SET cantitate = ? WHERE codVarianta = ? AND $codeColumn = ?");
        $stmt->execute([$item['cantitate'], $item['codVarianta'], $item['codItem']]);
        $updateCount += $stmt->rowCount();
    }
    
    $pdo->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "Modificările au fost salvate cu succes. {$updateCount} rânduri actualizate."
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'error' => 'Eroare la salvarea modificărilor: ' . $e->getMessage()
    ]);
}
?> 
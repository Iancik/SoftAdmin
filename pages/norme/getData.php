<?php
// Afișează erorile pentru depanare
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Rută pentru debugging
if (isset($_GET['debug']) && $_GET['debug'] === 'tables') {
    try {
        header('Content-Type: application/json');
        
        // Verifică tabelele disponibile
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Structura tabelelor relevante
        $structure = [];
        
        foreach (['Link_Variante_Material', 'MaterialTehnologic', 'Link_Variante_Manopera', 'Manopera', 'Link_Variante_Utilaj', 'Utilaj'] as $tableName) {
            if (in_array($tableName, $tables)) {
                $stmt = $pdo->query("DESCRIBE $tableName");
                $structure[$tableName] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $structure[$tableName] = "Tabelul nu există";
            }
        }
        
        echo json_encode([
            'tables' => $tables,
            'structure' => $structure
        ]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Eroare bază de date: ' . $e->getMessage()]);
        exit;
    }
}

header('Content-Type: application/json');

$category = $_GET['category'] ?? '';
$codVarianta = $_GET['codVarianta'] ?? '';
$search = $_GET['search'] ?? '';

// Debugging
if (isset($_GET['debug'])) {
    try {
        $stmt = $pdo->query("DESCRIBE Manopera");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['debug' => 'Manopera columns', 'columns' => $columns]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => 'Debug error: ' . $e->getMessage()]);
        exit;
    }
}

if (empty($category)) {
    echo json_encode(['error' => 'Categoria este obligatorie']);
    exit;
}

try {
    $table = '';
    $codColumn = '';
    $sourceTable = '';
    $sourceColumn = '';
    $denumireColumn = '';
    
    switch ($category) {
        case 'material':
            $table = 'Link_Variante_Material';
            $codColumn = 'CodMaterial';
            $sourceTable = 'MaterialTehnologic';
            $sourceColumn = 'codMaterial';
            $denumireColumn = 'denumire';
            break;
        case 'manopera':
            $table = 'Link_Variante_Manopera';
            $codColumn = 'codManopera';
            $sourceTable = 'Manopera';
            $sourceColumn = 'codManopera';
            $denumireColumn = 'denumire';
            break;
        case 'utilaj':
            $table = 'Link_Variante_Utilaj';
            $codColumn = 'codUtilaj';
            $sourceTable = 'Utilaj';
            $sourceColumn = 'codUtilaj';
            $denumireColumn = 'denumire';
            break;
        default:
            throw new Exception('Categorie invalidă');
    }

    // Dacă avem un codVarianta, returnăm resursele asociate
    if (!empty($codVarianta)) {
        $stmt = $pdo->prepare("
            SELECT l.codVarianta, l.$codColumn as codItem, s.$denumireColumn as denumire, 
                   l.Pozitie as pozitie, l.Cantitate as cantitate
            FROM $table l
            JOIN $sourceTable s ON l.$codColumn = s.$sourceColumn
            WHERE l.codVarianta = ?
            ORDER BY l.Pozitie
        ");
        $stmt->execute([$codVarianta]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
    } 
    // Dacă avem un termen de căutare, căutăm în tabela sursă pentru dropdown
    else if (!empty($search)) {
        $stmt = $pdo->prepare("
            SELECT $sourceColumn as id, $denumireColumn as text
            FROM $sourceTable
            WHERE $denumireColumn LIKE ?
            ORDER BY $denumireColumn
            LIMIT 10
        ");
        $stmt->execute([$search . '%']);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format pentru Select2
        echo json_encode([
            'results' => $results
        ]);
    }
    // Altfel, returnăm toate resursele din tabela sursă pentru dropdown
    else {
        $stmt = $pdo->prepare("
            SELECT $sourceColumn as id, $denumireColumn as text
            FROM $sourceTable
            ORDER BY $denumireColumn
            LIMIT 10
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format pentru Select2
        echo json_encode([
            'results' => $results
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Enable error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Get JSON data
    $json = file_get_contents('php://input');
    error_log('JSON primit: ' . $json);
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Eroare la decodarea JSON: ' . json_last_error_msg());
    }

    if (!$data) {
        throw new Exception('Date invalide primite');
    }

    // Validate required fields
    $requiredFields = ['codVarianta', 'simbol', 'denumire', 'um', 'codArticol', 'codCapitol'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Câmpul obligatoriu '$field' lipsește");
        }
    }

    // Log received data
    error_log('Date primite: ' . print_r($data, true));

    // Start transaction
    $pdo->beginTransaction();

    // Insert into ArticoleVariante
    $stmt = $pdo->prepare("INSERT INTO ArticoleVariante (codVarianta, Simbol, Denumire, Um, codArticol, codCapitol, Data) 
                          VALUES (:codVarianta, :simbol, :denumire, :um, :codArticol, :codCapitol, :data)");
    
    $params = [
        ':codVarianta' => $data['codVarianta'],
        ':simbol' => $data['simbol'],
        ':denumire' => $data['denumire'],
        ':um' => $data['um'],
        ':codArticol' => $data['codArticol'],
        ':codCapitol' => $data['codCapitol'],
        ':data' => date('Y-m-d')
    ];
    
    error_log('Parametri pentru ArticoleVariante: ' . print_r($params, true));
    
    $result = $stmt->execute($params);
    if (!$result) {
        error_log('Eroare la inserarea în ArticoleVariante: ' . print_r($stmt->errorInfo(), true));
        throw new Exception('Eroare la inserarea în ArticoleVariante: ' . implode(', ', $stmt->errorInfo()));
    }

    // Log resources
    error_log('Resurse de procesat: ' . print_r($data['resurse'], true));

    // Insert resources
    if (!empty($data['resurse'])) {
        foreach ($data['resurse'] as $index => $resursa) {
            error_log("Procesare resursă #$index: " . print_r($resursa, true));

            // Validate resource data
            if (empty($resursa['tip']) || empty($resursa['id']) || empty($resursa['pozitie']) || empty($resursa['cantitate'])) {
                throw new Exception('Date incomplete pentru resursă: ' . print_r($resursa, true));
            }

            // Determine table and field based on resource type
            switch ($resursa['tip']) {
                case 'manopera':
                    $table = 'Link_Variante_Manopera';
                    $codField = 'codManopera';
                    break;
                case 'material':
                    $table = 'Link_Variante_Material';
                    $codField = 'CodMaterial';
                    break;
                case 'utilaj':
                    $table = 'Link_Variante_Utilaj';
                    $codField = 'codUtilaj';
                    break;
                default:
                    throw new Exception('Tip de resursă invalid: ' . $resursa['tip']);
            }

            // Prepare SQL with correct field names
            $sql = "INSERT INTO $table (codVarianta, $codField, Pozitie, Cantitate) 
                    VALUES (:codVarianta, :codResursa, :pozitie, :cantitate)";
            
            error_log("SQL pentru inserare în $table: " . $sql);
            
            $stmt = $pdo->prepare($sql);
            $params = [
                ':codVarianta' => $data['codVarianta'],
                ':codResursa' => $resursa['id'],
                ':pozitie' => $resursa['pozitie'],
                ':cantitate' => $resursa['cantitate']
            ];
            
            error_log("Parametri pentru inserare în $table: " . print_r($params, true));
            
            try {
                $result = $stmt->execute($params);
                if (!$result) {
                    error_log('Eroare la inserare: ' . print_r($stmt->errorInfo(), true));
                    throw new Exception('Eroare la inserarea resursei în tabela ' . $table . ': ' . implode(', ', $stmt->errorInfo()));
                }
                error_log("Inserare reușită în $table cu parametrii: " . print_r($params, true));
            } catch (PDOException $e) {
                error_log('Eroare PDO la inserare: ' . $e->getMessage());
                throw new Exception('Eroare la inserarea resursei în tabela ' . $table . ': ' . $e->getMessage());
            }
        }
    } else {
        error_log('Nu există resurse de procesat');
    }

    // Commit transaction
    $pdo->commit();
    error_log('Tranzacție finalizată cu succes');

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
        error_log('Tranzacție anulată din cauza erorii');
    }
    
    error_log('Eroare la salvarea normei: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la salvarea normei: ' . $e->getMessage()
    ]);
} 
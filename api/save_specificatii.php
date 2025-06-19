<?php
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    // Verifică dacă toate câmpurile necesare sunt prezente
    if (!isset($_POST['simbol']) || !isset($_POST['descriere'])) {
        throw new Exception('Toate câmpurile sunt obligatorii');
    }

    $simbol = trim($_POST['simbol']);
    $descriere = trim($_POST['descriere']);

    if (empty($simbol)) {
        throw new Exception('Câmpul Simbol este obligatoriu');
    }

    // Verifică dacă este editare sau adăugare nouă
    if (isset($_POST['cod'])) {
        // Editare
        $stmt = $pdo->prepare("UPDATE specificatii SET Simbol = ?, Descriere = ? WHERE codSpecificatie = ?");
        $stmt->execute([$simbol, $descriere, $_POST['cod']]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('Specificația nu a fost găsită');
        }
    } else {
        // Adăugare nouă
        $stmt = $pdo->prepare("INSERT INTO specificatii (Simbol, Descriere) VALUES (?, ?)");
        $stmt->execute([$simbol, $descriere]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Specificația a fost salvată cu succes'
    ]);

} catch (Exception $e) {
    error_log('Eroare la salvarea specificației: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
<?php
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

try {
    // Validare date
    if (empty($_POST['simbol'])) {
        throw new Exception('Simbolul este obligatoriu');
    }

    $cod = $_POST['cod'] ?? null;
    $simbol = trim($_POST['simbol']);
    $descriere = trim($_POST['descriere'] ?? '');

    // Verificăm dacă simbolul există deja (doar pentru adăugare nouă)
    if (!$cod) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM specificatii WHERE simbol = ?");
        $stmt->execute([$simbol]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Acest simbol există deja');
        }
    }

    if ($cod) {
        // Update
        $stmt = $pdo->prepare("UPDATE specificatii SET simbol = ?, descriere = ? WHERE codSpecificatie = ?");
        $stmt->execute([$simbol, $descriere, $cod]);
        $message = 'Specificația a fost actualizată cu succes';
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO specificatii (simbol, descriere) VALUES (?, ?)");
        $stmt->execute([$simbol, $descriere]);
        $message = 'Specificația a fost adăugată cu succes';
    }

    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 
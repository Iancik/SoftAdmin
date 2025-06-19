<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../config.php';

// Conectare la baza de date principală
try {
    $pdo_main = new PDO(
        "mysql:host=" . DB_MAIN_HOST . ";dbname=" . DB_MAIN_NAME . ";charset=utf8mb4",
        DB_MAIN_USER,
        DB_MAIN_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    error_log("Eroare conexiune baza de date: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Eroare conexiune baza de date']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cod'])) {
    try {
        // Verifică dacă înregistrarea există
        $check = $pdo_main->prepare("SELECT COUNT(*) FROM deviz_oferte WHERE CodArticol = ?");
        $check->execute([$_POST['cod']]);
        if ($check->fetchColumn() == 0) {
            throw new Exception("Prețul nu a fost găsit");
        }

        // Șterge înregistrarea
        $stmt = $pdo_main->prepare("DELETE FROM deviz_oferte WHERE CodArticol = ?");
        $stmt->execute([$_POST['cod']]);

        echo json_encode([
            'success' => true,
            'message' => 'Prețul a fost șters cu succes'
        ]);
    } catch (Exception $e) {
        error_log("Eroare la ștergere: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'A apărut o eroare la ștergere: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Metoda de request invalidă sau cod lipsă'
    ]);
} 
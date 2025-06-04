<?php
require_once '../../config.php';

header('Content-Type: application/json');

if (isset($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    try {
        $stmt = $pdo->prepare("SELECT id, denumire, pret FROM materialtehnologic WHERE denumire LIKE ? ORDER BY denumire LIMIT 10");
        $stmt->execute([$search]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($results);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Eroare la căutare: ' . $e->getMessage()]);
    }
} else {
    echo json_encode([]);
} 
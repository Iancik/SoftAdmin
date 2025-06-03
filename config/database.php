<?php
try {
    $host = '127.0.0.1';
    $dbname = 'norme';
    $username = 'root';
    $password = 'w2i1n4d3o5c2deviz';
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    error_log('Eroare conexiune bază de date: ' . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Eroare la conectarea la baza de date. Vă rugăm contactați administratorul.'
    ]);
    exit;
} 
<?php
$host = 'devize.md';
$dbname = 'norme_moldova';
$username = 'norme_moldova';
$password = 'norme_moldova_2014';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {

    if (filter_var($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', FILTER_SANITIZE_STRING) === 'XMLHttpRequest' 
        || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Eroare de conexiune la baza de date. Contactați administratorul.']);
    } else {
        echo "Eroare de conexiune la baza de date. Contactați administratorul.";
    }
    exit;
}
?> 
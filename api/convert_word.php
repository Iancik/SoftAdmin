<?php
// Dezactivează afișarea erorilor PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Asigură-te că toate erorile sunt prinse
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

header('Content-Type: application/json');

try {
    // Verifică dacă a fost încărcat un fișier
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Nu a fost încărcat niciun fișier sau a apărut o eroare la încărcare');
    }

    $file = $_FILES['file'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Verifică extensia fișierului
    if (!in_array($fileExtension, ['doc', 'docx'])) {
        throw new Exception('Format de fișier neacceptat. Sunt acceptate doar fișiere .doc și .docx');
    }

    // Creează un director temporar dacă nu există
    $tempDir = __DIR__ . '/../temp';
    if (!file_exists($tempDir)) {
        if (!mkdir($tempDir, 0777, true)) {
            throw new Exception('Nu s-a putut crea directorul temporar. Verificați permisiunile.');
        }
    }

    // Verifică permisiunile directorului
    if (!is_writable($tempDir)) {
        throw new Exception('Directorul temporar nu are permisiuni de scriere.');
    }

    // Salvează fișierul temporar
    $tempFile = $tempDir . '/' . uniqid() . '.' . $fileExtension;
    if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
        throw new Exception('Nu s-a putut salva fișierul temporar. Verificați permisiunile.');
    }

    try {
        // Încarcă documentul Word
        $phpWord = IOFactory::load($tempFile);
        
        // Extrage textul din document
        $html = '';
        $sections = $phpWord->getSections();
        
        foreach ($sections as $section) {
            $elements = $section->getElements();
            foreach ($elements as $element) {
                if (method_exists($element, 'getText')) {
                    $text = $element->getText();
                    if (!empty($text)) {
                        $html .= '<p>' . htmlspecialchars($text) . '</p>';
                    }
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $subElement) {
                        if (method_exists($subElement, 'getText')) {
                            $text = $subElement->getText();
                            if (!empty($text)) {
                                $html .= '<p>' . htmlspecialchars($text) . '</p>';
                            }
                        }
                    }
                }
            }
        }

        // Dacă nu s-a găsit text, încercă o altă metodă
        if (empty($html)) {
            // Încearcă să citești fișierul ca text simplu
            $content = file_get_contents($tempFile);
            if ($content !== false) {
                // Extrage textul din conținut
                $text = strip_tags($content);
                $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);
                
                if (!empty($text)) {
                    $html = '<p>' . htmlspecialchars($text) . '</p>';
                }
            }
        }

        if (empty($html)) {
            throw new Exception('Nu s-a putut extrage textul din fișierul Word.');
        }

    } catch (Exception $e) {
        throw new Exception('Eroare la procesarea fișierului Word: ' . $e->getMessage());
    }

    // Șterge fișierul temporar
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }

    // Returnează rezultatul
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    error_log('Eroare la conversia documentului: ' . $e->getMessage());
    // DEBUG: trimite eroarea direct ca text simplu
    header('Content-Type: text/plain');
    echo $e->getMessage();
    exit;
} 
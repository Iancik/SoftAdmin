<?php
require_once 'config.php';

header('Content-Type: application/json');

function getRealTimeRate() {
    try {
        // Using National Bank of Moldova API
        $url = 'https://www.bnm.md/ro/official_exchange_rates?get_xml=1&date=' . date('d.m.Y');
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('Error fetching data: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Parse XML response
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            throw new Exception('Error parsing XML response');
        }
        
        // Find EUR rate
        $eurRate = null;
        foreach ($xml->Valute as $valuta) {
            if ((string)$valuta->CharCode === 'EUR') {
                $eurRate = (float)$valuta->Value;
                break;
            }
        }
        
        if ($eurRate === null) {
            throw new Exception('EUR rate not found');
        }
        
        return [
            'success' => true,
            'rate' => $eurRate,
            'date' => date('Y-m-d'),
            'source' => 'BNM'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Get the real-time rate
$result = getRealTimeRate();
echo json_encode($result);
?> 
<?php
// Simple test script to check ticket/data JSON response
header('Content-Type: application/json');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/ticket/data');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'start' => 0,
    'length' => 10,
    'draw' => 1
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'http_code' => $httpCode,
    'response_length' => strlen($response),
    'is_json' => json_decode($response) !== null,
    'response' => $response
], JSON_PRETTY_PRINT);

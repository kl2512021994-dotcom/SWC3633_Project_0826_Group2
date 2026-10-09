<?php
function logRequest($statusCode = 200) {
    $logFile = __DIR__ . '/api_requests.log';
    $timestamp = date('Y-m-d H:i:s');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    $logMessage = sprintf("[%s] %s | Method: %s | URI: %s | Status: %d\n", $timestamp, $ip, $method, $uri, $statusCode);
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
?>

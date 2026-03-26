<?php
require_once __DIR__ . '/../config/database.php';

function sendRequest($method, $url, $headers = [], $body = '') {
    $ch = curl_init();

    // Build headers array
    $curlHeaders = [];
    foreach ($headers as $key => $value) {
        $curlHeaders[] = "$key: $value";
    }

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => $curlHeaders,
        CURLOPT_HEADER         => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'API-Tester/1.0 (PHP cURL)',
    ]);

    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        case 'HEAD':
            curl_setopt($ch, CURLOPT_NOBODY, true);
            break;
        default:
            curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $startTime = microtime(true);
    $rawResponse = curl_exec($ch);
    $endTime = microtime(true);
    $responseTimeMs = round(($endTime - $startTime) * 1000);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [
            'success'          => false,
            'error'            => $error,
            'status'           => 0,
            'response_headers' => [],
            'body'             => '',
            'time_ms'          => $responseTimeMs,
        ];
    }

    $rawHeaders = substr($rawResponse, 0, $headerSize);
    $body       = substr($rawResponse, $headerSize);

    // Parse response headers
    $responseHeaders = [];
    foreach (explode("\r\n", $rawHeaders) as $line) {
        if (strpos($line, ':') !== false) {
            [$key, $val] = explode(':', $line, 2);
            $responseHeaders[trim($key)] = trim($val);
        }
    }

    $contentType = $responseHeaders['Content-Type'] ?? $responseHeaders['content-type'] ?? '';

    return [
        'success'          => true,
        'status'           => $httpCode,
        'response_headers' => $responseHeaders,
        'body'             => $body,
        'time_ms'          => $responseTimeMs,
        'content_type'     => $contentType,
    ];
}

function logRequest($method, $url, $reqHeaders, $reqBody, $result) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO request_logs 
        (method, url, request_headers, request_body, response_status, response_headers, response_body, response_time_ms, content_type)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        strtoupper($method),
        $url,
        json_encode($reqHeaders),
        $reqBody,
        $result['status'] ?? 0,
        json_encode($result['response_headers'] ?? []),
        $result['body'] ?? '',
        $result['time_ms'] ?? 0,
        $result['content_type'] ?? '',
    ]);
    return $db->lastInsertId();
}

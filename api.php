<?php
require_once __DIR__ . '/src/request.php';
require_once __DIR__ . '/src/collections.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'send_request':
        $method  = strtoupper(trim($_POST['method'] ?? 'GET'));
        $url     = trim($_POST['url'] ?? '');
        $body    = trim($_POST['body'] ?? '');
        $headersRaw = trim($_POST['headers'] ?? '{}');

        if (empty($url)) {
            echo json_encode(['success' => false, 'error' => 'URL is required']);
            exit;
        }

        $headers = [];
        $decoded = json_decode($headersRaw, true);
        if (is_array($decoded)) {
            $headers = $decoded;
        }

        $result = sendRequest($method, $url, $headers, $body);
        logRequest($method, $url, $headers, $body, $result);

        // Try to pretty-print JSON response body
        $jsonBody = json_decode($result['body']);
        if (json_last_error() === JSON_ERROR_NONE) {
            $result['body_formatted'] = json_encode($jsonBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $result['is_json'] = true;
        } else {
            $result['body_formatted'] = $result['body'];
            $result['is_json'] = false;
        }

        echo json_encode($result);
        break;

    case 'get_history':
        $db    = getDB();
        $limit = (int)($_GET['limit'] ?? 50);
        $method_filter = $_GET['method'] ?? '';
        $status_filter = $_GET['status'] ?? '';

        $where  = [];
        $params = [];

        if ($method_filter && $method_filter !== 'ALL') {
            $where[]  = "method = ?";
            $params[] = $method_filter;
        }
        if ($status_filter === '2xx') {
            $where[]  = "response_status BETWEEN 200 AND 299";
        } elseif ($status_filter === '4xx') {
            $where[]  = "response_status BETWEEN 400 AND 499";
        } elseif ($status_filter === '5xx') {
            $where[]  = "response_status BETWEEN 500 AND 599";
        }

        $sql = "SELECT id, method, url, response_status, response_time_ms, content_type, created_at FROM request_logs";
        if ($where) $sql .= " WHERE " . implode(" AND ", $where);
        $sql .= " ORDER BY created_at DESC LIMIT $limit";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
        break;

    case 'get_log_detail':
        $id   = (int)($_GET['id'] ?? 0);
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM request_logs WHERE id = ?");
        $stmt->execute([$id]);
        $row  = $stmt->fetch();
        if ($row) {
            $json = json_decode($row['response_body']);
            $row['body_formatted'] = (json_last_error() === JSON_ERROR_NONE)
                ? json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : $row['response_body'];
            $row['is_json'] = (json_last_error() === JSON_ERROR_NONE);
        }
        echo json_encode($row);
        break;

    case 'get_collections':
        echo json_encode(getAllCollections());
        break;

    case 'save_collection':
        $name    = trim($_POST['name'] ?? '');
        $desc    = trim($_POST['description'] ?? '');
        $method  = trim($_POST['method'] ?? 'GET');
        $url     = trim($_POST['url'] ?? '');
        $headers = trim($_POST['headers'] ?? '{}');
        $body    = trim($_POST['body'] ?? '');

        if (!$name || !$url) {
            echo json_encode(['success' => false, 'error' => 'Name and URL are required']);
            exit;
        }
        $id = saveCollection($name, $desc, $method, $url, $headers, $body);
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    case 'delete_collection':
        $id = (int)($_POST['id'] ?? 0);
        echo json_encode(['success' => deleteCollection($id)]);
        break;

    case 'clear_history':
        $db = getDB();
        $db->exec("DELETE FROM request_logs");
        echo json_encode(['success' => true]);
        break;

    case 'export_history':
        $db   = getDB();
        $rows = $db->query("SELECT method, url, response_status, response_time_ms, created_at FROM request_logs ORDER BY created_at DESC")->fetchAll();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="api_history_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Method', 'URL', 'Status', 'Time (ms)', 'Date']);
        foreach ($rows as $r) fputcsv($out, $r);
        fclose($out);
        exit;

    default:
        echo json_encode(['error' => 'Unknown action']);
}

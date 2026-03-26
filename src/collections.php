<?php
require_once __DIR__ . '/../config/database.php';

function getAllCollections() {
    $db = getDB();
    return $db->query("SELECT * FROM collections ORDER BY created_at DESC")->fetchAll();
}

function saveCollection($name, $description, $method, $url, $headers, $body) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO collections (name, description, method, url, request_headers, request_body) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$name, $description, strtoupper($method), $url, $headers, $body]);
    return $db->lastInsertId();
}

function deleteCollection($id) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM collections WHERE id = ?");
    return $stmt->execute([(int)$id]);
}

function getCollection($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM collections WHERE id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

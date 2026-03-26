-- API Tester & Request Logger Database Schema
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS api_tester CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE api_tester;

CREATE TABLE IF NOT EXISTS request_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method VARCHAR(10) NOT NULL,
    url TEXT NOT NULL,
    request_headers TEXT,
    request_body TEXT,
    response_status INT,
    response_headers TEXT,
    response_body LONGTEXT,
    response_time_ms INT,
    content_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    method VARCHAR(10) NOT NULL,
    url TEXT NOT NULL,
    request_headers TEXT,
    request_body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample collection entries
INSERT INTO collections (name, description, method, url, request_headers, request_body) VALUES
('JSONPlaceholder - Get Posts', 'Fetch all posts from JSONPlaceholder', 'GET', 'https://jsonplaceholder.typicode.com/posts', '{"Content-Type": "application/json"}', ''),
('JSONPlaceholder - Create Post', 'Create a new post', 'POST', 'https://jsonplaceholder.typicode.com/posts', '{"Content-Type": "application/json"}', '{"title": "foo", "body": "bar", "userId": 1}'),
('HTTPBin - Get Request Info', 'Inspect your request details', 'GET', 'https://httpbin.org/get', '{}', '');

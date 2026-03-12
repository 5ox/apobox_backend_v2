<?php

// Lightweight health check — bypass Laravel entirely to avoid session/Redis deps
if ($_SERVER['REQUEST_URI'] === '/health') {
    header('Content-Type: application/json');
    try {
        $host = $_ENV['DB_HOST'] ?? $_ENV['MYSQLHOST'] ?? '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? $_ENV['MYSQLPORT'] ?? '3306';
        $db   = $_ENV['DB_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? '';
        $user = $_ENV['DB_USERNAME'] ?? $_ENV['MYSQLUSER'] ?? '';
        $pass = $_ENV['DB_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? '';
        new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [
            PDO::ATTR_TIMEOUT => 3,
        ]);
        echo json_encode(['status' => 'ok', 'database' => 'ok']);
    } catch (Throwable $e) {
        http_response_code(503);
        echo json_encode(['status' => 'degraded', 'database' => 'error']);
    }
    exit;
}

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());

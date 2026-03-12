<?php

// Temporary debug endpoint — shows last 50 lines of Laravel log
if ($_SERVER['REQUEST_URI'] === '/debug-log') {
    header('Content-Type: text/plain');
    $logFile = __DIR__ . '/../storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        echo implode('', array_slice($lines, -50));
    } else {
        echo 'No log file found. Checking stderr...';
    }
    exit;
}

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
        $result = ['status' => 'ok', 'database' => 'ok'];
    } catch (Throwable $e) {
        http_response_code(503);
        $result = ['status' => 'degraded', 'database' => 'error', 'error' => $e->getMessage()];
    }
    // Append last 30 lines of Laravel log for debugging
    $logFile = __DIR__ . '/../storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $result['log_tail'] = array_values(array_slice($lines, -30));
    } else {
        $result['log_tail'] = ['No log file found'];
    }
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

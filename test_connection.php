<?php
/**
 * API Endpoint for Checking Database Connection Status
 * Western Mindanao State University - College of Computing Studies
 * Reference: 01-PHP-MySQL-Database-Connection.pdf (Pages 6, 7)
 */
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$dbPaths = [
    __DIR__ . '/database.php',
    __DIR__ . '/../api/database.php',
    __DIR__ . '/../config/database.php'
];
foreach ($dbPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        break;
    }
}

$testPaths = [
    __DIR__ . '/../class/DbTest.php',
    __DIR__ . '/class/DbTest.php',
    __DIR__ . '/DbTest.php'
];
foreach ($testPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        break;
    }
}

$database = new Database();
$db = $database->getConnection();

if ($db) {
    $test = new DbTest($db);
    $response = $test->checkConnection();

    // Enrich with server version & table verification
    try {
        $version = $db->query('SELECT VERSION()')->fetchColumn();
        $count = $db->query('SELECT COUNT(*) FROM employees')->fetchColumn();
        $response['database'] = $database->getDbName();
        $response['server_version'] = $version;
        $response['total_employees'] = intval($count);
    } catch (Exception $e) {
        // Table may not yet be initialized
    }
} else {
    $response = [
        "status" => "error",
        "message" => "Failed to connect to database"
    ];
}

echo json_encode($response);

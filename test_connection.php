<?php
/**
 * Database Connection Test Endpoint
 * Pings MySQL database via PDO and returns connection status JSON.
 */
error_reporting(E_ALL);
ini_set('display_errors', '0');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
} elseif (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $stmt = $db->query("SELECT COUNT(*) AS total FROM employees");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalEmployees = $row ? intval($row['total']) : 0;
        
        $serverVersion = $db->getAttribute(PDO::ATTR_SERVER_VERSION);

        http_response_code(200);
        echo json_encode([
            "success" => true,
            "status" => "success",
            "message" => "Database connection successful",
            "database" => "employee_db",
            "server_version" => $serverVersion,
            "table_verified" => "employees",
            "total_employees" => $totalEmployees,
            "timestamp" => date("Y-m-d H:i:s")
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "status" => "error",
            "message" => "Failed to establish PDO connection."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "status" => "error",
        "message" => "Database connection exception: " . $e->getMessage()
    ]);
}

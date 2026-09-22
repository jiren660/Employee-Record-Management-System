<?php
/**
 * RESTful API Controller for Employee Operations
 * Handles GET, POST, PUT, DELETE requests with clean JSON responses.
 */

// Enable Error Reporting (Log only, suppress display to prevent corrupting JSON output)
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Set JSON and CORS Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include Database Connection
if (file_exists(__DIR__ . '/database.php')) {
    require_once __DIR__ . '/database.php';
} elseif (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
}

// Include Employee Model
if (file_exists(__DIR__ . '/Employee.php')) {
    require_once __DIR__ . '/Employee.php';
} elseif (file_exists(__DIR__ . '/class/Employee.php')) {
    require_once __DIR__ . '/class/Employee.php';
} elseif (file_exists(__DIR__ . '/../class/Employee.php')) {
    require_once __DIR__ . '/../class/Employee.php';
} elseif (file_exists(__DIR__ . '/models/Employee.php')) {
    require_once __DIR__ . '/models/Employee.php';
}

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception("Could not connect to database.");
    }
    $employee = new Employee($db);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "status" => "error",
        "message" => "Database connection failed: " . $e->getMessage()
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $record = $employee->getById($id);
            if ($record) {
                http_response_code(200);
                echo json_encode([
                    "success" => true,
                    "status" => "success",
                    "data" => $record
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    "success" => false,
                    "status" => "error",
                    "message" => "Employee with ID $id not found."
                ]);
            }
        } else {
            $stmt = $employee->getAll();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "status" => "success",
                "count" => count($employees),
                "data" => $employees
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            $data = $_POST;
        }

        if (empty($data['name']) || empty($data['email']) || empty($data['position'])) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "status" => "error",
                "message" => "Incomplete data. Name, Email, and Position are required."
            ]);
            break;
        }

        $employee->name = $data['name'];
        $employee->email = $data['email'];
        $employee->position = $data['position'];
        $employee->department = isset($data['department']) ? $data['department'] : 'General';
        $employee->salary = isset($data['salary']) ? $data['salary'] : 0;

        if ($employee->create()) {
            http_response_code(201);
            echo json_encode([
                "success" => true,
                "status" => "success",
                "message" => "Employee created successfully.",
                "id" => $employee->id
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "status" => "error",
                "message" => "Unable to create employee."
            ]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['id']) || empty($data['name']) || empty($data['email']) || empty($data['position'])) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "status" => "error",
                "message" => "Incomplete data. ID, Name, Email, and Position are required."
            ]);
            break;
        }

        $employee->id = $data['id'];
        $employee->name = $data['name'];
        $employee->email = $data['email'];
        $employee->position = $data['position'];
        $employee->department = isset($data['department']) ? $data['department'] : 'General';
        $employee->salary = isset($data['salary']) ? $data['salary'] : 0;

        if ($employee->update()) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "status" => "success",
                "message" => "Employee updated successfully."
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "status" => "error",
                "message" => "Unable to update employee."
            ]);
        }
        break;

    case 'DELETE':
        $id = null;
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
            if (isset($data['id'])) {
                $id = intval($data['id']);
            }
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode([
                "success" => false,
                "status" => "error",
                "message" => "Employee ID is required for deletion."
            ]);
            break;
        }

        if ($employee->delete($id)) {
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "status" => "success",
                "message" => "Employee deleted successfully."
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "status" => "error",
                "message" => "Unable to delete employee."
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode([
            "success" => false,
            "status" => "error",
            "message" => "Method not allowed."
        ]);
        break;
}

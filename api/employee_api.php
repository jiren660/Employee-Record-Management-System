<?php
/**
 * RESTful Employee API Controller
 * Western Mindanao State University - College of Computing Studies
 * Reference: 02-Application-Programming-Interface-API.pdf, 03-Simple-Web-App, 04-Testing-API
 */
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, API-Token");

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Locate database.php and Employee.php
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

$empPaths = [
    __DIR__ . '/../class/Employee.php',
    __DIR__ . '/class/Employee.php',
    __DIR__ . '/Employee.php'
];
foreach ($empPaths as $p) {
    if (file_exists($p)) {
        require_once $p;
        break;
    }
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed."]);
    exit;
}

$emp = new Employee($db);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = intval($_GET['id']);
            $employee = $emp->getEmployeeById($id);
            if ($employee) {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "employee" => $employee,
                    "data" => $employee
                ]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Employee with ID {$id} not found."]);
            }
        } elseif (isset($_GET['search']) && !empty($_GET['search'])) {
            $keyword = trim($_GET['search']);
            $employees = $emp->searchEmployees($keyword);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "employee" => $employees,
                "data" => $employees,
                "count" => count($employees)
            ]);
        } elseif (isset($_GET['gender']) && !empty($_GET['gender'])) {
            $gender = trim($_GET['gender']);
            $employees = $emp->filterByGender($gender);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "employee" => $employees,
                "data" => $employees,
                "count" => count($employees)
            ]);
        } elseif (isset($_GET['job_title']) && !empty($_GET['job_title'])) {
            $jobTitle = trim($_GET['job_title']);
            $employees = $emp->filterByJobTitle($jobTitle);
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "employee" => $employees,
                "data" => $employees,
                "count" => count($employees)
            ]);
        } else {
            $emps = $emp->getAllEmployees();
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "employee" => $emps,
                "data" => $emps,
                "count" => count($emps)
            ]);
        }
        break;

    case 'POST':
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload or empty request."]);
            exit;
        }

        // Support DELETE override via POST
        if ((isset($data['_method']) && strtoupper($data['_method']) === 'DELETE') || (isset($data['action']) && $data['action'] === 'delete')) {
            $delId = intval($data['id'] ?? ($_GET['id'] ?? 0));
            if (!$delId) {
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid or missing employee ID."]);
                exit;
            }
            $existing = $emp->getEmployeeById($delId);
            if (!$existing) {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Employee with ID {$delId} not found."]);
                exit;
            }
            $result = $emp->deleteEmployee($delId);
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Employee deleted successfully!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Failed to delete employee record."]);
            }
            exit;
        }

        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $middleInitial = strtoupper(trim($data['middle_initial'] ?? ''));
        $mobileNumber = trim($data['mobile_number'] ?? '');
        $email = trim($data['email'] ?? '');
        $sex = trim($data['sex'] ?? '');
        $jobTitle = trim($data['job_title'] ?? '');

        // Validation: First Name
        if (empty($firstName)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "first_name", "message" => "First Name must not be empty."]);
            exit;
        }

        // Validation: Last Name
        if (empty($lastName)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "last_name", "message" => "Last Name must not be empty."]);
            exit;
        }

        // Validation: Middle Initial (Optional, exactly 1 char VARCHAR(1))
        if (!empty($middleInitial) && mb_strlen($middleInitial) > 1) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "middle_initial", "message" => "Middle Initial must be 1 character only (e.g. A)."]);
            exit;
        }

        // Validation: Email
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "email", "message" => "Email address must not be empty."]);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "email", "message" => "Email must be in a valid format (e.g., user@example.com)."]);
            exit;
        }

        // Validation: Mobile Number
        if (empty($mobileNumber)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "mobile_number", "message" => "Mobile number must not be empty."]);
            exit;
        }
        $cleanMobile = preg_replace('/[^0-9+]/', '', $mobileNumber);
        if (strlen($cleanMobile) < 10 || strlen($cleanMobile) > 15) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "mobile_number", "message" => "Mobile number must be a valid 10-15 digit phone number (e.g. 09123456789)."]);
            exit;
        }

        // Validation: Gender
        if (empty($sex) || !in_array($sex, ['Male', 'Female'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "sex", "message" => "Please select a valid gender (Male or Female)."]);
            exit;
        }

        // Validation: Job Title
        if (empty($jobTitle) || $jobTitle === 'Select Job Title') {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "job_title", "message" => "Please select a valid job title."]);
            exit;
        }

        try {
            $result = $emp->addEmployee($firstName, $lastName, $middleInitial, $mobileNumber, $email, $sex, $jobTitle);
            if ($result) {
                http_response_code(201);
                echo json_encode([
                    "status" => "success",
                    "message" => "Employee added successfully!",
                    "id" => $emp->getLastInsertId()
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Failed to add employee record into database."]);
            }
        } catch (PDOException $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate') !== false) {
                if (strpos($msg, 'email') !== false) {
                    $msg = "An employee with this email address already exists.";
                } elseif (strpos($msg, 'mobile_number') !== false) {
                    $msg = "An employee with this mobile number already exists.";
                } else {
                    $msg = "Duplicate entry detected. Email and mobile number must be unique.";
                }
            }
            echo json_encode(["status" => "error", "message" => $msg]);
        }
        break;

    case 'PUT':
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);

        if (!$data) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON payload or empty request."]);
            exit;
        }

        $id = intval($data['id'] ?? 0);
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $middleInitial = strtoupper(trim($data['middle_initial'] ?? ''));
        $mobileNumber = trim($data['mobile_number'] ?? '');
        $email = trim($data['email'] ?? '');
        $sex = trim($data['sex'] ?? '');
        $jobTitle = trim($data['job_title'] ?? '');

        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Employee ID is required for updating."]);
            exit;
        }

        // Validation: First Name
        if (empty($firstName)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "first_name", "message" => "First Name must not be empty."]);
            exit;
        }

        // Validation: Last Name
        if (empty($lastName)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "last_name", "message" => "Last Name must not be empty."]);
            exit;
        }

        // Validation: Middle Initial (Optional, exactly 1 char VARCHAR(1))
        if (!empty($middleInitial) && mb_strlen($middleInitial) > 1) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "middle_initial", "message" => "Middle Initial must be 1 character only (e.g. A)."]);
            exit;
        }

        // Validation: Email
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "email", "message" => "Email address must not be empty."]);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "email", "message" => "Email must be in a valid format (e.g., user@example.com)."]);
            exit;
        }

        // Validation: Mobile Number
        if (empty($mobileNumber)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "mobile_number", "message" => "Mobile number must not be empty."]);
            exit;
        }
        $cleanMobile = preg_replace('/[^0-9+]/', '', $mobileNumber);
        if (strlen($cleanMobile) < 10 || strlen($cleanMobile) > 15) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "mobile_number", "message" => "Mobile number must be a valid 10-15 digit phone number (e.g. 09123456789)."]);
            exit;
        }

        // Validation: Gender
        if (empty($sex) || !in_array($sex, ['Male', 'Female'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "sex", "message" => "Please select a valid gender (Male or Female)."]);
            exit;
        }

        // Validation: Job Title
        if (empty($jobTitle) || $jobTitle === 'Select Job Title') {
            http_response_code(400);
            echo json_encode(["status" => "error", "field" => "job_title", "message" => "Please select a valid job title."]);
            exit;
        }

        try {
            $result = $emp->updateEmployee($id, $firstName, $lastName, $middleInitial, $mobileNumber, $email, $sex, $jobTitle);
            if ($result) {
                http_response_code(200);
                echo json_encode([
                    "status" => "success",
                    "message" => "Employee updated successfully!"
                ]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Failed to update employee."]);
            }
        } catch (PDOException $e) {
            http_response_code(400);
            $msg = $e->getMessage();
            if (strpos($msg, 'Duplicate') !== false) {
                if (strpos($msg, 'email') !== false) {
                    $msg = "Another employee is already registered with this email address.";
                } elseif (strpos($msg, 'mobile_number') !== false) {
                    $msg = "Another employee is already registered with this mobile number.";
                } else {
                    $msg = "Duplicate entry detected. Email and mobile number must be unique.";
                }
            }
            echo json_encode(["status" => "error", "message" => $msg]);
        }
        break;

    case 'DELETE':
        $id = null;
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $id = intval($_GET['id']);
        } else {
            $raw = file_get_contents("php://input");
            $data = json_decode($raw, true);
            if (isset($data['id'])) {
                $id = intval($data['id']);
            }
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid or missing employee ID."]);
            exit;
        }

        // Check if employee exists
        $existing = $emp->getEmployeeById($id);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Employee with ID {$id} not found."]);
            exit;
        }

        $result = $emp->deleteEmployee($id);
        if ($result) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Employee deleted successfully!"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete employee record."]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Invalid request method."]);
        break;
}

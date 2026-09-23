# Western Mindanao State University — College of Computing Studies
## ADS133: Simple Web Application Development with MySQL Database Connection & API Implementation
**Course Instructor:** Mr. Jason A. Catadman, MIT

---

## Project Structure Overview (PDF 3 Page 2)

```
ADS133/
├── api/
│   ├── database.php          # Handles database connection using PDO with fallback
│   ├── employee_api.php      # Main RESTful API endpoint for CRUD operations
│   └── test_connection.php   # API endpoint for checking database connection
├── class/
│   ├── DbTest.php            # Class to test database connection status
│   └── Employee.php          # Employee model class defining CRUD methods
├── employees/
│   ├── employee.js           # Handles frontend AJAX interactions and Fetch API calls
│   └── index.php             # Main GUI (HTML + PHP) for managing employee records
├── javascript/
│   └── functions.js          # Reusable JavaScript helper and validation functions
├── style/
│   └── style.css             # 100% Pure Vanilla CSS styles for the web application
├── sql/
│   └── database.sql          # MySQL database schema and 15 sample seed records
├── postman/
│   └── Employee_API.postman_collection.json # Complete Postman test collection
├── tests/
│   └── api_test.php          # Automated PHP curl-based test suite for all endpoints
├── index.php                 # Root application entry point
└── README.md                 # Complete documentation and setup guide
```

---

## Module 1: Database Setup & PDO Connection (`01-PHP-MySQL-Database-Connection.pdf`)

### 1. Database & Table Creation
Run the provided SQL script (`sql/database.sql`) in MySQL CLI or phpMyAdmin:
```sql
CREATE DATABASE IF NOT EXISTS `employee_db`;
USE `employee_db`;

CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `middle_initial` CHAR(1) DEFAULT NULL,
  `mobile_number` VARCHAR(15) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `sex` ENUM('Male', 'Female') NOT NULL,
  `job_title` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Testing Database Connection
- **In Browser:** `http://localhost/ADS133/api/test_connection.php`
- **Expected JSON Output:**
```json
{
  "status": "success",
  "message": "Database connected successfully",
  "database": "employee_db",
  "server_version": "10.6.25-MariaDB",
  "total_employees": 15
}
```

---

## Module 2: RESTful API Endpoints (`02-Application-Programming-Interface-API.pdf`)

| Method | Endpoint | Description | Sample Payload |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/employee_api.php` | Retrieve all employees | N/A |
| `GET` | `/api/employee_api.php?id={id}` | Retrieve single employee by ID | N/A |
| `POST` | `/api/employee_api.php` | Add a new employee | `{"first_name": "Alice", "middle_initial": "M", "last_name": "Brown", "mobile_number": "09123456789", "email": "alice@example.com", "sex": "Female", "job_title": "Fullstack Software Engineer"}` |
| `PUT` | `/api/employee_api.php` | Update existing employee | `{"id": 1, "first_name": "John", "middle_initial": "A", "last_name": "Doe", "mobile_number": "09123456701", "email": "john.doe@example.com", "sex": "Male", "job_title": "Project Manager"}` |
| `DELETE` | `/api/employee_api.php?id={id}` | Delete employee by ID | N/A |

---

## Module 3: Frontend Web Application (`03-Simple-Web-Application-Development-with-API-implementation.pdf`)

1. **Header & Navigation Bar**:
   - Dark navigation bar (`#212529`) displaying `Management Information System` and menu links (`Departments`, `Employees`, `Products`, `Orders`).
2. **Database Status Banner**:
   - Sub-header banner reporting `Database Connection Status: Database Connected Successfully` with live ping button.
3. **Employee Directory Card**:
   - Centered white card with `Employee List` title, record counter, and green `+Add Employee` button (`#28a745`).
   - Live `Search by Name...` input.
   - Filter dropdowns by `Gender` and `Job Title`.
4. **Data Table**:
   - Signature bright blue table header (`#007bff`) with white text.
   - 9 columns: `ID`, `First Name`, `M.I.`, `Last Name`, `Mobile`, `Email`, `Sex`, `Job Title`, `Actions`.
   - Distinct **Edit** (blue `#007bff`) and **Delete** (red `#dc3545`) action buttons.
5. **Modals**:
   - Centered 2-column form grids for adding and updating employees.
   - Full-width blue action buttons (`Save Employee Details` / `Save Changes`).

---

## Module 4: API Testing (`04-Testing-the-Web-App-API.pdf`)

### 1. Using Postman
Import the included collection from `postman/Employee_API.postman_collection.json`. It includes pre-configured requests for all 5 CRUD operations and database connection checks.

### 2. Using Automated PHP CLI Test Suite
Run the test script from the terminal:
```bash
php tests/api_test.php
```
This executes all 5 REST operations sequentially via cURL and verifies HTTP status codes (200, 201, 404).

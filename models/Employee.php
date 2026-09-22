<?php
/**
 * Employee Model Class
 * Handles MySQL database CRUD operations using PDO
 */
if (!class_exists('Employee')) {
    class Employee {
        private $conn;
        private $table_name = "employees";

        public $id;
        public $name;
        public $email;
        public $position;
        public $department;
        public $salary;
        public $created_at;
        public $updated_at;

        public function __construct($db) {
            $this->conn = $db;
        }

        // Get All Employees
        public function getAll() {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        }

        // Get Single Employee by ID
        public function getById($id) {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->position = $row['position'];
                $this->department = isset($row['department']) ? $row['department'] : 'General';
                $this->salary = $row['salary'];
                $this->created_at = isset($row['created_at']) ? $row['created_at'] : null;
                $this->updated_at = isset($row['updated_at']) ? $row['updated_at'] : null;
                return $row;
            }
            return false;
        }

        // Create New Employee
        public function create() {
            $query = "INSERT INTO " . $this->table_name . " 
                      (name, email, position, department, salary) 
                      VALUES (:name, :email, :position, :department, :salary)";
            $stmt = $this->conn->prepare($query);

            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->position = htmlspecialchars(strip_tags($this->position));
            $this->department = htmlspecialchars(strip_tags($this->department));
            $this->salary = floatval($this->salary);

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':position', $this->position);
            $stmt->bindParam(':department', $this->department);
            $stmt->bindParam(':salary', $this->salary);

            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        }

        // Update Employee Record
        public function update() {
            $query = "UPDATE " . $this->table_name . " 
                      SET name = :name, email = :email, position = :position, department = :department, salary = :salary 
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);

            $this->id = intval($this->id);
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->email = htmlspecialchars(strip_tags($this->email));
            $this->position = htmlspecialchars(strip_tags($this->position));
            $this->department = htmlspecialchars(strip_tags($this->department));
            $this->salary = floatval($this->salary);

            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':position', $this->position);
            $stmt->bindParam(':department', $this->department);
            $stmt->bindParam(':salary', $this->salary);

            return $stmt->execute();
        }

        // Delete Employee Record
        public function delete($id) {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }
}

<?php
/**
 * Employee Model Class for CRUD Operations
 * Western Mindanao State University - College of Computing Studies
 * Reference: 03-Simple-Web-Application-Development-with-API-implementation.pdf (Pages 4, 14, 17, 20, 22)
 */
if (!class_exists('Employee')) {
    class Employee {
        private $conn;
        private $table = "employees";

        public function __construct($db) {
            $this->conn = $db;
        }

        /**
         * Fetch all employees
         *
         * @return array
         */
        public function getAllEmployees() {
            $query = "SELECT id, first_name, last_name, middle_initial, mobile_number, email, sex, job_title, created_at, updated_at FROM " . $this->table . " ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Fetch employee by ID
         *
         * @param int $id
         * @return array|false
         */
        public function getEmployeeById($id) {
            $query = "SELECT id, first_name, last_name, middle_initial, mobile_number, email, sex, job_title, created_at, updated_at FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        /**
         * Add a new employee
         *
         * @return bool
         */
        public function addEmployee($first_name, $last_name, $middle_initial, $mobile_number, $email, $sex, $job_title) {
            $query = "INSERT INTO " . $this->table . " (first_name, last_name, middle_initial, mobile_number, email, sex, job_title) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$first_name, $last_name, $middle_initial, $mobile_number, $email, $sex, $job_title]);
        }

        /**
         * Update employee details
         *
         * @return bool
         */
        public function updateEmployee($id, $first_name, $last_name, $middle_initial, $mobile_number, $email, $sex, $job_title) {
            $query = "UPDATE " . $this->table . " SET first_name = ?, last_name = ?, middle_initial = ?, mobile_number = ?, email = ?, sex = ?, job_title = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$first_name, $last_name, $middle_initial, $mobile_number, $email, $sex, $job_title, $id]);
        }

        /**
         * Delete employee
         *
         * @param int $id
         * @return bool
         */
        public function deleteEmployee($id) {
            $query = "DELETE FROM " . $this->table . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        }

        /**
         * Get last inserted ID
         *
         * @return string
         */
        public function getLastInsertId() {
            return $this->conn->lastInsertId();
        }
    }
}

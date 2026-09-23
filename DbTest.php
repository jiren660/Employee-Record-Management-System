<?php
/**
 * Database Connection Test Class
 * Western Mindanao State University - College of Computing Studies
 * Reference: 01-PHP-MySQL-Database-Connection.pdf (Page 6)
 */
if (!class_exists('DbTest')) {
    class DbTest {
        private $conn;

        public function __construct($db) {
            $this->conn = $db;
        }

        public function checkConnection() {
            if ($this->conn) {
                return [
                    "status" => "success",
                    "message" => "Database connected successfully"
                ];
            } else {
                return [
                    "status" => "error",
                    "message" => "Failed to connect to database"
                ];
            }
        }
    }
}

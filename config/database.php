<?php
/**
 * Database Connection Class
 * Western Mindanao State University - College of Computing Studies
 * Handles PDO connection to MySQL database with automatic credential and DB name fallback.
 */
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";
        private $db_name = "employee_db";
        private $username = "root";
        private $password = "";
        private $port = "3306";
        public $conn = null;

        /**
         * Establish and return the PDO database connection
         *
         * @return PDO|null
         */
        public function getConnection() {
            $this->conn = null;

            // Supported database names and passwords
            $databases = ["employee_db", "test_connection_db", "test_db"];
            $passwords = ["", "123456"];
            $lastException = null;

            foreach ($databases as $dbName) {
                foreach ($passwords as $pwd) {
                    try {
                        $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $dbName . ";charset=utf8mb4";
                        $this->conn = new PDO($dsn, $this->username, $pwd, [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                        ]);
                        $this->db_name = $dbName;
                        $this->password = $pwd;
                        return $this->conn;
                    } catch (PDOException $e) {
                        $lastException = $e;
                    }
                }
            }

            if ($lastException) {
                // If specific database doesn't exist, try connecting to MySQL server directly and creating it
                try {
                    $pdo = new PDO("mysql:host=" . $this->host . ";port=" . $this->port, $this->username, "");
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `employee_db` DEFAULT CHARACTER SET utf8mb4");
                    return $this->getConnection();
                } catch (Exception $ex) {
                    // Output error if completely unreachable
                }
            }

            return null;
        }

        public function connect() {
            return $this->getConnection();
        }

        public function getDbName() {
            return $this->db_name;
        }
    }
}

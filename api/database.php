<?php
/**
 * Database Connection Class
 * Handles PDO connection to MySQL database with automatic credential fallback.
 */
if (!class_exists('Database')) {
    class Database {
        private $host = "localhost";
        private $db_name = "employee_db";
        private $username = "root";
        private $password = ""; // Default XAMPP MySQL password
        private $port = "3306";
        private $conn = null;

        /**
         * Establish and return the PDO database connection
         *
         * @return PDO|null
         */
        public function getConnection() {
            $this->conn = null;

            // Try standard XAMPP blank password first, with fallback to 123456
            $passwords = ["", "123456"];
            $lastException = null;

            foreach ($passwords as $pwd) {
                try {
                    $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
                    $this->conn = new PDO($dsn, $this->username, $pwd, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    // If successful, update password property and return connection
                    $this->password = $pwd;
                    return $this->conn;
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }

            // If all attempts failed, throw the exception
            if ($lastException) {
                throw $lastException;
            }

            return null;
        }

        /**
         * Alias for getConnection()
         *
         * @return PDO|null
         */
        public function connect() {
            return $this->getConnection();
        }
    }
}

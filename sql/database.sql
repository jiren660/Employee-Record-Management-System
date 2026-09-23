-- ==============================================================================
-- Western Mindanao State University - College of Computing Studies
-- ADS133: Simple Web Application Development with MySQL Database & API Implementation
-- Course Instructor: Mr. Jason A. Catadman, MIT
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `test_connection_db`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `test_connection_db`;

-- Create Employees Table (Directly matched from activity specification)
CREATE TABLE IF NOT EXISTS `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `middle_initial` VARCHAR(1) DEFAULT NULL,
  `mobile_number` VARCHAR(15) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `sex` ENUM('Male', 'Female') NOT NULL,
  `job_title` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Official Sample Employee Records (PDF 3 Page 3)
INSERT INTO `employees` (`first_name`, `last_name`, `middle_initial`, `mobile_number`, `email`, `sex`, `job_title`) VALUES
('John', 'Smith', 'A', '09123456701', 'john.smith@example.com', 'Male', 'Project Manager'),
('Jane', 'Doe', 'B', '09123456702', 'jane.doe@example.com', 'Female', 'Business Analyst'),
('Alice', 'Johnson', 'C', '09123456703', 'alice.johnson@example.com', 'Female', 'Fullstack Software Engineer'),
('Michael', 'Brown', 'D', '09123456704', 'michael.brown@example.com', 'Male', 'Front End Developer'),
('Emily', 'Davis', 'E', '09123456705', 'emily.davis@example.com', 'Female', 'Back End Developer'),
('Daniel', 'Wilson', 'F', '09123456706', 'daniel.wilson@example.com', 'Male', 'Quality Assurance Engineer'),
('Sarah', 'Martinez', 'G', '09123456707', 'sarah.martinez@example.com', 'Female', 'Project Manager'),
('Robert', 'Anderson', 'H', '09123456708', 'robert.anderson@example.com', 'Male', 'Business Analyst'),
('Sophia', 'Taylor', 'I', '09123456709', 'sophia.taylor@example.com', 'Female', 'Fullstack Software Engineer'),
('David', 'Thomas', 'J', '09123456710', 'david.thomas@example.com', 'Male', 'Front End Developer'),
('Olivia', 'Harris', 'K', '09123456711', 'olivia.harris@example.com', 'Female', 'Back End Developer'),
('James', 'Clark', 'L', '09123456712', 'james.clark@example.com', 'Male', 'Quality Assurance Engineer'),
('Isabella', 'Lewis', 'M', '09123456713', 'isabella.lewis@example.com', 'Female', 'Project Manager'),
('William', 'Lee', 'N', '09123456714', 'william.lee@example.com', 'Male', 'Business Analyst'),
('Mia', 'Walker', 'O', '09123456715', 'mia.walker@example.com', 'Female', 'Fullstack Software Engineer')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

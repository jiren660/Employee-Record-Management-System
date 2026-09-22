-- ==============================================================================
-- Employee Management System - Database Schema & Seed Data
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `employee_db`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `employee_db`;

-- Drop existing table if needed to ensure clean state
DROP TABLE IF EXISTS `employees`;

-- Create Employees Table
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `position` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `salary` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Initial Realistic Employee Records
INSERT INTO `employees` (`name`, `email`, `position`, `department`, `salary`) VALUES
('Alexander Wright', 'a.wright@enterprise.com', 'Senior Software Engineer', 'Engineering', 92000.00),
('Sophia Chen', 'sophia.chen@enterprise.com', 'Lead Product Manager', 'Product', 105000.00),
('Marcus Johnson', 'm.johnson@enterprise.com', 'DevOps & Cloud Architect', 'Engineering', 98000.00),
('Elena Rodriguez', 'e.rodriguez@enterprise.com', 'HR Business Partner', 'Human Resources', 68000.00),
('David Kim', 'david.kim@enterprise.com', 'Senior Financial Analyst', 'Finance', 76000.00),
('Olivia Taylor', 'o.taylor@enterprise.com', 'Creative Design Lead', 'Design', 84000.00),
('James Mitchell', 'j.mitchell@enterprise.com', 'Marketing Director', 'Marketing', 89000.00),
('Rachel Green', 'r.green@enterprise.com', 'Operations Coordinator', 'Operations', 62000.00);

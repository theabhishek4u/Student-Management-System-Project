-- Smart Student Management System Database Export
-- Database: student_management_db

CREATE DATABASE IF NOT EXISTS `student_management_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `student_management_db`;

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `roll_number` VARCHAR(30) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `gender` VARCHAR(20) NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `address` TEXT NOT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_students_roll_number` (`roll_number`),
  KEY `idx_students_course` (`course`),
  KEY `idx_students_full_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `students`
  (`full_name`, `roll_number`, `email`, `phone`, `gender`, `course`, `date_of_birth`, `address`, `profile_photo`)
VALUES
  ('Aarav Sharma', 'BCA2025001', 'aarav.sharma@student.example.edu', '+91 98000 10101', 'Male', 'BCA', '2004-05-14', '14 MG Road, Bengaluru, Karnataka', NULL),
  ('Priya Nair', 'MCA2025002', 'priya.nair@student.example.edu', '+91 98000 10102', 'Female', 'MCA', '2002-09-21', '22 Marine Drive, Kochi, Kerala', NULL),
  ('Sonu Verma', 'BT2025003', 'sonu.verma@student.example.edu', '+91 98000 10103', 'Male', 'B.Tech', '2004-01-10', '88 Civil Lines, Jaipur, Rajasthan', NULL),
  ('Ananya Iyer', 'BBA2025004', 'ananya.iyer@student.example.edu', '+91 98000 10104', 'Female', 'BBA', '2005-03-19', '9 Adyar Main Road, Chennai, Tamil Nadu', NULL),
  ('Kabir Malhotra', 'MBA2025005', 'kabir.malhotra@student.example.edu', '+91 98000 10105', 'Male', 'MBA', '2001-11-05', '45 Sector 17, Chandigarh', NULL),
  ('Sneha Kulkarni', 'BCA2025006', 'sneha.kulkarni@student.example.edu', '+91 98000 10106', 'Female', 'BCA', '2004-07-28', '102 FC Road, Pune, Maharashtra', NULL),
  ('Vikramaditya Joshi', 'BT2025007', 'vikram.joshi@student.example.edu', '+91 98000 10107', 'Male', 'B.Tech', '2003-12-03', '17 Arera Colony, Bhopal, Madhya Pradesh', NULL),
  ('Meera Krishnan', 'MCA2025008', 'meera.krishnan@student.example.edu', '+91 98000 10108', 'Female', 'MCA', '2002-04-16', '31 Race Course Road, Coimbatore, Tamil Nadu', NULL),
  ('Aditya Deshmukh', 'BBA2025009', 'aditya.deshmukh@student.example.edu', '+91 98000 10109', 'Male', 'BBA', '2005-02-08', '55 Dharampeth, Nagpur, Maharashtra', NULL),
  ('Ishita Banerjee', 'MBA2025010', 'ishita.banerjee@student.example.edu', '+91 98000 10110', 'Female', 'MBA', '2001-08-24', '72 Park Street, Kolkata, West Bengal', NULL),
  ('Siddharth Rao', 'BT2025011', 'siddharth.rao@student.example.edu', '+91 98000 10111', 'Male', 'B.Tech', '2004-06-11', '19 Banjara Hills, Hyderabad, Telangana', NULL),
  ('Diya Chatterjee', 'BCA2025012', 'diya.chatterjee@student.example.edu', '+91 98000 10112', 'Female', 'BCA', '2004-10-30', '64 Salt Lake Sector V, Kolkata, West Bengal', NULL);

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(40) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`action`, `description`) VALUES
  ('SYSTEM', 'Initialized student_management_db with 12 sample student records.');

-- EduPro Enterprise Attendance System Database Schema
-- Database Name: eduprom_db

CREATE DATABASE IF NOT EXISTS `eduprom_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eduprom_db`;

-- --------------------------------------------------------
-- Table structure for table `classes`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `classes` (
    `class_id` INT AUTO_INCREMENT PRIMARY KEY,
    `workspace` VARCHAR(50) DEFAULT 'School',
    `class_name` VARCHAR(100) NOT NULL,
    `teacher_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `students`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) DEFAULT '+91 0000000000',
    `class_id` INT NOT NULL,
    `status` VARCHAR(20) DEFAULT 'present',
    `remark` TEXT DEFAULT NULL,
    `attendance_rate` INT DEFAULT 100,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_class_id` (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sample Demo Data (Optional - can be removed or kept)
-- --------------------------------------------------------
INSERT INTO `classes` (`class_id`, `workspace`, `class_name`, `teacher_name`, `email`, `password`) VALUES
(1, 'School', 'Class 10A', 'Prof. Sharma', 'teacher@school.edu', 'pass123'),
(2, 'Corporate', 'DevOps Team', 'Alex Carter', 'alex@techcorp.io', 'pass123')
ON DUPLICATE KEY UPDATE `class_id`=`class_id`;

INSERT INTO `students` (`student_id`, `name`, `phone`, `class_id`, `status`, `remark`, `attendance_rate`) VALUES
('STU-101', 'Aarav Patel', '+91 9876543210', 1, 'present', 'On time', 95),
('STU-102', 'Diya Sen', '+91 9876543211', 1, 'present', '', 100),
('STU-103', 'Rohan Gupta', '+91 9876543212', 1, 'late', 'Bus delayed', 80),
('EMP-201', 'Sarah Jenkins', '+1 555-0199', 2, 'present', '', 98),
('EMP-202', 'Liam Vance', '+1 555-0144', 2, 'present', '', 92)
ON DUPLICATE KEY UPDATE `student_id`=`student_id`;

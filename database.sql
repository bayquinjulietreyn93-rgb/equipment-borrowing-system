-- ========================================================
-- Room Equipment Borrowing System - Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10.4+ / MySQL 8.x
-- Suitable for Local XAMPP and InfinityFree Hosting
-- ========================================================

CREATE DATABASE IF NOT EXISTS `room_equipment_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `room_equipment_db`;

-- --------------------------------------------------------
-- Table structure for table `equipment_inventory`
-- Stores available room equipment items and their quantities
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipment_inventory` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `item_code` VARCHAR(50) NOT NULL UNIQUE,
  `item_name` VARCHAR(100) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `total_quantity` INT NOT NULL DEFAULT 1,
  `available_quantity` INT NOT NULL DEFAULT 1,
  `storage_room` VARCHAR(50) NOT NULL DEFAULT 'IT Storage Room',
  `status` ENUM('Available', 'Maintenance', 'Decommissioned') NOT NULL DEFAULT 'Available',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `equipment_borrowings`
-- Main CRUD table: Tracks room equipment borrowing transactions
-- Contains 18 well-defined fields with appropriate data types
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `equipment_borrowings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrower_name` VARCHAR(100) NOT NULL,
  `borrower_id_number` VARCHAR(50) NOT NULL,
  `borrower_email` VARCHAR(100) NOT NULL,
  `borrower_contact` VARCHAR(20) NOT NULL,
  `equipment_id` INT DEFAULT NULL,
  `equipment_name` VARCHAR(100) NOT NULL,
  `room_assigned` VARCHAR(50) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `borrow_date` DATE NOT NULL,
  `borrow_time` TIME NOT NULL,
  `expected_return_date` DATE NOT NULL,
  `actual_return_date` DATE DEFAULT NULL,
  `status` ENUM('Borrowed', 'Returned', 'Overdue', 'Pending') NOT NULL DEFAULT 'Borrowed',
  `purpose` VARCHAR(255) NOT NULL,
  `condition_on_borrow` VARCHAR(100) NOT NULL DEFAULT 'Good / Functional',
  `condition_on_return` VARCHAR(100) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`status`),
  INDEX (`borrow_date`),
  INDEX (`equipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data for `equipment_inventory`
-- --------------------------------------------------------
INSERT INTO `equipment_inventory` (`id`, `item_code`, `item_name`, `category`, `total_quantity`, `available_quantity`, `storage_room`, `status`) VALUES
(1, 'EQ-PROJ-01', 'Epson EB-X06 XGA 3600lm Projector', 'Audio/Visual', 6, 4, 'AVR Media Center', 'Available'),
(2, 'EQ-MIC-01', 'Shure Dual Wireless UHF Microphone', 'Audio/Visual', 8, 5, 'AVR Media Center', 'Available'),
(3, 'EQ-HDMI-01', 'High-Speed 4K HDMI Cable (10 Meters)', 'Cables & Adapters', 15, 12, 'IT Support Office', 'Available'),
(4, 'EQ-LAP-01', 'Dell Latitude 3520 Presentation Laptop', 'Computing', 4, 3, 'IT Support Office', 'Available'),
(5, 'EQ-SPK-01', 'Portable Bluetooth PA Speaker 120W', 'Audio/Visual', 5, 4, 'AVR Media Center', 'Available'),
(6, 'EQ-PRES-01', 'Logitech R400 Wireless Presenter Laser', 'Peripherals', 10, 8, 'IT Support Office', 'Available'),
(7, 'EQ-CAM-01', 'Logitech C922 Pro Stream HD Webcam', 'Peripherals', 6, 5, 'IT Support Office', 'Available'),
(8, 'EQ-EXT-01', 'Heavy Duty Extension Reel (15m, 4 Outlets)', 'Cables & Adapters', 12, 10, 'Maintenance Room', 'Available'),
(9, 'EQ-DOC-01', 'AverVision USB Document Camera / Visualizer', 'Audio/Visual', 3, 2, 'AVR Media Center', 'Available'),
(10, 'EQ-SCR-01', 'Portable Tripod Projector Screen (70x70)', 'Audio/Visual', 4, 3, 'AVR Media Center', 'Available');

-- --------------------------------------------------------
-- Seed Data for `equipment_borrowings`
-- --------------------------------------------------------
INSERT INTO `equipment_borrowings` (
  `id`, `borrower_name`, `borrower_id_number`, `borrower_email`, `borrower_contact`, 
  `equipment_id`, `equipment_name`, `room_assigned`, `quantity`, 
  `borrow_date`, `borrow_time`, `expected_return_date`, `actual_return_date`, 
  `status`, `purpose`, `condition_on_borrow`, `condition_on_return`, `remarks`
) VALUES
(1, 'Prof. Maria Santos', 'FAC-2023-0104', 'maria.santos@university.edu', '+63 917 123 4567', 
 1, 'Epson EB-X06 XGA 3600lm Projector', 'Room 304 - Multimedia Room', 1, 
 CURDATE(), '08:30:00', CURDATE(), NULL, 
 'Borrowed', 'Undergraduate Thesis Defense Presentations', 'Good / Functional', NULL, 'Includes HDMI cable and power cord'),

(2, 'Engr. Christian Reyes', 'FAC-2021-0889', 'c.reyes@university.edu', '+63 928 456 7890', 
 2, 'Shure Dual Wireless UHF Microphone', 'Main Auditorium', 2, 
 CURDATE(), '09:00:00', CURDATE(), NULL, 
 'Borrowed', 'College Academic Orientation Seminar', 'Good / Functional', NULL, 'Batteries replaced with brand new pair'),

(3, 'Samantha Jane Diaz', 'STU-2024-5512', 'samantha.diaz@student.edu', '+63 905 333 1212', 
 4, 'Dell Latitude 3520 Presentation Laptop', 'Computer Lab 3', 1, 
 DATE_SUB(CURDATE(), INTERVAL 2 DAY), '10:15:00', DATE_SUB(CURDATE(), INTERVAL 2 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 
 'Returned', 'Student Council Leadership Workshop', 'Good / Functional', 'Good / Fully Functional', 'Returned in original laptop bag with charger'),

(4, 'Dr. Robert Alcantara', 'FAC-2019-0312', 'r.alcantara@university.edu', '+63 918 777 9900', 
 3, 'High-Speed 4K HDMI Cable (10 Meters)', 'Science Hall Amphitheater', 1, 
 DATE_SUB(CURDATE(), INTERVAL 3 DAY), '13:00:00', DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, 
 'Overdue', 'Guest Speaker Lecture Series', 'Good / Functional', NULL, 'Borrower notified via SMS reminder'),

(5, 'Kenneth Paul Tan', 'STU-2023-7721', 'kenneth.tan@student.edu', '+63 939 888 2345', 
 6, 'Logitech R400 Wireless Presenter Laser', 'Room 201 - Lecture Hall', 1, 
 CURDATE(), '11:45:00', CURDATE(), NULL, 
 'Pending', 'Capstone Research Final Presentation', 'Good / Functional', NULL, 'Awaiting department head approval signature');

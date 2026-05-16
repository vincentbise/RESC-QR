-- RESC-QR Database Schema
-- Rapid Emergency Status Checking via Quick Response
-- University of Southeastern Philippines (USeP)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+08:00";

CREATE DATABASE IF NOT EXISTS `resc_qr` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resc_qr`;

-- ==========================================
-- TABLE: admin
-- ==========================================
CREATE TABLE IF NOT EXISTS `admin` (
    `admin_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'admin',
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: class
-- ==========================================
CREATE TABLE IF NOT EXISTS `class` (
    `class_id` INT AUTO_INCREMENT PRIMARY KEY,
    `section_name` VARCHAR(100) NOT NULL,
    `program` VARCHAR(150) NOT NULL,
    `year_level` VARCHAR(20) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: class_mayor
-- ==========================================
CREATE TABLE IF NOT EXISTS `class_mayor` (
    `mayor_id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`class_id`) REFERENCES `class`(`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: student
-- ==========================================
CREATE TABLE IF NOT EXISTS `student` (
    `student_id` INT AUTO_INCREMENT PRIMARY KEY,
    `class_id` INT NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `password_hash` VARCHAR(255) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `course` VARCHAR(100) NOT NULL,
    `year_level` VARCHAR(20) NOT NULL,
    `qr_code_value` VARCHAR(255) DEFAULT NULL UNIQUE,
    `profile_image` VARCHAR(255) DEFAULT NULL,
    `profile_status` ENUM('Active','Inactive') DEFAULT 'Active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`class_id`) REFERENCES `class`(`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: emergency_event
-- ==========================================
CREATE TABLE IF NOT EXISTS `emergency_event` (
    `event_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(50) NOT NULL DEFAULT 'Earthquake',
    `event_datetime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `description` TEXT DEFAULT NULL,
    `status` ENUM('Active','Closed') DEFAULT 'Active',
    `created_by` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `admin`(`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: qr_scan_log
-- ==========================================
CREATE TABLE IF NOT EXISTS `qr_scan_log` (
    `scan_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `scanned_by` INT NOT NULL,
    `scan_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `scan_result` ENUM('Valid','Invalid') NOT NULL DEFAULT 'Valid',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `emergency_event`(`event_id`) ON DELETE CASCADE,
    FOREIGN KEY (`scanned_by`) REFERENCES `class_mayor`(`mayor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: student_status
-- ==========================================
CREATE TABLE IF NOT EXISTS `student_status` (
    `status_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `status` ENUM('Safe','Missing','Not in class') NOT NULL DEFAULT 'Missing',
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_student_event` (`student_id`, `event_id`),
    FOREIGN KEY (`student_id`) REFERENCES `student`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `emergency_event`(`event_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: attendance
-- ==========================================
CREATE TABLE IF NOT EXISTS `attendance` (
    `attendance_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `status` ENUM('Present','Absent','Late') NOT NULL DEFAULT 'Present',
    `recorded_by` INT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_attendance_student_date` (`student_id`, `date`),
    FOREIGN KEY (`student_id`) REFERENCES `student`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `class`(`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: emergency_contact
-- ==========================================
CREATE TABLE IF NOT EXISTS `emergency_contact` (
    `contact_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `contact_name` VARCHAR(100) NOT NULL,
    `relationship` VARCHAR(50) NOT NULL,
    `phone_number` VARCHAR(20) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student`(`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: offline_scan_buffer
-- ==========================================
CREATE TABLE IF NOT EXISTS `offline_scan_buffer` (
    `buffer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `scanned_by` INT NOT NULL,
    `scan_time` DATETIME NOT NULL,
    `synced_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`student_id`) REFERENCES `student`(`student_id`) ON DELETE CASCADE,
    FOREIGN KEY (`event_id`) REFERENCES `emergency_event`(`event_id`) ON DELETE CASCADE,
    FOREIGN KEY (`scanned_by`) REFERENCES `class_mayor`(`mayor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: incident_report
-- ==========================================
CREATE TABLE IF NOT EXISTS `incident_report` (
    `report_id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_id` INT NOT NULL,
    `generated_by` INT NOT NULL,
    `report_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `summary_text` TEXT DEFAULT NULL,
    `total_students` INT DEFAULT 0,
    `safe_count` INT DEFAULT 0,
    `missing_count` INT DEFAULT 0,
    `not_in_class_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`event_id`) REFERENCES `emergency_event`(`event_id`) ON DELETE CASCADE,
    FOREIGN KEY (`generated_by`) REFERENCES `admin`(`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: login_attempts (security)
-- ==========================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `attempt_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_time` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- INDEXES
-- ==========================================
CREATE INDEX idx_student_class ON student(class_id);
CREATE INDEX idx_student_qr ON student(qr_code_value);
CREATE INDEX idx_scan_event ON qr_scan_log(event_id);
CREATE INDEX idx_scan_student ON qr_scan_log(student_id);
CREATE INDEX idx_scan_time ON qr_scan_log(scan_time);
CREATE INDEX idx_status_event ON student_status(event_id);
CREATE INDEX idx_status_student ON student_status(student_id);
CREATE INDEX idx_offline_sync ON offline_scan_buffer(synced_at);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_contact_student ON emergency_contact(student_id);
CREATE INDEX idx_report_event ON incident_report(event_id);

-- ==========================================
-- VIEWS
-- ==========================================
CREATE OR REPLACE VIEW vw_student_profile AS
SELECT
    s.student_id, s.first_name, s.last_name, s.email, s.phone,
    s.course, s.year_level, s.qr_code_value, s.profile_image,
    s.profile_status, s.created_at,
    c.class_id, c.section_name, c.program, c.year_level AS class_year
FROM student s
JOIN class c ON s.class_id = c.class_id;

CREATE OR REPLACE VIEW vw_event_status_summary AS
SELECT
    event_id,
    SUM(status = 'Safe') AS safe_count,
    SUM(status = 'Missing') AS missing_count,
    SUM(status = 'Not in class') AS not_in_class_count,
    COUNT(*) AS total_count
FROM student_status
GROUP BY event_id;

CREATE OR REPLACE VIEW vw_missing_students AS
SELECT
    ss.event_id, s.student_id, s.first_name, s.last_name,
    s.phone, s.profile_image, c.section_name, c.program
FROM student_status ss
JOIN student s ON ss.student_id = s.student_id
JOIN class c ON s.class_id = c.class_id
WHERE ss.status = 'Missing';

CREATE OR REPLACE VIEW vw_scan_audit AS
SELECT
    q.scan_id, q.event_id, q.scan_time, q.scan_result,
    s.student_id, s.first_name, s.last_name,
    m.mayor_id, m.name AS mayor_name,
    c.section_name
FROM qr_scan_log q
JOIN student s ON q.student_id = s.student_id
JOIN class_mayor m ON q.scanned_by = m.mayor_id
JOIN class c ON s.class_id = c.class_id;

-- ==========================================
-- STORED PROCEDURES
-- ==========================================
DELIMITER $$

CREATE PROCEDURE sp_log_qr_scan(
    IN p_student_id INT,
    IN p_event_id INT,
    IN p_scanned_by INT,
    IN p_scan_result VARCHAR(20)
)
BEGIN
    INSERT INTO qr_scan_log(student_id, event_id, scanned_by, scan_time, scan_result)
    VALUES(p_student_id, p_event_id, p_scanned_by, NOW(), p_scan_result);

    IF p_scan_result = 'Valid' THEN
        INSERT INTO student_status(student_id, event_id, status, updated_at)
        VALUES(p_student_id, p_event_id, 'Safe', NOW())
        ON DUPLICATE KEY UPDATE status='Safe', updated_at=NOW();
    END IF;
END$$

CREATE PROCEDURE sp_sync_offline_scans()
BEGIN
    INSERT INTO qr_scan_log(student_id, event_id, scanned_by, scan_time, scan_result)
    SELECT student_id, event_id, scanned_by, scan_time, 'Valid'
    FROM offline_scan_buffer
    WHERE synced_at IS NULL;

    UPDATE offline_scan_buffer
    SET synced_at = NOW()
    WHERE synced_at IS NULL;
END$$

CREATE PROCEDURE sp_generate_incident_report(
    IN p_event_id INT,
    IN p_admin_id INT,
    IN p_summary TEXT
)
BEGIN
    DECLARE v_total INT DEFAULT 0;
    DECLARE v_safe INT DEFAULT 0;
    DECLARE v_missing INT DEFAULT 0;
    DECLARE v_not_in_class INT DEFAULT 0;

    SELECT
        COUNT(*),
        SUM(status = 'Safe'),
        SUM(status = 'Missing'),
        SUM(status = 'Not in class')
    INTO v_total, v_safe, v_missing, v_not_in_class
    FROM student_status
    WHERE event_id = p_event_id;

    INSERT INTO incident_report(event_id, generated_by, report_time, summary_text,
        total_students, safe_count, missing_count, not_in_class_count)
    VALUES(p_event_id, p_admin_id, NOW(), p_summary,
        v_total, v_safe, v_missing, v_not_in_class);
END$$

DELIMITER ;

-- ==========================================
-- TRIGGERS
-- ==========================================
DELIMITER $$

CREATE TRIGGER trg_before_scan_insert
BEFORE INSERT ON qr_scan_log
FOR EACH ROW
BEGIN
    IF NOT EXISTS (SELECT 1 FROM student WHERE student_id = NEW.student_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid student_id in QR scan';
    END IF;
END$$

CREATE TRIGGER trg_after_scan_insert
AFTER INSERT ON qr_scan_log
FOR EACH ROW
BEGIN
    IF NEW.scan_result = 'Valid' THEN
        INSERT INTO student_status(student_id, event_id, status, updated_at)
        VALUES(NEW.student_id, NEW.event_id, 'Safe', NOW())
        ON DUPLICATE KEY UPDATE status='Safe', updated_at=NOW();
    END IF;
END$$

DELIMITER ;

COMMIT;

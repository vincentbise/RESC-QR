-- RESC-QR Database Schema
-- Rapid Emergency Status Checking via Quick Response
-- University of Southeastern Philippines (USeP)
-- Version 2.0 — Security Enhancement: SPs read from views

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
-- Status values: Safe, Not Yet Scanned, Not in class
-- ==========================================
CREATE TABLE IF NOT EXISTS `student_status` (
    `status_id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `event_id` INT NOT NULL,
    `status` ENUM('Safe','Not Yet Scanned','Not in class') NOT NULL DEFAULT 'Not Yet Scanned',
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
-- TABLE: activity_log (audit trail)
-- ==========================================
CREATE TABLE IF NOT EXISTS `activity_log` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id` INT DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- INDEXES
-- ==========================================
CREATE INDEX idx_student_class ON student(class_id);
CREATE INDEX idx_student_qr ON student(qr_code_value);
CREATE INDEX idx_student_email ON student(email);
CREATE INDEX idx_scan_event ON qr_scan_log(event_id);
CREATE INDEX idx_scan_student ON qr_scan_log(student_id);
CREATE INDEX idx_scan_time ON qr_scan_log(scan_time);
CREATE INDEX idx_scan_log_composite ON qr_scan_log(event_id, student_id, scan_time);
CREATE INDEX idx_status_event ON student_status(event_id);
CREATE INDEX idx_status_student ON student_status(student_id);
CREATE INDEX idx_offline_sync ON offline_scan_buffer(synced_at);
CREATE INDEX idx_attendance_date ON attendance(date);
CREATE INDEX idx_contact_student ON emergency_contact(student_id);
CREATE INDEX idx_report_event ON incident_report(event_id);
CREATE INDEX idx_event_status_datetime ON emergency_event(status, event_datetime);
CREATE INDEX idx_activity_log_composite ON activity_log(user_id, action, created_at);

-- ==========================================
-- VIEWS
-- ==========================================

-- vw_student_profile: basic student + class info
CREATE OR REPLACE VIEW vw_student_profile AS
SELECT
    s.student_id, s.first_name, s.last_name, s.email, s.phone,
    s.course, s.year_level, s.qr_code_value, s.profile_image,
    s.profile_status, s.created_at,
    c.class_id, c.section_name, c.program, c.year_level AS class_year
FROM student s
JOIN class c ON s.class_id = c.class_id;

-- vw_event_status_summary: counts per event
CREATE OR REPLACE VIEW vw_event_status_summary AS
SELECT
    event_id,
    SUM(status = 'Safe') AS safe_count,
    SUM(status = 'Not Yet Scanned') AS missing_count,
    SUM(status = 'Not in class') AS not_in_class_count,
    COUNT(*) AS total_count
FROM student_status
GROUP BY event_id;

-- vw_missing_students: students not yet scanned
CREATE OR REPLACE VIEW vw_missing_students AS
SELECT
    ss.event_id, s.student_id, s.first_name, s.last_name,
    s.phone, s.profile_image, c.section_name, c.program
FROM student_status ss
JOIN student s ON ss.student_id = s.student_id
JOIN class c ON s.class_id = c.class_id
WHERE ss.status = 'Not Yet Scanned';

-- vw_scan_audit: scan audit trail
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

-- vw_student_full_status: student + latest status + class + emergency contact
CREATE OR REPLACE VIEW vw_student_full_status AS
SELECT
    s.student_id,
    s.first_name,
    s.last_name,
    s.email,
    s.phone,
    s.course,
    s.year_level,
    s.qr_code_value,
    s.profile_image,
    s.profile_status,
    s.created_at AS student_created_at,
    s.updated_at AS student_updated_at,
    c.class_id,
    c.section_name,
    c.program,
    c.year_level AS class_year,
    ss.status AS latest_status,
    ss.event_id AS latest_event_id,
    ss.updated_at AS status_updated_at,
    ec.contact_name AS emergency_contact_name,
    ec.relationship AS emergency_contact_relationship,
    ec.phone_number AS emergency_contact_phone
FROM student s
JOIN class c ON s.class_id = c.class_id
LEFT JOIN (
    SELECT ss1.*
    FROM student_status ss1
    INNER JOIN (
        SELECT student_id, MAX(status_id) AS max_status_id
        FROM student_status
        GROUP BY student_id
    ) ss2 ON ss1.student_id = ss2.student_id AND ss1.status_id = ss2.max_status_id
) ss ON s.student_id = ss.student_id
LEFT JOIN (
    SELECT ec1.*
    FROM emergency_contact ec1
    INNER JOIN (
        SELECT student_id, MIN(contact_id) AS min_contact_id
        FROM emergency_contact
        GROUP BY student_id
    ) ec2 ON ec1.student_id = ec2.student_id AND ec1.contact_id = ec2.min_contact_id
) ec ON s.student_id = ec.student_id;

-- vw_scan_log_detailed: comprehensive scan log with all related entity names
CREATE OR REPLACE VIEW vw_scan_log_detailed AS
SELECT
    q.scan_id,
    q.scan_time,
    q.scan_result,
    q.created_at AS log_created_at,
    s.student_id,
    s.first_name,
    s.last_name,
    s.qr_code_value,
    s.email AS student_email,
    c.class_id,
    c.section_name,
    c.program,
    m.mayor_id,
    m.name AS mayor_name,
    e.event_id,
    e.event_type,
    e.event_datetime,
    e.status AS event_status,
    e.description AS event_description
FROM qr_scan_log q
JOIN student s ON q.student_id = s.student_id
JOIN class c ON s.class_id = c.class_id
JOIN class_mayor m ON q.scanned_by = m.mayor_id
JOIN emergency_event e ON q.event_id = e.event_id;

-- vw_active_emergency_summary: active event with all counts and metadata
CREATE OR REPLACE VIEW vw_active_emergency_summary AS
SELECT
    e.event_id,
    e.event_type,
    e.event_datetime,
    e.description,
    e.status,
    e.created_by,
    e.created_at,
    a.name AS created_by_name,
    COALESCE(v.safe_count, 0) AS safe_count,
    COALESCE(v.missing_count, 0) AS missing_count,
    COALESCE(v.not_in_class_count, 0) AS not_in_class_count,
    COALESCE(v.total_count, 0) AS total_count
FROM emergency_event e
JOIN admin a ON e.created_by = a.admin_id
LEFT JOIN vw_event_status_summary v ON e.event_id = v.event_id;

-- vw_class_attendance_rate: per-class attendance statistics
CREATE OR REPLACE VIEW vw_class_attendance_rate AS
SELECT
    c.class_id,
    c.section_name,
    c.program,
    c.year_level,
    COUNT(DISTINCT s.student_id) AS total_students,
    COUNT(DISTINCT att.attendance_id) AS total_records,
    COALESCE(SUM(att.status = 'Present'), 0) AS present_count,
    COALESCE(SUM(att.status = 'Absent'), 0) AS absent_count,
    COALESCE(SUM(att.status = 'Late'), 0) AS late_count,
    CASE
        WHEN COUNT(DISTINCT att.attendance_id) > 0
        THEN ROUND(SUM(att.status = 'Present') / COUNT(DISTINCT att.attendance_id) * 100, 2)
        ELSE 0.00
    END AS attendance_rate
FROM class c
LEFT JOIN student s ON c.class_id = s.class_id AND s.profile_status = 'Active'
LEFT JOIN attendance att ON s.student_id = att.student_id
GROUP BY c.class_id, c.section_name, c.program, c.year_level;

-- ==========================================
-- STORED PROCEDURES
-- ==========================================
DELIMITER $$

-- sp_log_qr_scan: log a QR scan and update status
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

-- sp_sync_offline_scans: sync buffered offline scans
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

-- sp_generate_incident_report: generate report reading from VIEW
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

    -- Read from VIEW, not base table (security enhancement)
    SELECT
        COALESCE(total_count, 0),
        COALESCE(safe_count, 0),
        COALESCE(missing_count, 0),
        COALESCE(not_in_class_count, 0)
    INTO v_total, v_safe, v_missing, v_not_in_class
    FROM vw_event_status_summary
    WHERE event_id = p_event_id;

    INSERT INTO incident_report(event_id, generated_by, report_time, summary_text,
        total_students, safe_count, missing_count, not_in_class_count)
    VALUES(p_event_id, p_admin_id, NOW(), p_summary,
        v_total, v_safe, v_missing, v_not_in_class);
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_students: server-side pagination
-- Reads from: vw_student_full_status (VIEW)
-- ---------------------------------------------------------
CREATE PROCEDURE sp_get_paginated_students(
    IN p_search VARCHAR(100),
    IN p_class_id INT,
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    -- Total count for pagination metadata
    SELECT COUNT(*) AS total_count
    FROM vw_student_full_status
    WHERE profile_status = 'Active'
      AND (p_search IS NULL OR p_search = '' OR
           first_name LIKE CONCAT('%', p_search, '%') OR
           last_name LIKE CONCAT('%', p_search, '%') OR
           email LIKE CONCAT('%', p_search, '%'))
      AND (p_class_id IS NULL OR class_id = p_class_id);

    -- Paginated results from VIEW
    SELECT
        student_id, first_name, last_name, email, phone,
        course, year_level, qr_code_value, profile_image,
        profile_status, student_created_at, class_id, section_name,
        program, class_year, latest_status, latest_event_id,
        status_updated_at, emergency_contact_name,
        emergency_contact_relationship, emergency_contact_phone
    FROM vw_student_full_status
    WHERE profile_status = 'Active'
      AND (p_search IS NULL OR p_search = '' OR
           first_name LIKE CONCAT('%', p_search, '%') OR
           last_name LIKE CONCAT('%', p_search, '%') OR
           email LIKE CONCAT('%', p_search, '%'))
      AND (p_class_id IS NULL OR class_id = p_class_id)
    ORDER BY last_name ASC, first_name ASC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_scan_logs: paginated scan logs
-- Reads from: vw_scan_log_detailed (VIEW)
-- ---------------------------------------------------------
CREATE PROCEDURE sp_get_paginated_scan_logs(
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    SELECT COUNT(*) AS total_count FROM vw_scan_log_detailed;

    SELECT
        scan_id, scan_time, scan_result, student_id,
        first_name, last_name, qr_code_value, student_email,
        class_id, section_name, program, mayor_id, mayor_name,
        event_id, event_type, event_datetime, event_status
    FROM vw_scan_log_detailed
    ORDER BY scan_time DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_events: paginated events
-- Reads from: vw_active_emergency_summary (VIEW)
-- ---------------------------------------------------------
CREATE PROCEDURE sp_get_paginated_events(
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    SELECT COUNT(*) AS total_count FROM vw_active_emergency_summary;

    SELECT
        event_id, event_type, event_datetime, description,
        status, created_by_name, created_at,
        safe_count, missing_count, not_in_class_count, total_count
    FROM vw_active_emergency_summary
    ORDER BY event_datetime DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_reports: paginated incident reports
-- Reads from: vw_active_emergency_summary (VIEW) joined with incident_report
-- ---------------------------------------------------------
CREATE PROCEDURE sp_get_paginated_reports(
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    SELECT COUNT(*) AS total_count FROM incident_report;

    SELECT
        r.report_id, r.report_time, r.summary_text,
        r.total_students, r.safe_count, r.missing_count,
        r.not_in_class_count, r.created_at,
        v.event_id, v.event_type, v.event_datetime,
        v.status AS event_status, v.created_by_name AS generated_by_name
    FROM incident_report r
    LEFT JOIN vw_active_emergency_summary v ON r.event_id = v.event_id
    ORDER BY r.report_time DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_update_student_profile: safe student profile update
-- Validates via: vw_student_profile (VIEW)
-- Writes to: student (TABLE)
-- ---------------------------------------------------------
CREATE PROCEDURE sp_update_student_profile(
    IN p_student_id INT,
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_email VARCHAR(150),
    IN p_phone VARCHAR(20),
    IN p_course VARCHAR(100),
    IN p_year_level VARCHAR(20)
)
BEGIN
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_email_taken INT DEFAULT 0;

    -- Validate student exists using VIEW
    SELECT COUNT(*) INTO v_exists
    FROM vw_student_profile
    WHERE student_id = p_student_id;

    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Student not found.';
    END IF;

    IF p_first_name IS NOT NULL AND CHAR_LENGTH(TRIM(p_first_name)) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'First name cannot be empty.';
    END IF;

    IF p_last_name IS NOT NULL AND CHAR_LENGTH(TRIM(p_last_name)) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Last name cannot be empty.';
    END IF;

    IF p_email IS NOT NULL AND CHAR_LENGTH(TRIM(p_email)) > 0 THEN
        SELECT COUNT(*) INTO v_email_taken
        FROM vw_student_profile
        WHERE email = p_email AND student_id != p_student_id;

        IF v_email_taken > 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Email address is already in use by another student.';
        END IF;
    END IF;

    UPDATE student SET
        first_name = COALESCE(NULLIF(TRIM(p_first_name), ''), first_name),
        last_name  = COALESCE(NULLIF(TRIM(p_last_name), ''), last_name),
        email      = CASE WHEN p_email IS NOT NULL THEN TRIM(p_email) ELSE email END,
        phone      = CASE WHEN p_phone IS NOT NULL THEN TRIM(p_phone) ELSE phone END,
        course     = COALESCE(NULLIF(TRIM(p_course), ''), course),
        year_level = COALESCE(NULLIF(TRIM(p_year_level), ''), year_level),
        updated_at = NOW()
    WHERE student_id = p_student_id;

    -- Return updated profile from VIEW
    SELECT * FROM vw_student_profile WHERE student_id = p_student_id;
END$$

-- ---------------------------------------------------------
-- sp_declare_emergency: creates event + initializes statuses
-- ---------------------------------------------------------
CREATE PROCEDURE sp_declare_emergency(
    IN p_event_type VARCHAR(50),
    IN p_description TEXT,
    IN p_admin_id INT
)
BEGIN
    DECLARE v_event_id INT;
    DECLARE v_admin_exists INT DEFAULT 0;

    SELECT COUNT(*) INTO v_admin_exists
    FROM admin WHERE admin_id = p_admin_id;

    IF v_admin_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid admin ID.';
    END IF;

    IF p_event_type IS NULL OR CHAR_LENGTH(TRIM(p_event_type)) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Event type is required.';
    END IF;

    START TRANSACTION;

    INSERT INTO emergency_event (
        event_type, event_datetime, description, status, created_by
    ) VALUES (
        TRIM(p_event_type), NOW(), p_description, 'Active', p_admin_id
    );

    SET v_event_id = LAST_INSERT_ID();

    INSERT IGNORE INTO student_status (student_id, event_id, status)
    SELECT s.student_id, v_event_id, 'Not Yet Scanned'
    FROM student s
    WHERE s.profile_status = 'Active';

    COMMIT;

    -- Return from VIEW
    SELECT * FROM vw_active_emergency_summary
    WHERE event_id = v_event_id;
END$$

-- ---------------------------------------------------------
-- sp_cleanup_old_login_attempts: removes attempts older than 24h
-- ---------------------------------------------------------
CREATE PROCEDURE sp_cleanup_old_login_attempts()
BEGIN
    DELETE FROM login_attempts
    WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END$$

DELIMITER ;

-- ==========================================
-- STORED FUNCTIONS
-- ==========================================
DELIMITER $$

-- fn_get_student_count_by_class: count of active students in a class
CREATE FUNCTION fn_get_student_count_by_class(p_class_id INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count
    FROM vw_student_profile
    WHERE class_id = p_class_id AND profile_status = 'Active';
    RETURN v_count;
END$$

-- fn_get_safe_percentage: percentage of safe students for an event
CREATE FUNCTION fn_get_safe_percentage(p_event_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_safe INT DEFAULT 0;
    DECLARE v_total INT DEFAULT 0;

    SELECT
        COALESCE(safe_count, 0),
        COALESCE(total_count, 0)
    INTO v_safe, v_total
    FROM vw_event_status_summary
    WHERE event_id = p_event_id;

    IF v_total = 0 THEN
        RETURN 0.00;
    END IF;

    RETURN ROUND((v_safe / v_total) * 100, 2);
END$$

-- fn_is_student_safe: returns 1 if student is safe, 0 otherwise
CREATE FUNCTION fn_is_student_safe(p_student_id INT, p_event_id INT)
RETURNS TINYINT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_status VARCHAR(20) DEFAULT NULL;

    SELECT status INTO v_status
    FROM student_status
    WHERE student_id = p_student_id AND event_id = p_event_id
    LIMIT 1;

    IF v_status = 'Safe' THEN
        RETURN 1;
    END IF;
    RETURN 0;
END$$

-- fn_format_scan_time: formats datetime for display
CREATE FUNCTION fn_format_scan_time(p_datetime DATETIME)
RETURNS VARCHAR(30)
DETERMINISTIC
NO SQL
BEGIN
    IF p_datetime IS NULL THEN
        RETURN 'N/A';
    END IF;
    RETURN DATE_FORMAT(p_datetime, '%b %d, %Y %h:%i %p');
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
    IF NEW.student_id != 0 AND NOT EXISTS (SELECT 1 FROM student WHERE student_id = NEW.student_id) THEN
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

-- Auto-log student creation
CREATE TRIGGER trg_after_student_insert
AFTER INSERT ON student
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (user_id, action, entity_type, entity_id, details)
    VALUES (
        NULL,
        'STUDENT_CREATED',
        'student',
        NEW.student_id,
        CONCAT('Student registered: ', NEW.first_name, ' ', NEW.last_name,
               ' (', NEW.course, ' - ', NEW.year_level, ')')
    );
END$$

-- Auto-log student profile changes
CREATE TRIGGER trg_after_student_update
AFTER UPDATE ON student
FOR EACH ROW
BEGIN
    DECLARE v_changes TEXT DEFAULT '';

    IF OLD.first_name != NEW.first_name THEN
        SET v_changes = CONCAT(v_changes, 'first_name: ', OLD.first_name, ' → ', NEW.first_name, '; ');
    END IF;
    IF OLD.last_name != NEW.last_name THEN
        SET v_changes = CONCAT(v_changes, 'last_name: ', OLD.last_name, ' → ', NEW.last_name, '; ');
    END IF;
    IF IFNULL(OLD.email, '') != IFNULL(NEW.email, '') THEN
        SET v_changes = CONCAT(v_changes, 'email: ', IFNULL(OLD.email, 'NULL'), ' → ', IFNULL(NEW.email, 'NULL'), '; ');
    END IF;
    IF IFNULL(OLD.phone, '') != IFNULL(NEW.phone, '') THEN
        SET v_changes = CONCAT(v_changes, 'phone: ', IFNULL(OLD.phone, 'NULL'), ' → ', IFNULL(NEW.phone, 'NULL'), '; ');
    END IF;
    IF OLD.course != NEW.course THEN
        SET v_changes = CONCAT(v_changes, 'course: ', OLD.course, ' → ', NEW.course, '; ');
    END IF;
    IF OLD.profile_status != NEW.profile_status THEN
        SET v_changes = CONCAT(v_changes, 'profile_status: ', OLD.profile_status, ' → ', NEW.profile_status, '; ');
    END IF;

    IF CHAR_LENGTH(v_changes) > 0 THEN
        INSERT INTO activity_log (user_id, action, entity_type, entity_id, details)
        VALUES (
            NULL,
            'STUDENT_UPDATED',
            'student',
            NEW.student_id,
            CONCAT('Profile updated for: ', NEW.first_name, ' ', NEW.last_name, '. Changes: ', v_changes)
        );
    END IF;
END$$

-- Auto-log emergency declaration
CREATE TRIGGER trg_after_event_create
AFTER INSERT ON emergency_event
FOR EACH ROW
BEGIN
    INSERT INTO activity_log (user_id, action, entity_type, entity_id, details)
    VALUES (
        NEW.created_by,
        'EMERGENCY_DECLARED',
        'emergency_event',
        NEW.event_id,
        CONCAT('Emergency declared: ', NEW.event_type,
               IFNULL(CONCAT(' — ', NEW.description), ''))
    );
END$$

-- Auto-log status changes
CREATE TRIGGER trg_after_status_update
AFTER UPDATE ON student_status
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO activity_log (user_id, action, entity_type, entity_id, details)
        VALUES (
            NULL,
            'STATUS_CHANGED',
            'student_status',
            NEW.status_id,
            CONCAT('Student ', NEW.student_id, ' status changed: ',
                   OLD.status, ' → ', NEW.status,
                   ' (Event: ', NEW.event_id, ')')
        );
    END IF;
END$$

-- Prevent hard delete (force soft delete)
CREATE TRIGGER trg_before_student_delete
BEFORE DELETE ON student
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Hard delete is not allowed. Use soft delete by setting profile_status to Inactive.';
END$$

DELIMITER ;

-- ==========================================
-- SCHEDULED EVENTS (MySQL Event Scheduler)
-- ==========================================
SET GLOBAL event_scheduler = ON;

DELIMITER $$

-- Daily cleanup of old login attempts
CREATE EVENT IF NOT EXISTS evt_cleanup_login_attempts
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURDATE()) + INTERVAL 0 HOUR)
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Deletes login attempts older than 24 hours'
DO
BEGIN
    CALL sp_cleanup_old_login_attempts();
END$$

-- Hourly cleanup of synced offline scan buffers
CREATE EVENT IF NOT EXISTS evt_cleanup_old_sessions
ON SCHEDULE EVERY 1 HOUR
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Purges synced offline_scan_buffer records older than 24 hours'
DO
BEGIN
    DELETE FROM offline_scan_buffer
    WHERE synced_at IS NOT NULL
      AND synced_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END$$

-- Daily auto-close events older than 72 hours
CREATE EVENT IF NOT EXISTS evt_auto_close_events
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURDATE()) + INTERVAL 1 HOUR)
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Auto-closes emergency events older than 72 hours'
DO
BEGIN
    UPDATE emergency_event
    SET status = 'Closed', updated_at = NOW()
    WHERE status = 'Active'
      AND event_datetime < DATE_SUB(NOW(), INTERVAL 72 HOUR);
END$$

DELIMITER ;

COMMIT;

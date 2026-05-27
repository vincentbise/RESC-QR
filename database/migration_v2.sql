-- ============================================================
-- RESC-QR Database Migration v2 (NEW OBJECTS ONLY)
-- Remove all sections that already exist in your database
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

USE `resc_qr`;

-- ============================================================
-- 1. ENUM MIGRATION: 'Missing' → 'Not Yet Scanned'
-- (Only if not already done)
-- ============================================================
-- Check if 'Not Yet Scanned' already exists in ENUM
-- ALTER TABLE `student_status`
--   MODIFY COLUMN `status` ENUM('Safe', 'Missing', 'Not Yet Scanned', 'Not in class')
--   NOT NULL DEFAULT 'Not Yet Scanned';
-- UPDATE `student_status` SET `status` = 'Not Yet Scanned' WHERE `status` = 'Missing';
-- ALTER TABLE `student_status`
--   MODIFY COLUMN `status` ENUM('Safe', 'Not Yet Scanned', 'Not in class')
--   NOT NULL DEFAULT 'Not Yet Scanned';

-- ============================================================
-- 2. NEW VIEWS (if they don't exist)
-- ============================================================

-- vw_student_full_status: student + latest status + class + emergency contact
CREATE OR REPLACE VIEW `vw_student_full_status` AS
SELECT
    s.`student_id`,
    s.`first_name`,
    s.`last_name`,
    s.`email`,
    s.`phone`,
    s.`course`,
    s.`year_level`,
    s.`qr_code_value`,
    s.`profile_image`,
    s.`profile_status`,
    s.`created_at` AS `student_created_at`,
    s.`updated_at` AS `student_updated_at`,
    c.`class_id`,
    c.`section_name`,
    c.`program`,
    c.`year_level` AS `class_year`,
    ss.`status` AS `latest_status`,
    ss.`event_id` AS `latest_event_id`,
    ss.`updated_at` AS `status_updated_at`,
    ec.`contact_name` AS `emergency_contact_name`,
    ec.`relationship` AS `emergency_contact_relationship`,
    ec.`phone_number` AS `emergency_contact_phone`
FROM `student` s
JOIN `class` c ON s.`class_id` = c.`class_id`
LEFT JOIN (
    SELECT ss1.*
    FROM `student_status` ss1
    INNER JOIN (
        SELECT `student_id`, MAX(`status_id`) AS `max_status_id`
        FROM `student_status`
        GROUP BY `student_id`
    ) ss2 ON ss1.`student_id` = ss2.`student_id` AND ss1.`status_id` = ss2.`max_status_id`
) ss ON s.`student_id` = ss.`student_id`
LEFT JOIN (
    SELECT ec1.*
    FROM `emergency_contact` ec1
    INNER JOIN (
        SELECT `student_id`, MIN(`contact_id`) AS `min_contact_id`
        FROM `emergency_contact`
        GROUP BY `student_id`
    ) ec2 ON ec1.`student_id` = ec2.`student_id` AND ec1.`contact_id` = ec2.`min_contact_id`
) ec ON s.`student_id` = ec.`student_id`;

-- vw_scan_log_detailed: comprehensive scan log with all related entity names
CREATE OR REPLACE VIEW `vw_scan_log_detailed` AS
SELECT
    q.`scan_id`,
    q.`scan_time`,
    q.`scan_result`,
    q.`created_at` AS `log_created_at`,
    s.`student_id`,
    s.`first_name`,
    s.`last_name`,
    s.`qr_code_value`,
    s.`email` AS `student_email`,
    c.`class_id`,
    c.`section_name`,
    c.`program`,
    m.`mayor_id`,
    m.`name` AS `mayor_name`,
    e.`event_id`,
    e.`event_type`,
    e.`event_datetime`,
    e.`status` AS `event_status`,
    e.`description` AS `event_description`
FROM `qr_scan_log` q
JOIN `student` s ON q.`student_id` = s.`student_id`
JOIN `class` c ON s.`class_id` = c.`class_id`
JOIN `class_mayor` m ON q.`scanned_by` = m.`mayor_id`
JOIN `emergency_event` e ON q.`event_id` = e.`event_id`;

-- vw_active_emergency_summary: active event with all counts and metadata
CREATE OR REPLACE VIEW `vw_active_emergency_summary` AS
SELECT
    e.`event_id`,
    e.`event_type`,
    e.`event_datetime`,
    e.`description`,
    e.`status`,
    e.`created_by`,
    e.`created_at`,
    a.`name` AS `created_by_name`,
    COALESCE(v.`safe_count`, 0) AS `safe_count`,
    COALESCE(v.`missing_count`, 0) AS `missing_count`,
    COALESCE(v.`not_in_class_count`, 0) AS `not_in_class_count`,
    COALESCE(v.`total_count`, 0) AS `total_count`
FROM `emergency_event` e
JOIN `admin` a ON e.`created_by` = a.`admin_id`
LEFT JOIN `vw_event_status_summary` v ON e.`event_id` = v.`event_id`;

-- vw_class_attendance_rate: per-class attendance statistics
CREATE OR REPLACE VIEW `vw_class_attendance_rate` AS
SELECT
    c.`class_id`,
    c.`section_name`,
    c.`program`,
    c.`year_level`,
    COUNT(DISTINCT s.`student_id`) AS `total_students`,
    COUNT(DISTINCT att.`attendance_id`) AS `total_records`,
    COALESCE(SUM(att.`status` = 'Present'), 0) AS `present_count`,
    COALESCE(SUM(att.`status` = 'Absent'), 0) AS `absent_count`,
    COALESCE(SUM(att.`status` = 'Late'), 0) AS `late_count`,
    CASE
        WHEN COUNT(DISTINCT att.`attendance_id`) > 0
        THEN ROUND(SUM(att.`status` = 'Present') / COUNT(DISTINCT att.`attendance_id`) * 100, 2)
        ELSE 0.00
    END AS `attendance_rate`
FROM `class` c
LEFT JOIN `student` s ON c.`class_id` = s.`class_id` AND s.`profile_status` = 'Active'
LEFT JOIN `attendance` att ON s.`student_id` = att.`student_id`
GROUP BY c.`class_id`, c.`section_name`, c.`program`, c.`year_level`;

-- ============================================================
-- 3. NEW STORED PROCEDURES (all read from views)
-- ============================================================
DELIMITER $$

-- ---------------------------------------------------------
-- sp_get_paginated_students: server-side pagination
-- Reads from: vw_student_full_status (VIEW)
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_get_paginated_students`$$

CREATE PROCEDURE `sp_get_paginated_students`(
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
    SELECT COUNT(*) AS `total_count`
    FROM `vw_student_full_status`
    WHERE `profile_status` = 'Active'
      AND (p_search IS NULL OR p_search = '' OR
           `first_name` LIKE CONCAT('%', p_search, '%') OR
           `last_name` LIKE CONCAT('%', p_search, '%') OR
           `email` LIKE CONCAT('%', p_search, '%'))
      AND (p_class_id IS NULL OR `class_id` = p_class_id);

    -- Paginated results from VIEW
    SELECT
        `student_id`, `first_name`, `last_name`, `email`, `phone`,
        `course`, `year_level`, `qr_code_value`, `profile_image`,
        `profile_status`, `student_created_at`, `class_id`, `section_name`,
        `program`, `class_year`, `latest_status`, `latest_event_id`,
        `status_updated_at`, `emergency_contact_name`,
        `emergency_contact_relationship`, `emergency_contact_phone`
    FROM `vw_student_full_status`
    WHERE `profile_status` = 'Active'
      AND (p_search IS NULL OR p_search = '' OR
           `first_name` LIKE CONCAT('%', p_search, '%') OR
           `last_name` LIKE CONCAT('%', p_search, '%') OR
           `email` LIKE CONCAT('%', p_search, '%'))
      AND (p_class_id IS NULL OR `class_id` = p_class_id)
    ORDER BY `last_name` ASC, `first_name` ASC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_scan_logs: paginated scan logs
-- Reads from: vw_scan_log_detailed (VIEW)
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_get_paginated_scan_logs`$$

CREATE PROCEDURE `sp_get_paginated_scan_logs`(
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    -- Total count
    SELECT COUNT(*) AS `total_count` FROM `vw_scan_log_detailed`;

    -- Paginated results from VIEW
    SELECT
        `scan_id`, `scan_time`, `scan_result`, `student_id`,
        `first_name`, `last_name`, `qr_code_value`, `student_email`,
        `class_id`, `section_name`, `program`, `mayor_id`, `mayor_name`,
        `event_id`, `event_type`, `event_datetime`, `event_status`
    FROM `vw_scan_log_detailed`
    ORDER BY `scan_time` DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_events: paginated events
-- Reads from: vw_active_emergency_summary (VIEW)
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_get_paginated_events`$$

CREATE PROCEDURE `sp_get_paginated_events`(
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    -- Total count
    SELECT COUNT(*) AS `total_count` FROM `vw_active_emergency_summary`;

    -- Paginated results from VIEW
    SELECT
        `event_id`, `event_type`, `event_datetime`, `description`,
        `status`, `created_by_name`, `created_at`,
        `safe_count`, `missing_count`, `not_in_class_count`, `total_count`
    FROM `vw_active_emergency_summary`
    ORDER BY `event_datetime` DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_get_paginated_reports: paginated incident reports
-- Reads from: vw_active_emergency_summary (VIEW) joined with incident_report
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_get_paginated_reports`$$

CREATE PROCEDURE `sp_get_paginated_reports`(
    IN p_page INT,
    IN p_per_page INT
)
BEGIN
    DECLARE v_offset INT;
    SET p_page = IFNULL(p_page, 1);
    SET p_per_page = IFNULL(p_per_page, 10);
    SET v_offset = (p_page - 1) * p_per_page;

    -- Total count
    SELECT COUNT(*) AS `total_count` FROM `incident_report`;

    -- Paginated results — joins with VIEW for event metadata
    SELECT
        r.`report_id`, r.`report_time`, r.`summary_text`,
        r.`total_students`, r.`safe_count`, r.`missing_count`,
        r.`not_in_class_count`, r.`created_at`,
        v.`event_id`, v.`event_type`, v.`event_datetime`,
        v.`status` AS `event_status`, v.`created_by_name` AS `generated_by_name`
    FROM `incident_report` r
    LEFT JOIN `vw_active_emergency_summary` v ON r.`event_id` = v.`event_id`
    ORDER BY r.`report_time` DESC
    LIMIT p_per_page OFFSET v_offset;
END$$

-- ---------------------------------------------------------
-- sp_update_student_profile: safe student profile update
-- Validates via: vw_student_full_status (VIEW)
-- Writes to: student (TABLE)
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_update_student_profile`$$

CREATE PROCEDURE `sp_update_student_profile`(
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

    -- Validate student exists using VIEW (security: no direct table access)
    SELECT COUNT(*) INTO v_exists
    FROM `vw_student_full_status`
    WHERE `student_id` = p_student_id;

    IF v_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Student not found.';
    END IF;

    -- Validate first_name
    IF p_first_name IS NOT NULL AND CHAR_LENGTH(TRIM(p_first_name)) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'First name cannot be empty.';
    END IF;

    -- Validate last_name
    IF p_last_name IS NOT NULL AND CHAR_LENGTH(TRIM(p_last_name)) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Last name cannot be empty.';
    END IF;

    -- Validate email uniqueness (if provided)
    IF p_email IS NOT NULL AND CHAR_LENGTH(TRIM(p_email)) > 0 THEN
        SELECT COUNT(*) INTO v_email_taken
        FROM `vw_student_full_status`
        WHERE `email` = p_email AND `student_id` != p_student_id;

        IF v_email_taken > 0 THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Email address is already in use by another student.';
        END IF;
    END IF;

    -- Perform update on base table (writes must target tables)
    UPDATE `student` SET
        `first_name` = COALESCE(NULLIF(TRIM(p_first_name), ''), `first_name`),
        `last_name`  = COALESCE(NULLIF(TRIM(p_last_name), ''), `last_name`),
        `email`      = CASE WHEN p_email IS NOT NULL THEN TRIM(p_email) ELSE `email` END,
        `phone`      = CASE WHEN p_phone IS NOT NULL THEN TRIM(p_phone) ELSE `phone` END,
        `course`     = COALESCE(NULLIF(TRIM(p_course), ''), `course`),
        `year_level` = COALESCE(NULLIF(TRIM(p_year_level), ''), `year_level`),
        `updated_at` = NOW()
    WHERE `student_id` = p_student_id;

    -- Return updated profile from VIEW
    SELECT * FROM `vw_student_full_status` WHERE `student_id` = p_student_id;
END$$

-- ---------------------------------------------------------
-- sp_declare_emergency: creates event + initializes statuses
-- Writes to: emergency_event, student_status (TABLES)
-- Returns: new event from vw_active_emergency_summary (VIEW)
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_declare_emergency`$$

CREATE PROCEDURE `sp_declare_emergency`(
    IN p_event_type VARCHAR(50),
    IN p_description TEXT,
    IN p_admin_id INT
)
BEGIN
    DECLARE v_event_id INT;
    DECLARE v_admin_exists INT DEFAULT 0;

    -- Validate admin exists
    SELECT COUNT(*) INTO v_admin_exists
    FROM `admin` WHERE `admin_id` = p_admin_id;

    IF v_admin_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Invalid admin ID.';
    END IF;

    -- Validate event type
    IF p_event_type IS NULL OR CHAR_LENGTH(TRIM(p_event_type)) = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Event type is required.';
    END IF;

    START TRANSACTION;

    -- Create the emergency event
    INSERT INTO `emergency_event` (
        `event_type`, `event_datetime`, `description`, `status`, `created_by`
    ) VALUES (
        TRIM(p_event_type), NOW(), p_description, 'Active', p_admin_id
    );

    SET v_event_id = LAST_INSERT_ID();

    -- Initialize all active student statuses to 'Not Yet Scanned'
    INSERT IGNORE INTO `student_status` (`student_id`, `event_id`, `status`)
    SELECT s.`student_id`, v_event_id, 'Not Yet Scanned'
    FROM `student` s
    WHERE s.`profile_status` = 'Active';

    COMMIT;

    -- Return from VIEW
    SELECT * FROM `vw_active_emergency_summary`
    WHERE `event_id` = v_event_id;
END$$

-- ---------------------------------------------------------
-- sp_cleanup_old_login_attempts: removes attempts older than 24h
-- ---------------------------------------------------------
DROP PROCEDURE IF EXISTS `sp_cleanup_old_login_attempts`$$

CREATE PROCEDURE `sp_cleanup_old_login_attempts`()
BEGIN
    DELETE FROM `login_attempts`
    WHERE `attempt_time` < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END$$

DELIMITER ;

-- ============================================================
-- 4. NEW STORED FUNCTIONS
-- ============================================================
DELIMITER $$

-- fn_get_student_count_by_class: returns count of active students in a class
DROP FUNCTION IF EXISTS `fn_get_student_count_by_class`$$

CREATE FUNCTION `fn_get_student_count_by_class`(p_class_id INT)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_count INT DEFAULT 0;
    SELECT COUNT(*) INTO v_count
    FROM `vw_student_full_status`
    WHERE `class_id` = p_class_id AND `profile_status` = 'Active';
    RETURN v_count;
END$$

-- fn_get_safe_percentage: returns percentage of safe students for an event
DROP FUNCTION IF EXISTS `fn_get_safe_percentage`$$

CREATE FUNCTION `fn_get_safe_percentage`(p_event_id INT)
RETURNS DECIMAL(5,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_safe INT DEFAULT 0;
    DECLARE v_total INT DEFAULT 0;

    SELECT
        COALESCE(`safe_count`, 0),
        COALESCE(`total_count`, 0)
    INTO v_safe, v_total
    FROM `vw_event_status_summary`
    WHERE `event_id` = p_event_id;

    IF v_total = 0 THEN
        RETURN 0.00;
    END IF;

    RETURN ROUND((v_safe / v_total) * 100, 2);
END$$

-- fn_is_student_safe: returns 1 if student is safe, 0 otherwise
DROP FUNCTION IF EXISTS `fn_is_student_safe`$$

CREATE FUNCTION `fn_is_student_safe`(p_student_id INT, p_event_id INT)
RETURNS TINYINT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_status VARCHAR(20) DEFAULT NULL;

    SELECT `status` INTO v_status
    FROM `student_status`
    WHERE `student_id` = p_student_id AND `event_id` = p_event_id
    LIMIT 1;

    IF v_status = 'Safe' THEN
        RETURN 1;
    END IF;
    RETURN 0;
END$$

-- fn_format_scan_time: formats datetime for display
DROP FUNCTION IF EXISTS `fn_format_scan_time`$$

CREATE FUNCTION `fn_format_scan_time`(p_datetime DATETIME)
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

-- ============================================================
-- 5. NEW TRIGGERS
-- ============================================================
DELIMITER $$

-- trg_after_student_insert: auto-log student creation
DROP TRIGGER IF EXISTS `trg_after_student_insert`$$

CREATE TRIGGER `trg_after_student_insert`
AFTER INSERT ON `student`
FOR EACH ROW
BEGIN
    INSERT INTO `activity_log` (`user_id`, `action`, `entity_type`, `entity_id`, `details`)
    VALUES (
        NULL,
        'STUDENT_CREATED',
        'student',
        NEW.`student_id`,
        CONCAT('Student registered: ', NEW.`first_name`, ' ', NEW.`last_name`,
               ' (', NEW.`course`, ' - ', NEW.`year_level`, ')')
    );
END$$

-- trg_after_student_update: auto-log student profile changes
DROP TRIGGER IF EXISTS `trg_after_student_update`$$

CREATE TRIGGER `trg_after_student_update`
AFTER UPDATE ON `student`
FOR EACH ROW
BEGIN
    DECLARE v_changes TEXT DEFAULT '';

    IF OLD.`first_name` != NEW.`first_name` THEN
        SET v_changes = CONCAT(v_changes, 'first_name: ', OLD.`first_name`, ' → ', NEW.`first_name`, '; ');
    END IF;
    IF OLD.`last_name` != NEW.`last_name` THEN
        SET v_changes = CONCAT(v_changes, 'last_name: ', OLD.`last_name`, ' → ', NEW.`last_name`, '; ');
    END IF;
    IF IFNULL(OLD.`email`, '') != IFNULL(NEW.`email`, '') THEN
        SET v_changes = CONCAT(v_changes, 'email: ', IFNULL(OLD.`email`, 'NULL'), ' → ', IFNULL(NEW.`email`, 'NULL'), '; ');
    END IF;
    IF IFNULL(OLD.`phone`, '') != IFNULL(NEW.`phone`, '') THEN
        SET v_changes = CONCAT(v_changes, 'phone: ', IFNULL(OLD.`phone`, 'NULL'), ' → ', IFNULL(NEW.`phone`, 'NULL'), '; ');
    END IF;
    IF OLD.`course` != NEW.`course` THEN
        SET v_changes = CONCAT(v_changes, 'course: ', OLD.`course`, ' → ', NEW.`course`, '; ');
    END IF;
    IF OLD.`profile_status` != NEW.`profile_status` THEN
        SET v_changes = CONCAT(v_changes, 'profile_status: ', OLD.`profile_status`, ' → ', NEW.`profile_status`, '; ');
    END IF;

    IF CHAR_LENGTH(v_changes) > 0 THEN
        INSERT INTO `activity_log` (`user_id`, `action`, `entity_type`, `entity_id`, `details`)
        VALUES (
            NULL,
            'STUDENT_UPDATED',
            'student',
            NEW.`student_id`,
            CONCAT('Profile updated for: ', NEW.`first_name`, ' ', NEW.`last_name`, '. Changes: ', v_changes)
        );
    END IF;
END$$

-- trg_after_event_create: auto-log emergency declaration
DROP TRIGGER IF EXISTS `trg_after_event_create`$$

CREATE TRIGGER `trg_after_event_create`
AFTER INSERT ON `emergency_event`
FOR EACH ROW
BEGIN
    INSERT INTO `activity_log` (`user_id`, `action`, `entity_type`, `entity_id`, `details`)
    VALUES (
        NEW.`created_by`,
        'EMERGENCY_DECLARED',
        'emergency_event',
        NEW.`event_id`,
        CONCAT('Emergency declared: ', NEW.`event_type`,
               IFNULL(CONCAT(' — ', NEW.`description`), ''))
    );
END$$

-- trg_after_status_update: auto-log status changes
DROP TRIGGER IF EXISTS `trg_after_status_update`$$

CREATE TRIGGER `trg_after_status_update`
AFTER UPDATE ON `student_status`
FOR EACH ROW
BEGIN
    IF OLD.`status` != NEW.`status` THEN
        INSERT INTO `activity_log` (`user_id`, `action`, `entity_type`, `entity_id`, `details`)
        VALUES (
            NULL,
            'STATUS_CHANGED',
            'student_status',
            NEW.`status_id`,
            CONCAT('Student ', NEW.`student_id`, ' status changed: ',
                   OLD.`status`, ' → ', NEW.`status`,
                   ' (Event: ', NEW.`event_id`, ')')
        );
    END IF;
END$$

-- trg_before_student_delete: prevent hard delete (force soft delete)
DROP TRIGGER IF EXISTS `trg_before_student_delete`$$

CREATE TRIGGER `trg_before_student_delete`
BEFORE DELETE ON `student`
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Hard delete is not allowed. Use soft delete by setting profile_status to Inactive.';
END$$

DELIMITER ;

-- ============================================================
-- 6. SCHEDULED EVENTS (MySQL Event Scheduler)
-- ============================================================

-- Enable the event scheduler
SET GLOBAL event_scheduler = ON;

DELIMITER $$

-- evt_cleanup_login_attempts: daily cleanup of old login attempts
DROP EVENT IF EXISTS `evt_cleanup_login_attempts`$$

CREATE EVENT IF NOT EXISTS `evt_cleanup_login_attempts`
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURDATE()) + INTERVAL 0 HOUR)
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Deletes login attempts older than 24 hours'
DO
BEGIN
    CALL `sp_cleanup_old_login_attempts`();
END$$

-- evt_cleanup_old_sessions: hourly cleanup of synced offline scan buffers
DROP EVENT IF EXISTS `evt_cleanup_old_sessions`$$

CREATE EVENT IF NOT EXISTS `evt_cleanup_old_sessions`
ON SCHEDULE EVERY 1 HOUR
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Purges synced offline_scan_buffer records older than 24 hours'
DO
BEGIN
    DELETE FROM `offline_scan_buffer`
    WHERE `synced_at` IS NOT NULL
      AND `synced_at` < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END$$

-- evt_auto_close_events: daily auto-close events older than 72 hours
DROP EVENT IF EXISTS `evt_auto_close_events`$$

CREATE EVENT IF NOT EXISTS `evt_auto_close_events`
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURDATE()) + INTERVAL 1 HOUR)
ON COMPLETION PRESERVE
ENABLE
COMMENT 'Auto-closes emergency events older than 72 hours'
DO
BEGIN
    UPDATE `emergency_event`
    SET `status` = 'Closed', `updated_at` = NOW()
    WHERE `status` = 'Active'
      AND `event_datetime` < DATE_SUB(NOW(), INTERVAL 72 HOUR);
END$$

DELIMITER ;

-- ============================================================
-- Migration complete - NEW OBJECTS ONLY
-- ============================================================
SELECT 'Migration v2 (new objects only) completed successfully.' AS `result`;
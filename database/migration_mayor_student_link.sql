USE `resc_qr`;

SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'class_mayor'
      AND COLUMN_NAME = 'student_id'
);
SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE `class_mayor` ADD COLUMN `student_id` INT NULL UNIQUE AFTER `mayor_id`, ADD CONSTRAINT `fk_class_mayor_student` FOREIGN KEY (`student_id`) REFERENCES `student`(`student_id`) ON DELETE SET NULL;',
    'SELECT 1;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `class_mayor` cm
JOIN `student` s ON LOWER(s.email) = LOWER(cm.email)
SET cm.student_id = s.student_id
WHERE cm.student_id IS NULL;

INSERT INTO `student` (
    `class_id`, `first_name`, `last_name`, `email`, `password_hash`,
    `phone`, `course`, `year_level`, `qr_code_value`, `profile_image`, `profile_status`
)
SELECT
    cm.class_id,
    SUBSTRING_INDEX(TRIM(cm.name), ' ', 1) AS first_name,
    COALESCE(NULLIF(TRIM(SUBSTRING(TRIM(cm.name), CHAR_LENGTH(SUBSTRING_INDEX(TRIM(cm.name), ' ', 1)) + 1)), ''), SUBSTRING_INDEX(TRIM(cm.name), ' ', 1)) AS last_name,
    cm.email,
    cm.password_hash,
    cm.phone,
    CASE
        WHEN c.program LIKE '%Information Technology%' THEN 'BSIT'
        WHEN c.program LIKE '%Computer Science%' THEN 'BSCS'
        ELSE c.program
    END AS course,
    c.year_level,
    CONCAT('RESC-STU-', LPAD((SELECT COALESCE(MAX(s2.student_id), 0) FROM `student` s2) + ROW_NUMBER() OVER (ORDER BY cm.mayor_id), 5, '0')) AS qr_code_value,
    cm.profile_image,
    'Active'
FROM `class_mayor` cm
JOIN `class` c ON cm.class_id = c.class_id
WHERE cm.student_id IS NULL;

UPDATE `class_mayor` cm
JOIN `student` s ON LOWER(s.email) = LOWER(cm.email)
SET cm.student_id = s.student_id
WHERE cm.student_id IS NULL;

SET @fk1 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'qr_scan_log' AND CONSTRAINT_NAME = 'qr_scan_log_ibfk_3'
);
SET @sql = IF(@fk1 > 0, 'ALTER TABLE `qr_scan_log` DROP FOREIGN KEY `qr_scan_log_ibfk_3`;', 'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk2 = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'offline_scan_buffer' AND CONSTRAINT_NAME = 'offline_scan_buffer_ibfk_3'
);
SET @sql = IF(@fk2 > 0, 'ALTER TABLE `offline_scan_buffer` DROP FOREIGN KEY `offline_scan_buffer_ibfk_3`;', 'SELECT 1;');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_scan_type = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'qr_scan_log' AND COLUMN_NAME = 'scanned_by_type'
);
SET @sql = IF(
    @col_scan_type = 0,
    "ALTER TABLE `qr_scan_log` ADD COLUMN `scanned_by_type` ENUM('admin','mayor','student') NOT NULL DEFAULT 'mayor' AFTER `scanned_by`;",
    'SELECT 1;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_buf_type = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'offline_scan_buffer' AND COLUMN_NAME = 'scanned_by_type'
);
SET @sql = IF(
    @col_buf_type = 0,
    "ALTER TABLE `offline_scan_buffer` ADD COLUMN `scanned_by_type` ENUM('admin','mayor','student') NOT NULL DEFAULT 'mayor' AFTER `scanned_by`;",
    'SELECT 1;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

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
    q.`scanned_by` AS `mayor_id`,
    CASE
        WHEN q.`scanned_by_type` = 'student' THEN CONCAT(sc_s.`first_name`, ' ', sc_s.`last_name`)
        WHEN q.`scanned_by_type` = 'admin'   THEN sc_a.`name`
        ELSE m.`name`
    END AS `mayor_name`,
    e.`event_id`,
    e.`event_type`,
    e.`event_datetime`,
    e.`status` AS `event_status`,
    e.`description` AS `event_description`
FROM `qr_scan_log` q
JOIN `student` s ON q.`student_id` = s.`student_id`
JOIN `class` c ON s.`class_id` = c.`class_id`
JOIN `emergency_event` e ON q.`event_id` = e.`event_id`
LEFT JOIN `class_mayor` m ON q.`scanned_by_type` = 'mayor' AND q.`scanned_by` = m.`mayor_id`
LEFT JOIN `student` sc_s ON q.`scanned_by_type` = 'student' AND q.`scanned_by` = sc_s.`student_id`
LEFT JOIN `admin` sc_a ON q.`scanned_by_type` = 'admin' AND q.`scanned_by` = sc_a.`admin_id`;

CREATE OR REPLACE VIEW `vw_scan_audit` AS
SELECT
    q.`scan_id`,
    q.`event_id`,
    q.`scan_time`,
    q.`scan_result`,
    s.`student_id`,
    s.`first_name`,
    s.`last_name`,
    q.`scanned_by` AS `mayor_id`,
    CASE
        WHEN q.`scanned_by_type` = 'student' THEN CONCAT(sc_s.`first_name`, ' ', sc_s.`last_name`)
        WHEN q.`scanned_by_type` = 'admin'   THEN sc_a.`name`
        ELSE m.`name`
    END AS `mayor_name`,
    c.`section_name`
FROM `qr_scan_log` q
JOIN `student` s ON q.`student_id` = s.`student_id`
JOIN `class` c ON s.`class_id` = c.`class_id`
LEFT JOIN `class_mayor` m ON q.`scanned_by_type` = 'mayor' AND q.`scanned_by` = m.`mayor_id`
LEFT JOIN `student` sc_s ON q.`scanned_by_type` = 'student' AND q.`scanned_by` = sc_s.`student_id`
LEFT JOIN `admin` sc_a ON q.`scanned_by_type` = 'admin' AND q.`scanned_by` = sc_a.`admin_id`;

INSERT IGNORE INTO `student_status` (`student_id`, `event_id`, `status`)
SELECT
    s.`student_id`,
    e.`event_id`,
    CASE
        WHEN (cm.`mayor_id` IS NOT NULL AND COALESCE(up_m.`is_mayor`, 1) = 1)
          OR COALESCE(up_s.`is_mayor`, 0) = 1
            THEN 'Safe'
        WHEN a.`status` = 'Absent'
            THEN 'Absent'
        ELSE 'Not Yet Scanned'
    END
FROM `student` s
JOIN `emergency_event` e ON e.`status` = 'Active'
LEFT JOIN `class_mayor` cm ON cm.`student_id` = s.`student_id`
LEFT JOIN `user_permissions` up_m ON up_m.`user_type` = 'mayor' AND up_m.`user_id` = cm.`mayor_id`
LEFT JOIN `user_permissions` up_s ON up_s.`user_type` = 'student' AND up_s.`user_id` = s.`student_id`
LEFT JOIN `attendance` a ON a.`student_id` = s.`student_id` AND a.`date` = DATE(e.`event_datetime`)
WHERE s.`profile_status` = 'Active';

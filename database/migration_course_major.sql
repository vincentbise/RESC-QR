-- ============================================================
-- Migration: Course Major
-- A course is now identified by Course Code + Major
-- (e.g. BSIT / Information Security, BSIT / Web Development).
-- Safe to run multiple times.
-- ============================================================

USE `resc_qr`;

-- 1. Add course_major column
SET @col_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND COLUMN_NAME = 'course_major'
);
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `course` ADD COLUMN `course_major` VARCHAR(150) NOT NULL DEFAULT '''' AFTER `course_code`;',
    'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Drop old single-column unique keys (same code may now have several majors)
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND INDEX_NAME = 'uq_course_code'
);
SET @sql = IF(@idx_exists > 0, 'ALTER TABLE `course` DROP INDEX `uq_course_code`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND INDEX_NAME = 'uq_course_name'
);
SET @sql = IF(@idx_exists > 0, 'ALTER TABLE `course` DROP INDEX `uq_course_name`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Unique per (code, major)
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'course' AND INDEX_NAME = 'uq_course_code_major'
);
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `course` ADD UNIQUE KEY `uq_course_code_major` (`course_code`, `course_major`);', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

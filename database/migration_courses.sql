-- ============================================================
-- Migration: Dynamic Courses Table & Link to Class Table
-- Safe to run multiple times.
-- ============================================================

USE `resc_qr`;

-- 1. Create course table
CREATE TABLE IF NOT EXISTS `course` (
    `course_id`   INT          NOT NULL AUTO_INCREMENT,
    `course_code` VARCHAR(30)  NOT NULL,
    `course_name` VARCHAR(150) NOT NULL,
    `created_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`course_id`),
    UNIQUE KEY `uq_course_code` (`course_code`),
    UNIQUE KEY `uq_course_name` (`course_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Seed existing courses if not already present
INSERT IGNORE INTO `course` (`course_code`, `course_name`) VALUES
('BSIT', 'BS Information Technology'),
('BSCS', 'BS Computer Science'),
('BSIS', 'BS Information Systems'),
('BSEd', 'BS Education'),
('BSBA', 'BS Business Administration');

-- 3. Add course_id column to class table if not exists
SET @col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'class'
      AND COLUMN_NAME = 'course_id'
);
SET @sql = IF(
    @col_exists = 0,
    'ALTER TABLE `class` ADD COLUMN `course_id` INT NULL AFTER `class_id`, ADD CONSTRAINT `fk_class_course` FOREIGN KEY (`course_id`) REFERENCES `course`(`course_id`) ON DELETE CASCADE;',
    'SELECT 1;'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Backfill class.course_id from matching course_name
UPDATE `class` c
JOIN `course` co ON LOWER(co.`course_name`) = LOWER(c.`program`)
SET c.`course_id` = co.`course_id`
WHERE c.`course_id` IS NULL;

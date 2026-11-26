-- ============================================================
-- RBAC Migration: User Permissions Table
-- Run this on a fresh database to create the RBAC system.
-- ============================================================

CREATE TABLE IF NOT EXISTS `user_permissions` (
    `permission_id`          INT             NOT NULL AUTO_INCREMENT,
    `user_type`              ENUM('admin','mayor','student') NOT NULL,
    `user_id`                INT             NOT NULL,
    `is_mayor`               TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_qr_scanner`        TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_generate_report`   TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_declare_emergency` TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_qr_codes`          TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_emergency_hotlines`TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_my_qr`             TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_student_list_admin`TINYINT(1)      NOT NULL DEFAULT 0,
    `perm_student_list_mayor`TINYINT(1)      NOT NULL DEFAULT 0,
    `updated_at`             DATETIME        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `updated_by`             INT             DEFAULT NULL,
    PRIMARY KEY (`permission_id`),
    UNIQUE KEY `uq_user_perm` (`user_type`, `user_id`),
    KEY `idx_user_perm_lookup` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

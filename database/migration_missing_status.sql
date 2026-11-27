-- ============================================================
-- Migration: "Missing" student status
-- While an event is Active, unscanned students are "Not Yet Scanned".
-- When the event is Closed, anyone still "Not Yet Scanned" becomes "Missing".
-- Safe to run multiple times.
-- ============================================================

USE `resc_qr`;

-- 1. Allow the Missing status
ALTER TABLE `student_status`
    MODIFY `status` ENUM('Safe','Not Yet Scanned','Absent','Missing') NOT NULL DEFAULT 'Not Yet Scanned';

-- 2. Backfill: events that were already closed
UPDATE `student_status` ss
JOIN `emergency_event` e ON e.event_id = ss.event_id
SET ss.status = 'Missing'
WHERE e.status = 'Closed' AND ss.status = 'Not Yet Scanned';

-- 3. Summary view: missing_count is only final (closed-event) Missing students
CREATE OR REPLACE VIEW `vw_event_status_summary` AS
SELECT
    `event_id`,
    SUM(`status` = 'Safe')            AS `safe_count`,
    SUM(`status` = 'Missing')         AS `missing_count`,
    SUM(`status` = 'Not Yet Scanned') AS `not_yet_scanned_count`,
    SUM(`status` = 'Absent')          AS `not_in_class_count`,
    COUNT(*)                          AS `total_count`
FROM `student_status`
GROUP BY `event_id`;

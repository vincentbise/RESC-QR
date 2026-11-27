-- ============================================================
-- Migration: Ensure Sections A & B exist for every Course & Year Level
-- Safe to run multiple times (skips existing sections).
-- ============================================================

USE `resc_qr`;

INSERT INTO `class` (`section_name`, `program`, `year_level`)
SELECT v.`section_name`, v.`program`, v.`year_level`
FROM (
    SELECT 'BSIT 1A' AS section_name, 'BS Information Technology' AS program, '1st Year' AS year_level UNION ALL
    SELECT 'BSIT 1B', 'BS Information Technology', '1st Year' UNION ALL
    SELECT 'BSIT 2A', 'BS Information Technology', '2nd Year' UNION ALL
    SELECT 'BSIT 2B', 'BS Information Technology', '2nd Year' UNION ALL
    SELECT 'BSIT 3A', 'BS Information Technology', '3rd Year' UNION ALL
    SELECT 'BSIT 3B', 'BS Information Technology', '3rd Year' UNION ALL
    SELECT 'BSIT 4A', 'BS Information Technology', '4th Year' UNION ALL
    SELECT 'BSIT 4B', 'BS Information Technology', '4th Year' UNION ALL

    SELECT 'BSCS 1A', 'BS Computer Science', '1st Year' UNION ALL
    SELECT 'BSCS 1B', 'BS Computer Science', '1st Year' UNION ALL
    SELECT 'BSCS 2A', 'BS Computer Science', '2nd Year' UNION ALL
    SELECT 'BSCS 2B', 'BS Computer Science', '2nd Year' UNION ALL
    SELECT 'BSCS 3A', 'BS Computer Science', '3rd Year' UNION ALL
    SELECT 'BSCS 3B', 'BS Computer Science', '3rd Year' UNION ALL
    SELECT 'BSCS 4A', 'BS Computer Science', '4th Year' UNION ALL
    SELECT 'BSCS 4B', 'BS Computer Science', '4th Year' UNION ALL

    SELECT 'BSIS 1A', 'BS Information Systems', '1st Year' UNION ALL
    SELECT 'BSIS 1B', 'BS Information Systems', '1st Year' UNION ALL
    SELECT 'BSIS 2A', 'BS Information Systems', '2nd Year' UNION ALL
    SELECT 'BSIS 2B', 'BS Information Systems', '2nd Year' UNION ALL
    SELECT 'BSIS 3A', 'BS Information Systems', '3rd Year' UNION ALL
    SELECT 'BSIS 3B', 'BS Information Systems', '3rd Year' UNION ALL
    SELECT 'BSIS 4A', 'BS Information Systems', '4th Year' UNION ALL
    SELECT 'BSIS 4B', 'BS Information Systems', '4th Year' UNION ALL

    SELECT 'BSEd 1A', 'BS Education', '1st Year' UNION ALL
    SELECT 'BSEd 1B', 'BS Education', '1st Year' UNION ALL
    SELECT 'BSEd 2A', 'BS Education', '2nd Year' UNION ALL
    SELECT 'BSEd 2B', 'BS Education', '2nd Year' UNION ALL
    SELECT 'BSEd 3A', 'BS Education', '3rd Year' UNION ALL
    SELECT 'BSEd 3B', 'BS Education', '3rd Year' UNION ALL
    SELECT 'BSEd 4A', 'BS Education', '4th Year' UNION ALL
    SELECT 'BSEd 4B', 'BS Education', '4th Year' UNION ALL

    SELECT 'BSBA 1A', 'BS Business Administration', '1st Year' UNION ALL
    SELECT 'BSBA 1B', 'BS Business Administration', '1st Year' UNION ALL
    SELECT 'BSBA 2A', 'BS Business Administration', '2nd Year' UNION ALL
    SELECT 'BSBA 2B', 'BS Business Administration', '2nd Year' UNION ALL
    SELECT 'BSBA 3A', 'BS Business Administration', '3rd Year' UNION ALL
    SELECT 'BSBA 3B', 'BS Business Administration', '3rd Year' UNION ALL
    SELECT 'BSBA 4A', 'BS Business Administration', '4th Year' UNION ALL
    SELECT 'BSBA 4B', 'BS Business Administration', '4th Year'
) AS v
WHERE NOT EXISTS (
    SELECT 1 FROM `class` c WHERE c.`section_name` = v.`section_name`
);

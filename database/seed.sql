-- RESC-QR Seed Data
USE `resc_qr`;

-- Admin account (password: Admin@123)
INSERT INTO `admin` (`name`, `email`, `password_hash`, `role`) VALUES
('System Administrator', 'admin@usep.edu.ph', '$2y$12$ukma1BJNZxv53Ja2ISTjnu6W0mo0ZithOCBe8NLtX.Ij6jIpYyqMS', 'admin');

-- Classes / Sections
INSERT INTO `class` (`section_name`, `program`, `year_level`) VALUES
('BSIT 1A', 'BS Information Technology', '1st Year'),
('BSIT 1B', 'BS Information Technology', '1st Year'),
('BSIT 2A', 'BS Information Technology', '2nd Year'),
('BSCS 1A', 'BS Computer Science', '1st Year'),
('BSCS 2A', 'BS Computer Science', '2nd Year');

-- Class Mayors (password: Mayor@123)
INSERT INTO `class_mayor` (`class_id`, `name`, `email`, `password_hash`, `phone`) VALUES
(1, 'Maria Santos', 'maria.santos@usep.edu.ph', '$2y$12$ukma1BJNZxv53Ja2ISTjnu6W0mo0ZithOCBe8NLtX.Ij6jIpYyqMS', '09171234567'),
(2, 'Juan Dela Cruz', 'juan.delacruz@usep.edu.ph', '$2y$12$ukma1BJNZxv53Ja2ISTjnu6W0mo0ZithOCBe8NLtX.Ij6jIpYyqMS', '09181234567'),
(3, 'Ana Reyes', 'ana.reyes@usep.edu.ph', '$2y$12$ukma1BJNZxv53Ja2ISTjnu6W0mo0ZithOCBe8NLtX.Ij6jIpYyqMS', '09191234567');

-- Students
INSERT INTO `student` (`class_id`, `first_name`, `last_name`, `email`, `phone`, `course`, `year_level`, `qr_code_value`, `profile_status`) VALUES
(1, 'Carlos', 'Garcia', 'carlos.garcia@usep.edu.ph', '09201111111', 'BSIT', '1st Year', 'RESC-STU-00001', 'Active'),
(1, 'Sophia', 'Mendoza', 'sophia.mendoza@usep.edu.ph', '09202222222', 'BSIT', '1st Year', 'RESC-STU-00002', 'Active'),
(1, 'Miguel', 'Ramos', 'miguel.ramos@usep.edu.ph', '09203333333', 'BSIT', '1st Year', 'RESC-STU-00003', 'Active'),
(1, 'Isabella', 'Torres', 'isabella.torres@usep.edu.ph', '09204444444', 'BSIT', '1st Year', 'RESC-STU-00004', 'Active'),
(1, 'Gabriel', 'Villanueva', 'gabriel.villanueva@usep.edu.ph', '09205555555', 'BSIT', '1st Year', 'RESC-STU-00005', 'Active'),
(2, 'Patricia', 'Lim', 'patricia.lim@usep.edu.ph', '09206666666', 'BSIT', '1st Year', 'RESC-STU-00006', 'Active'),
(2, 'Rafael', 'Cruz', 'rafael.cruz@usep.edu.ph', '09207777777', 'BSIT', '1st Year', 'RESC-STU-00007', 'Active'),
(2, 'Andrea', 'Santos', 'andrea.santos@usep.edu.ph', '09208888888', 'BSIT', '1st Year', 'RESC-STU-00008', 'Active'),
(3, 'Daniel', 'Fernandez', 'daniel.fernandez@usep.edu.ph', '09209999999', 'BSIT', '2nd Year', 'RESC-STU-00009', 'Active'),
(3, 'Camille', 'Rivera', 'camille.rivera@usep.edu.ph', '09210000000', 'BSIT', '2nd Year', 'RESC-STU-00010', 'Active'),
(4, 'Joshua', 'Navarro', 'joshua.navarro@usep.edu.ph', '09211111111', 'BSCS', '1st Year', 'RESC-STU-00011', 'Active'),
(4, 'Samantha', 'Bautista', 'samantha.bautista@usep.edu.ph', '09212222222', 'BSCS', '1st Year', 'RESC-STU-00012', 'Active'),
(5, 'Ethan', 'Castillo', 'ethan.castillo@usep.edu.ph', '09213333333', 'BSCS', '2nd Year', 'RESC-STU-00013', 'Active'),
(5, 'Nicole', 'Aquino', 'nicole.aquino@usep.edu.ph', '09214444444', 'BSCS', '2nd Year', 'RESC-STU-00014', 'Active'),
(5, 'Christian', 'Pascual', 'christian.pascual@usep.edu.ph', '09215555555', 'BSCS', '2nd Year', 'RESC-STU-00015', 'Active');

-- Emergency Contacts
INSERT INTO `emergency_contact` (`student_id`, `contact_name`, `relationship`, `phone_number`) VALUES
(1, 'Roberto Garcia', 'Father', '09301111111'),
(2, 'Elena Mendoza', 'Mother', '09302222222'),
(3, 'Pedro Ramos', 'Father', '09303333333'),
(4, 'Maria Torres', 'Mother', '09304444444'),
(5, 'Jose Villanueva', 'Father', '09305555555');

-- ============================================================================
--  CLINIC MANAGEMENT SYSTEM  -  complete database with demo data
--  ---------------------------------------------------------------------------
--  HOW TO USE THIS FILE
--    1. Open phpMyAdmin  ->  http://localhost/phpmyadmin
--    2. Create a database called:  clinic_system   (collation utf8mb4_unicode_ci)
--    3. Select it, open the "Import" tab, choose this file and press "Go".
--
--  Every password in this file is:  password123
--
--  All dates are written relative to CURDATE(), so "today's appointments"
--  always contains data no matter which day you import the file.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `medicine_prescription`;
DROP TABLE IF EXISTS `prescriptions`;
DROP TABLE IF EXISTS `analyses`;
DROP TABLE IF EXISTS `medical_files`;
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `doctor_schedules`;
DROP TABLE IF EXISTS `doctors`;
DROP TABLE IF EXISTS `patients`;
DROP TABLE IF EXISTS `medicines`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `password_reset_tokens`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `migrations`;

-- ----------------------------------------------------------------------------
-- migrations : Laravel's own bookkeeping table. It is pre-filled so that
--              "php artisan migrate" knows the tables already exist.
-- ----------------------------------------------------------------------------
CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- users : every account in the system (admin / doctor / reception / patient)
-- ----------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `role` enum('admin','doctor','reception','patient') NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- departments
-- ----------------------------------------------------------------------------
CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- doctors : one row per user whose role is "doctor"
-- ----------------------------------------------------------------------------
CREATE TABLE `doctors` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `specialization` varchar(191) NOT NULL,
  `consultation_fee` decimal(8,2) NOT NULL,
  `bio` text,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctors_user_id_unique` (`user_id`),
  KEY `doctors_department_id_foreign` (`department_id`),
  CONSTRAINT `doctors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `doctors_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- doctor_schedules : weekly working hours
-- ----------------------------------------------------------------------------
CREATE TABLE `doctor_schedules` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint UNSIGNED NOT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_schedules_doctor_id_foreign` (`doctor_id`),
  CONSTRAINT `doctor_schedules_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- patients : one row per user whose role is "patient"
-- ----------------------------------------------------------------------------
CREATE TABLE `patients` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `blood_group` varchar(191) DEFAULT NULL,
  `address` text,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_user_id_unique` (`user_id`),
  CONSTRAINT `patients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- appointments : the central table of the whole system
-- ----------------------------------------------------------------------------
CREATE TABLE `appointments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` bigint UNSIGNED NOT NULL,
  `doctor_id` bigint UNSIGNED NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','accepted','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  `cancel_reason` text,
  `symptoms` text,
  `diagnosis` text,
  `notes` text,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointments_patient_id_foreign` (`patient_id`),
  KEY `appointments_doctor_id_foreign` (`doctor_id`),
  KEY `appointments_appointment_date_status_index` (`appointment_date`,`status`),
  CONSTRAINT `appointments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- medicines : the catalogue the doctor chooses from
-- ----------------------------------------------------------------------------
CREATE TABLE `medicines` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `description` text,
  `category` varchar(191) DEFAULT NULL,
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medicines_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- prescriptions : one per completed appointment
-- ----------------------------------------------------------------------------
CREATE TABLE `prescriptions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint UNSIGNED NOT NULL,
  `doctor_id` bigint UNSIGNED NOT NULL,
  `patient_id` bigint UNSIGNED NOT NULL,
  `instructions` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescriptions_appointment_id_unique` (`appointment_id`),
  KEY `prescriptions_doctor_id_foreign` (`doctor_id`),
  KEY `prescriptions_patient_id_foreign` (`patient_id`),
  CONSTRAINT `prescriptions_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- medicine_prescription : the MANY-TO-MANY pivot table
--   the dosage / frequency / duration belong to the LINK, not to the medicine
-- ----------------------------------------------------------------------------
CREATE TABLE `medicine_prescription` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `prescription_id` bigint UNSIGNED NOT NULL,
  `medicine_id` bigint UNSIGNED NOT NULL,
  `dosage` varchar(191) NOT NULL,
  `frequency` varchar(191) NOT NULL,
  `duration` varchar(191) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medicine_prescription_prescription_id_foreign` (`prescription_id`),
  KEY `medicine_prescription_medicine_id_foreign` (`medicine_id`),
  CONSTRAINT `medicine_prescription_prescription_id_foreign` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medicine_prescription_medicine_id_foreign` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- medical_files : uploaded by the DOCTOR / ADMIN about a patient
-- ----------------------------------------------------------------------------
CREATE TABLE `medical_files` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` bigint UNSIGNED NOT NULL,
  `uploaded_by` bigint UNSIGNED NOT NULL,
  `title` varchar(191) NOT NULL,
  `file_path` varchar(191) NOT NULL,
  `file_type` enum('lab_result','x_ray','prescription_scan','other') NOT NULL DEFAULT 'other',
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medical_files_patient_id_foreign` (`patient_id`),
  KEY `medical_files_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `medical_files_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `medical_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- analyses : uploaded by the PATIENT (blood test, x-ray ...)
-- ----------------------------------------------------------------------------
CREATE TABLE `analyses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `patient_id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(191) NOT NULL,
  `file_name` varchar(191) NOT NULL,
  `file_path` varchar(191) NOT NULL,
  `file_type` varchar(191) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `analyses_patient_id_foreign` (`patient_id`),
  KEY `analyses_appointment_id_foreign` (`appointment_id`),
  CONSTRAINT `analyses_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `analyses_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- activity_logs : simple audit trail (bonus feature)
-- ----------------------------------------------------------------------------
CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(191) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2025_01_01_000001_create_departments_table', 1),
(3, '2025_01_01_000002_create_doctors_table', 1),
(4, '2025_01_01_000003_create_doctor_schedules_table', 1),
(5, '2025_01_01_000004_create_patients_table', 1),
(6, '2025_01_01_000005_create_appointments_table', 1),
(7, '2025_01_01_000006_create_medicines_table', 1),
(8, '2025_01_01_000007_create_prescriptions_table', 1),
(9, '2025_01_01_000008_create_medicine_prescription_table', 1),
(10, '2025_01_01_000009_create_medical_files_table', 1),
(11, '2025_01_01_000010_create_analyses_table', 1),
(12, '2025_01_01_000011_create_activity_logs_table', 1);


INSERT INTO `departments` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Cardiology', 'Diagnosis and treatment of heart and blood vessel conditions.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(2, 'Dentistry', 'Care of the teeth, gums and mouth.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(3, 'Orthopedics', 'Bones, joints, muscles and sports injuries.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(4, 'Dermatology', 'Skin, hair and nail conditions.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(5, 'Pediatrics', 'Medical care for infants, children and teenagers.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(6, 'Neurology', 'Disorders of the brain, spine and nervous system.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(7, 'Ophthalmology', 'Eye examinations and vision care.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(8, 'ENT', 'Ear, nose and throat conditions.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY));


INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01000000001', 'admin', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(2, 'Mona Adel', 'reception@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01000000002', 'reception', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(3, 'Hoda Kamal', 'reception2@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01000000003', 'reception', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(4, 'Mohamed Ali', 'mohamed@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01012345678', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(5, 'Sara Ahmed', 'sara@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01123456789', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(6, 'Ahmed Hassan', 'ahmed.d@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01234567890', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(7, 'Youssef Omar', 'youssef@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01512345678', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(8, 'Nada Ibrahim', 'nada@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01098765432', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(9, 'Khaled Mostafa', 'khaled@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01187654321', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(10, 'Mariam Fouad', 'mariam@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01276543210', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(11, 'Tarek Samir', 'tarek@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01565432109', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(12, 'Laila Hassan', 'laila@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01055554444', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(13, 'Omar Farouk', 'omar@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01166663333', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(14, 'Heba Nasser', 'heba@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01277772222', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(15, 'Amr Zaki', 'amr@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01588881111', 'doctor', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(16, 'Ahmed Ali', 'ahmed@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01011112222', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(17, 'Sara Mohamed', 'sara.p@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01022223333', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(18, 'Omar Hassan', 'omar.p@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01033334444', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(19, 'Mona Sayed', 'mona@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01044445555', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(20, 'Kareem Adel', 'kareem@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01055556666', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(21, 'Yasmin Tarek', 'yasmin@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01066667777', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(22, 'Hassan Mahmoud', 'hassan@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01077778888', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(23, 'Fatma Gamal', 'fatma@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01088889999', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(24, 'Mostafa Nabil', 'mostafa@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01099990000', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(25, 'Aya Ashraf', 'aya@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01111112222', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(26, 'Ibrahim Fathy', 'ibrahim@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01122223333', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(27, 'Nourhan Salah', 'nourhan@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01133334444', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(28, 'Ali Ramadan', 'ali@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01144445555', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(29, 'Dina Wael', 'dina@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01155556666', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(30, 'Tamer Sobhy', 'tamer@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01166667777', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(31, 'Salma Ezzat', 'salma@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01177778888', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(32, 'Marwan Sherif', 'marwan@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01188889999', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(33, 'Rania Hosny', 'rania@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01199990000', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(34, 'Sherif Magdy', 'sherif@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01211112222', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(35, 'Hana Yasser', 'hana@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01222223333', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(36, 'Waleed Fahmy', 'waleed@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01233334444', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(37, 'Noha Sami', 'noha@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01244445555', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(38, 'Ziad Alaa', 'ziad@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01255556666', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(39, 'Amira Reda', 'amira@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01266667777', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(40, 'Mahmoud Anwar', 'mahmoud@clinic.com', '$2y$12$yGm3U/ILZ.6DG0zpchQHruc4St4IOG4Oqf5AtnIyCG/2KYd3mxEXe', '01277778888', 'patient', 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY));


INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `consultation_fee`, `bio`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'Interventional Cardiology', '350.00', 'Dr. Mohamed Ali is a specialist in Interventional Cardiology at the Cardiology department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(2, 5, 2, 'Cosmetic Dentistry', '250.00', 'Dr. Sara Ahmed is a specialist in Cosmetic Dentistry at the Dentistry department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(3, 6, 3, 'Joint Replacement', '400.00', 'Dr. Ahmed Hassan is a specialist in Joint Replacement at the Orthopedics department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(4, 7, 4, 'Cosmetic Dermatology', '300.00', 'Dr. Youssef Omar is a specialist in Cosmetic Dermatology at the Dermatology department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(5, 8, 5, 'Neonatal Care', '280.00', 'Dr. Nada Ibrahim is a specialist in Neonatal Care at the Pediatrics department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(6, 9, 6, 'Epilepsy & Seizures', '450.00', 'Dr. Khaled Mostafa is a specialist in Epilepsy & Seizures at the Neurology department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(7, 10, 7, 'Retina Surgery', '380.00', 'Dr. Mariam Fouad is a specialist in Retina Surgery at the Ophthalmology department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(8, 11, 8, 'Sinus Surgery', '320.00', 'Dr. Tarek Samir is a specialist in Sinus Surgery at the ENT department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(9, 12, 1, 'Heart Failure', '360.00', 'Dr. Laila Hassan is a specialist in Heart Failure at the Cardiology department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(10, 13, 3, 'Sports Injuries', '340.00', 'Dr. Omar Farouk is a specialist in Sports Injuries at the Orthopedics department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(11, 14, 5, 'Childhood Allergies', '260.00', 'Dr. Heba Nasser is a specialist in Childhood Allergies at the Pediatrics department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(12, 15, 4, 'Skin Cancer Screening', '310.00', 'Dr. Amr Zaki is a specialist in Skin Cancer Screening at the Dermatology department.', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY));


INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(2, 1, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(3, 1, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(4, 1, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(5, 1, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(6, 2, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(7, 2, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(8, 2, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(9, 2, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(10, 2, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(11, 3, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(12, 3, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(13, 3, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(14, 3, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(15, 3, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(16, 4, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(17, 4, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(18, 4, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(19, 4, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(20, 4, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(21, 5, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(22, 5, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(23, 5, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(24, 5, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(25, 5, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(26, 6, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(27, 6, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(28, 6, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(29, 6, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(30, 6, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(31, 7, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(32, 7, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(33, 7, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(34, 7, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(35, 7, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(36, 8, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(37, 8, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(38, 8, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(39, 8, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(40, 8, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(41, 9, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(42, 9, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(43, 9, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(44, 9, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(45, 9, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(46, 10, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(47, 10, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(48, 10, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(49, 10, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(50, 10, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(51, 11, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(52, 11, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(53, 11, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(54, 11, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(55, 11, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(56, 12, 'Sunday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(57, 12, 'Monday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(58, 12, 'Tuesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(59, 12, 'Wednesday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(60, 12, 'Thursday', '09:00:00', '15:00:00', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY));


INSERT INTO `patients` (`id`, `user_id`, `dob`, `gender`, `blood_group`, `address`, `created_at`, `updated_at`) VALUES
(1, 16, '1995-04-12', 'male', 'O+', 'Nasr City, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(2, 17, '1990-09-30', 'female', 'A+', 'Maadi, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(3, 18, '1988-01-22', 'male', 'B+', 'Dokki, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(4, 19, '2000-07-08', 'female', 'AB+', 'Heliopolis, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(5, 20, '1975-11-19', 'male', 'O-', 'Zamalek, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(6, 21, '1998-03-05', 'female', 'A-', 'Mohandessin, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(7, 22, '1982-06-27', 'male', 'B-', '6th of October City', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(8, 23, '1993-12-14', 'female', 'O+', 'Shubra, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(9, 24, '1979-02-09', 'male', 'A+', 'New Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(10, 25, '2002-08-21', 'female', 'AB-', 'Faisal, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(11, 26, '1968-05-03', 'male', 'O+', 'Helwan, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(12, 27, '1996-10-17', 'female', 'B+', 'Agouza, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(13, 28, '1985-04-29', 'male', 'A+', 'Sheikh Zayed', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(14, 29, '1991-01-11', 'female', 'O-', 'Rehab City, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(15, 30, '1973-09-24', 'male', 'AB+', 'Haram, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(16, 31, '2004-06-16', 'female', 'A+', 'Manial, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(17, 32, '1999-03-30', 'male', 'B+', 'Obour City', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(18, 33, '1987-07-13', 'female', 'O+', 'Madinat Nasr, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(19, 34, '1994-11-02', 'male', 'A-', 'Giza Square, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(20, 35, '2001-02-25', 'female', 'B-', 'Katameya, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(21, 36, '1980-08-07', 'male', 'O+', 'Imbaba, Giza', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(22, 37, '1992-05-19', 'female', 'A+', 'Ain Shams, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(23, 38, '2006-12-01', 'male', 'AB+', 'Badr City', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(24, 39, '1977-10-09', 'female', 'O-', 'Sayeda Zeinab, Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(25, 40, '1989-06-04', 'male', 'B+', 'Tagamoa, New Cairo', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY));


INSERT INTO `medicines` (`id`, `name`, `description`, `category`, `price`, `quantity`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Panadol', 'Paracetamol 500 mg tablets for pain and fever.', 'Painkiller', '15.00', 480, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(2, 'Augmentin 1g', 'Amoxicillin + clavulanic acid 1 g tablets.', 'Antibiotic', '85.50', 210, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(3, 'Brufen 400', 'Ibuprofen 400 mg anti-inflammatory tablets.', 'Painkiller', '28.00', 350, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(4, 'Voltaren 50', 'Diclofenac 50 mg tablets for joint pain.', 'Painkiller', '32.00', 260, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(5, 'Amoxil 500', 'Amoxicillin 500 mg capsules.', 'Antibiotic', '45.00', 300, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(6, 'Zithromax 500', 'Azithromycin 500 mg tablets.', 'Antibiotic', '95.00', 140, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(7, 'Vitamin C 1000', 'Vitamin C 1000 mg effervescent tablets.', 'Vitamin', '40.00', 520, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(8, 'Vitamin D3', 'Cholecalciferol 5000 IU capsules.', 'Vitamin', '60.00', 310, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(9, 'Ferrous Sulfate', 'Iron supplement for anaemia.', 'Supplement', '35.00', 240, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(10, 'Omega 3', 'Fish oil 1000 mg soft gel capsules.', 'Supplement', '75.00', 190, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(11, 'Concor 5', 'Bisoprolol 5 mg tablets for blood pressure.', 'Cardiac', '52.00', 180, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(12, 'Aspocid 75', 'Low dose aspirin 75 mg for blood thinning.', 'Cardiac', '12.00', 420, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(13, 'Lipitor 20', 'Atorvastatin 20 mg tablets for cholesterol.', 'Cardiac', '98.00', 160, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(14, 'Glucophage 500', 'Metformin 500 mg tablets for diabetes.', 'Diabetes', '30.00', 380, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(15, 'Lantus', 'Insulin glargine injection pen.', 'Diabetes', '320.00', 60, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(16, 'Ventolin', 'Salbutamol inhaler for asthma.', 'Respiratory', '55.00', 145, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(17, 'Claritine', 'Loratadine 10 mg antihistamine tablets.', 'Allergy', '38.00', 270, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(18, 'Zyrtec', 'Cetirizine 10 mg antihistamine tablets.', 'Allergy', '42.00', 230, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(19, 'Nexium 40', 'Esomeprazole 40 mg for stomach acid.', 'Digestive', '88.00', 200, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(20, 'Buscopan', 'Hyoscine butylbromide for stomach cramps.', 'Digestive', '26.00', 290, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(21, 'Antinal', 'Nifuroxazide capsules for diarrhoea.', 'Digestive', '22.00', 330, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(22, 'Betadine', 'Povidone iodine antiseptic solution.', 'Antiseptic', '30.00', 410, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(23, 'Fucidin Cream', 'Fusidic acid 2% topical antibiotic cream.', 'Dermatology', '48.00', 175, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY)),
(24, 'Tobradex', 'Tobramycin + dexamethasone eye drops.', 'Ophthalmic', '66.00', 120, 'active', DATE_ADD(NOW(), INTERVAL -180 DAY), DATE_ADD(NOW(), INTERVAL -180 DAY));


INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `cancel_reason`, `symptoms`, `diagnosis`, `notes`, `created_at`, `updated_at`) VALUES
(1, 21, 2, DATE_ADD(CURDATE(), INTERVAL -60 DAY), '11:30:00', 'rejected', 'The doctor is not available at this time', 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -63 DAY), DATE_ADD(NOW(), INTERVAL -60 DAY)),
(2, 12, 5, DATE_ADD(CURDATE(), INTERVAL -59 DAY), '09:30:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -62 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(3, 2, 6, DATE_ADD(CURDATE(), INTERVAL -59 DAY), '13:30:00', 'completed', NULL, 'Itchy red rash on both arms.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -62 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(4, 11, 2, DATE_ADD(CURDATE(), INTERVAL -59 DAY), '13:30:00', 'completed', NULL, 'Blurred vision when reading.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -62 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(5, 15, 5, DATE_ADD(CURDATE(), INTERVAL -58 DAY), '09:00:00', 'cancelled', 'Cancelled by the patient', 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -61 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(6, 2, 11, DATE_ADD(CURDATE(), INTERVAL -58 DAY), '12:00:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -61 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(7, 11, 7, DATE_ADD(CURDATE(), INTERVAL -58 DAY), '11:30:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -61 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(8, 15, 3, DATE_ADD(CURDATE(), INTERVAL -57 DAY), '12:30:00', 'cancelled', 'Cancelled by the patient', 'Sore throat and difficulty swallowing.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -60 DAY), DATE_ADD(NOW(), INTERVAL -57 DAY)),
(9, 3, 9, DATE_ADD(CURDATE(), INTERVAL -57 DAY), '11:00:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -60 DAY), DATE_ADD(NOW(), INTERVAL -57 DAY)),
(10, 8, 10, DATE_ADD(CURDATE(), INTERVAL -56 DAY), '09:00:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -56 DAY)),
(11, 25, 12, DATE_ADD(CURDATE(), INTERVAL -55 DAY), '09:00:00', 'completed', NULL, 'Chest pain and shortness of breath for three days.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(12, 6, 2, DATE_ADD(CURDATE(), INTERVAL -55 DAY), '13:00:00', 'completed', NULL, 'Knee pain after playing football.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(13, 3, 3, DATE_ADD(CURDATE(), INTERVAL -55 DAY), '10:00:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Mild Hypertension', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(14, 19, 2, DATE_ADD(CURDATE(), INTERVAL -54 DAY), '09:00:00', 'rejected', 'The doctor is not available at this time', 'Follow-up visit for blood pressure control.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -57 DAY), DATE_ADD(NOW(), INTERVAL -54 DAY)),
(15, 11, 2, DATE_ADD(CURDATE(), INTERVAL -54 DAY), '10:30:00', 'completed', NULL, 'Chest pain and shortness of breath for three days.', 'Conjunctivitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -57 DAY), DATE_ADD(NOW(), INTERVAL -54 DAY)),
(16, 8, 6, DATE_ADD(CURDATE(), INTERVAL -53 DAY), '10:30:00', 'completed', NULL, 'Itchy red rash on both arms.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(17, 4, 10, DATE_ADD(CURDATE(), INTERVAL -53 DAY), '13:00:00', 'cancelled', 'Cancelled by the patient', 'Knee pain after playing football.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(18, 9, 7, DATE_ADD(CURDATE(), INTERVAL -53 DAY), '09:00:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(19, 21, 5, DATE_ADD(CURDATE(), INTERVAL -52 DAY), '11:00:00', 'completed', NULL, 'Stomach pain after meals.', 'Gastritis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -52 DAY)),
(20, 10, 9, DATE_ADD(CURDATE(), INTERVAL -52 DAY), '11:30:00', 'cancelled', 'Cancelled by the patient', 'Severe toothache on the lower right side.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -52 DAY)),
(21, 9, 10, DATE_ADD(CURDATE(), INTERVAL -51 DAY), '09:30:00', 'rejected', 'The doctor is not available at this time', 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -54 DAY), DATE_ADD(NOW(), INTERVAL -51 DAY)),
(22, 3, 11, DATE_ADD(CURDATE(), INTERVAL -51 DAY), '11:30:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Mild Hypertension', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -54 DAY), DATE_ADD(NOW(), INTERVAL -51 DAY)),
(23, 24, 8, DATE_ADD(CURDATE(), INTERVAL -50 DAY), '11:30:00', 'completed', NULL, 'Stomach pain after meals.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -50 DAY)),
(24, 7, 3, DATE_ADD(CURDATE(), INTERVAL -50 DAY), '11:30:00', 'cancelled', 'Cancelled by the patient', 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -50 DAY)),
(25, 22, 5, DATE_ADD(CURDATE(), INTERVAL -49 DAY), '12:00:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -52 DAY), DATE_ADD(NOW(), INTERVAL -49 DAY)),
(26, 16, 1, DATE_ADD(CURDATE(), INTERVAL -48 DAY), '11:30:00', 'completed', NULL, 'Blurred vision when reading.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -51 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(27, 15, 4, DATE_ADD(CURDATE(), INTERVAL -48 DAY), '09:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -51 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(28, 18, 5, DATE_ADD(CURDATE(), INTERVAL -47 DAY), '13:00:00', 'completed', NULL, 'Knee pain after playing football.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -50 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(29, 1, 2, DATE_ADD(CURDATE(), INTERVAL -47 DAY), '11:30:00', 'completed', NULL, 'Chest pain and shortness of breath for three days.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -50 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(30, 12, 9, DATE_ADD(CURDATE(), INTERVAL -47 DAY), '12:00:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -50 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(31, 4, 10, DATE_ADD(CURDATE(), INTERVAL -46 DAY), '11:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Gastritis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -49 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(32, 16, 11, DATE_ADD(CURDATE(), INTERVAL -46 DAY), '11:30:00', 'completed', NULL, 'Stomach pain after meals.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -49 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(33, 7, 4, DATE_ADD(CURDATE(), INTERVAL -45 DAY), '13:00:00', 'cancelled', 'Cancelled by the patient', 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(34, 9, 1, DATE_ADD(CURDATE(), INTERVAL -45 DAY), '13:30:00', 'completed', NULL, 'Skin irritation after using a new cream.', 'Mild Hypertension', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(35, 4, 2, DATE_ADD(CURDATE(), INTERVAL -45 DAY), '09:30:00', 'completed', NULL, 'Blurred vision when reading.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(36, 11, 5, DATE_ADD(CURDATE(), INTERVAL -44 DAY), '09:00:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(37, 19, 12, DATE_ADD(CURDATE(), INTERVAL -44 DAY), '13:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Acute Tonsillitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(38, 24, 2, DATE_ADD(CURDATE(), INTERVAL -44 DAY), '09:00:00', 'completed', NULL, 'Knee pain after playing football.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(39, 2, 4, DATE_ADD(CURDATE(), INTERVAL -43 DAY), '13:00:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -43 DAY)),
(40, 16, 4, DATE_ADD(CURDATE(), INTERVAL -42 DAY), '09:00:00', 'cancelled', 'Cancelled by the patient', 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -42 DAY)),
(41, 23, 9, DATE_ADD(CURDATE(), INTERVAL -42 DAY), '09:30:00', 'rejected', 'The doctor is not available at this time', 'Sore throat and difficulty swallowing.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -42 DAY)),
(42, 7, 11, DATE_ADD(CURDATE(), INTERVAL -42 DAY), '11:00:00', 'rejected', 'The doctor is not available at this time', 'Stomach pain after meals.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -42 DAY)),
(43, 15, 4, DATE_ADD(CURDATE(), INTERVAL -41 DAY), '13:30:00', 'cancelled', 'Cancelled by the patient', 'Knee pain after playing football.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -41 DAY)),
(44, 5, 4, DATE_ADD(CURDATE(), INTERVAL -41 DAY), '12:30:00', 'rejected', 'The doctor is not available at this time', 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -41 DAY)),
(45, 24, 11, DATE_ADD(CURDATE(), INTERVAL -40 DAY), '12:00:00', 'completed', NULL, 'Blurred vision when reading.', 'Conjunctivitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -43 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(46, 22, 11, DATE_ADD(CURDATE(), INTERVAL -40 DAY), '09:30:00', 'completed', NULL, 'Frequent headaches and dizziness.', 'Acute Tonsillitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -43 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(47, 8, 8, DATE_ADD(CURDATE(), INTERVAL -39 DAY), '10:00:00', 'rejected', 'The doctor is not available at this time', 'Follow-up visit for blood pressure control.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -42 DAY), DATE_ADD(NOW(), INTERVAL -39 DAY)),
(48, 6, 6, DATE_ADD(CURDATE(), INTERVAL -39 DAY), '11:30:00', 'rejected', 'The doctor is not available at this time', 'Stomach pain after meals.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -42 DAY), DATE_ADD(NOW(), INTERVAL -39 DAY)),
(49, 5, 4, DATE_ADD(CURDATE(), INTERVAL -39 DAY), '10:30:00', 'rejected', 'The doctor is not available at this time', 'Knee pain after playing football.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -42 DAY), DATE_ADD(NOW(), INTERVAL -39 DAY)),
(50, 16, 6, DATE_ADD(CURDATE(), INTERVAL -38 DAY), '12:30:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Contact Dermatitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -41 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(51, 7, 6, DATE_ADD(CURDATE(), INTERVAL -38 DAY), '12:00:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Conjunctivitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -41 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(52, 17, 2, DATE_ADD(CURDATE(), INTERVAL -37 DAY), '09:00:00', 'completed', NULL, 'Itchy red rash on both arms.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(53, 11, 8, DATE_ADD(CURDATE(), INTERVAL -37 DAY), '11:30:00', 'completed', NULL, 'Knee pain after playing football.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(54, 7, 1, DATE_ADD(CURDATE(), INTERVAL -37 DAY), '12:00:00', 'completed', NULL, 'Skin irritation after using a new cream.', 'Conjunctivitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(55, 13, 2, DATE_ADD(CURDATE(), INTERVAL -36 DAY), '09:30:00', 'cancelled', 'Cancelled by the patient', 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -39 DAY), DATE_ADD(NOW(), INTERVAL -36 DAY)),
(56, 11, 6, DATE_ADD(CURDATE(), INTERVAL -35 DAY), '11:30:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Contact Dermatitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -38 DAY), DATE_ADD(NOW(), INTERVAL -35 DAY)),
(57, 14, 11, DATE_ADD(CURDATE(), INTERVAL -34 DAY), '09:30:00', 'completed', NULL, 'Itchy red rash on both arms.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -34 DAY)),
(58, 15, 12, DATE_ADD(CURDATE(), INTERVAL -33 DAY), '13:30:00', 'completed', NULL, 'Frequent headaches and dizziness.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -36 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(59, 22, 9, DATE_ADD(CURDATE(), INTERVAL -33 DAY), '09:30:00', 'cancelled', 'Cancelled by the patient', 'Sore throat and difficulty swallowing.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -36 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(60, 25, 10, DATE_ADD(CURDATE(), INTERVAL -33 DAY), '09:30:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Gastritis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -36 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(61, 3, 9, DATE_ADD(CURDATE(), INTERVAL -32 DAY), '09:00:00', 'completed', NULL, 'Frequent headaches and dizziness.', 'Contact Dermatitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -35 DAY), DATE_ADD(NOW(), INTERVAL -32 DAY)),
(62, 14, 8, DATE_ADD(CURDATE(), INTERVAL -31 DAY), '09:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Acute Tonsillitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -34 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(63, 13, 11, DATE_ADD(CURDATE(), INTERVAL -31 DAY), '13:30:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -34 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(64, 2, 9, DATE_ADD(CURDATE(), INTERVAL -30 DAY), '11:30:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -30 DAY)),
(65, 23, 4, DATE_ADD(CURDATE(), INTERVAL -29 DAY), '11:30:00', 'completed', NULL, 'Chest pain and shortness of breath for three days.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -32 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(66, 25, 6, DATE_ADD(CURDATE(), INTERVAL -29 DAY), '12:30:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Seasonal Allergic Rhinitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -32 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(67, 22, 12, DATE_ADD(CURDATE(), INTERVAL -28 DAY), '10:00:00', 'completed', NULL, 'Stomach pain after meals.', 'Seasonal Allergic Rhinitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -28 DAY)),
(68, 19, 3, DATE_ADD(CURDATE(), INTERVAL -28 DAY), '13:00:00', 'rejected', 'The doctor is not available at this time', 'Frequent headaches and dizziness.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -28 DAY)),
(69, 7, 4, DATE_ADD(CURDATE(), INTERVAL -27 DAY), '13:30:00', 'cancelled', 'Cancelled by the patient', 'Frequent headaches and dizziness.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -27 DAY)),
(70, 8, 7, DATE_ADD(CURDATE(), INTERVAL -27 DAY), '11:00:00', 'cancelled', 'Cancelled by the patient', 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -27 DAY)),
(71, 10, 5, DATE_ADD(CURDATE(), INTERVAL -26 DAY), '10:30:00', 'cancelled', 'Cancelled by the patient', 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -26 DAY)),
(72, 22, 2, DATE_ADD(CURDATE(), INTERVAL -25 DAY), '11:00:00', 'rejected', 'The doctor is not available at this time', 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -28 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(73, 1, 9, DATE_ADD(CURDATE(), INTERVAL -25 DAY), '09:00:00', 'completed', NULL, 'Chest pain and shortness of breath for three days.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -28 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(74, 22, 8, DATE_ADD(CURDATE(), INTERVAL -24 DAY), '13:00:00', 'completed', NULL, 'High fever and a persistent cough.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -27 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(75, 15, 3, DATE_ADD(CURDATE(), INTERVAL -24 DAY), '10:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -27 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(76, 22, 12, DATE_ADD(CURDATE(), INTERVAL -24 DAY), '11:30:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Conjunctivitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -27 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(77, 25, 10, DATE_ADD(CURDATE(), INTERVAL -23 DAY), '09:00:00', 'completed', NULL, 'Knee pain after playing football.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -26 DAY), DATE_ADD(NOW(), INTERVAL -23 DAY)),
(78, 25, 10, DATE_ADD(CURDATE(), INTERVAL -23 DAY), '09:30:00', 'rejected', 'The doctor is not available at this time', 'Severe toothache on the lower right side.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -26 DAY), DATE_ADD(NOW(), INTERVAL -23 DAY)),
(79, 10, 9, DATE_ADD(CURDATE(), INTERVAL -22 DAY), '11:30:00', 'completed', NULL, 'Knee pain after playing football.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -25 DAY), DATE_ADD(NOW(), INTERVAL -22 DAY)),
(80, 17, 10, DATE_ADD(CURDATE(), INTERVAL -21 DAY), '10:00:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(81, 11, 12, DATE_ADD(CURDATE(), INTERVAL -21 DAY), '09:00:00', 'completed', NULL, 'Stomach pain after meals.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(82, 23, 7, DATE_ADD(CURDATE(), INTERVAL -21 DAY), '12:00:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(83, 23, 10, DATE_ADD(CURDATE(), INTERVAL -20 DAY), '11:00:00', 'completed', NULL, 'High fever and a persistent cough.', 'Seasonal Allergic Rhinitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -23 DAY), DATE_ADD(NOW(), INTERVAL -20 DAY)),
(84, 14, 1, DATE_ADD(CURDATE(), INTERVAL -19 DAY), '09:00:00', 'cancelled', 'Cancelled by the patient', 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -22 DAY), DATE_ADD(NOW(), INTERVAL -19 DAY)),
(85, 22, 3, DATE_ADD(CURDATE(), INTERVAL -19 DAY), '11:30:00', 'completed', NULL, 'Stomach pain after meals.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -22 DAY), DATE_ADD(NOW(), INTERVAL -19 DAY)),
(86, 17, 11, DATE_ADD(CURDATE(), INTERVAL -18 DAY), '10:00:00', 'cancelled', 'Cancelled by the patient', 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -18 DAY)),
(87, 2, 11, DATE_ADD(CURDATE(), INTERVAL -18 DAY), '13:00:00', 'cancelled', 'Cancelled by the patient', 'Itchy red rash on both arms.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -18 DAY)),
(88, 9, 2, DATE_ADD(CURDATE(), INTERVAL -17 DAY), '09:30:00', 'completed', NULL, 'Chest pain and shortness of breath for three days.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(89, 1, 12, DATE_ADD(CURDATE(), INTERVAL -17 DAY), '09:30:00', 'completed', NULL, 'Skin irritation after using a new cream.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(90, 9, 9, DATE_ADD(CURDATE(), INTERVAL -17 DAY), '12:00:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Contact Dermatitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(91, 18, 2, DATE_ADD(CURDATE(), INTERVAL -16 DAY), '13:30:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Acute Tonsillitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(92, 1, 7, DATE_ADD(CURDATE(), INTERVAL -16 DAY), '09:00:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Mild Hypertension', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(93, 3, 11, DATE_ADD(CURDATE(), INTERVAL -16 DAY), '11:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(94, 7, 10, DATE_ADD(CURDATE(), INTERVAL -15 DAY), '10:00:00', 'completed', NULL, 'Knee pain after playing football.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -18 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(95, 4, 12, DATE_ADD(CURDATE(), INTERVAL -15 DAY), '12:00:00', 'completed', NULL, 'High fever and a persistent cough.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -18 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(96, 14, 3, DATE_ADD(CURDATE(), INTERVAL -14 DAY), '12:30:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -14 DAY)),
(97, 23, 8, DATE_ADD(CURDATE(), INTERVAL -13 DAY), '12:00:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(98, 24, 7, DATE_ADD(CURDATE(), INTERVAL -13 DAY), '11:30:00', 'rejected', 'The doctor is not available at this time', 'Follow-up visit for blood pressure control.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(99, 21, 8, DATE_ADD(CURDATE(), INTERVAL -13 DAY), '13:00:00', 'completed', NULL, 'High fever and a persistent cough.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(100, 23, 2, DATE_ADD(CURDATE(), INTERVAL -12 DAY), '13:00:00', 'completed', NULL, 'Follow-up visit for blood pressure control.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(101, 24, 12, DATE_ADD(CURDATE(), INTERVAL -12 DAY), '09:30:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(102, 13, 5, DATE_ADD(CURDATE(), INTERVAL -11 DAY), '13:30:00', 'completed', NULL, 'High fever and a persistent cough.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -14 DAY), DATE_ADD(NOW(), INTERVAL -11 DAY)),
(103, 11, 3, DATE_ADD(CURDATE(), INTERVAL -10 DAY), '11:00:00', 'completed', NULL, 'Frequent headaches and dizziness.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -10 DAY)),
(104, 23, 2, DATE_ADD(CURDATE(), INTERVAL -9 DAY), '10:00:00', 'completed', NULL, 'Severe toothache on the lower right side.', 'Seasonal Allergic Rhinitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -9 DAY)),
(105, 7, 5, DATE_ADD(CURDATE(), INTERVAL -8 DAY), '10:30:00', 'completed', NULL, 'Blurred vision when reading.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -11 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(106, 10, 9, DATE_ADD(CURDATE(), INTERVAL -8 DAY), '09:00:00', 'completed', NULL, 'Sore throat and difficulty swallowing.', 'Seasonal Allergic Rhinitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -11 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(107, 21, 4, DATE_ADD(CURDATE(), INTERVAL -7 DAY), '11:00:00', 'completed', NULL, 'Knee pain after playing football.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(108, 7, 2, DATE_ADD(CURDATE(), INTERVAL -7 DAY), '12:00:00', 'completed', NULL, 'Itchy red rash on both arms.', 'Conjunctivitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(109, 19, 2, DATE_ADD(CURDATE(), INTERVAL -7 DAY), '09:30:00', 'completed', NULL, 'Skin irritation after using a new cream.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(110, 4, 4, DATE_ADD(CURDATE(), INTERVAL -6 DAY), '13:00:00', 'cancelled', 'Cancelled by the patient', 'Blurred vision when reading.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -9 DAY), DATE_ADD(NOW(), INTERVAL -6 DAY)),
(111, 17, 4, DATE_ADD(CURDATE(), INTERVAL -5 DAY), '13:30:00', 'completed', NULL, 'Knee pain after playing football.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(112, 17, 7, DATE_ADD(CURDATE(), INTERVAL -5 DAY), '09:30:00', 'completed', NULL, 'Skin irritation after using a new cream.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(113, 9, 1, DATE_ADD(CURDATE(), INTERVAL -4 DAY), '10:30:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Dental Caries', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(114, 15, 1, DATE_ADD(CURDATE(), INTERVAL -4 DAY), '13:30:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Contact Dermatitis', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(115, 24, 12, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '11:00:00', 'completed', NULL, 'Stomach pain after meals.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -6 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(116, 23, 4, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '09:30:00', 'completed', NULL, 'Stomach pain after meals.', 'Iron Deficiency Anaemia', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -6 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(117, 13, 6, DATE_ADD(CURDATE(), INTERVAL -3 DAY), '11:30:00', 'cancelled', 'Cancelled by the patient', 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -6 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(118, 2, 12, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '13:30:00', 'completed', NULL, 'Itchy red rash on both arms.', 'Migraine', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(119, 17, 1, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '09:00:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Type 2 Diabetes - follow up', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(120, 9, 5, DATE_ADD(CURDATE(), INTERVAL -2 DAY), '13:00:00', 'completed', NULL, 'Stomach pain after meals.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(121, 7, 3, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '10:00:00', 'completed', NULL, 'Lower back pain when standing for a long time.', 'Influenza', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(122, 21, 5, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '13:30:00', 'rejected', 'The doctor is not available at this time', 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(123, 6, 5, DATE_ADD(CURDATE(), INTERVAL -1 DAY), '11:00:00', 'completed', NULL, 'Frequent headaches and dizziness.', 'Lower Back Strain', 'Patient examined. Vital signs are within the normal range.', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(124, 5, 8, CURDATE(), '11:00:00', 'accepted', NULL, 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(125, 25, 7, CURDATE(), '12:00:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(126, 4, 3, CURDATE(), '13:00:00', 'pending', NULL, 'Follow-up visit for blood pressure control.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(127, 15, 12, CURDATE(), '10:30:00', 'accepted', NULL, 'Itchy red rash on both arms.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(128, 3, 6, CURDATE(), '12:00:00', 'pending', NULL, 'Frequent headaches and dizziness.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(129, 22, 1, CURDATE(), '12:00:00', 'pending', NULL, 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(130, 1, 12, CURDATE(), '09:00:00', 'pending', NULL, 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(131, 16, 2, CURDATE(), '12:30:00', 'accepted', NULL, 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -3 DAY), NOW()),
(132, 7, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:30:00', 'pending', NULL, 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY)),
(133, 21, 9, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY)),
(134, 20, 6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL 2 DAY)),
(135, 6, 4, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '12:30:00', 'pending', NULL, 'Stomach pain after meals.', NULL, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY)),
(136, 15, 10, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', 'pending', NULL, 'Stomach pain after meals.', NULL, NULL, NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY)),
(137, 23, 2, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', 'accepted', NULL, 'Itchy red rash on both arms.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 4 DAY)),
(138, 17, 1, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '10:30:00', 'pending', NULL, 'Follow-up visit for blood pressure control.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(NOW(), INTERVAL 5 DAY)),
(139, 10, 8, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '11:00:00', 'pending', NULL, 'Itchy red rash on both arms.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 6 DAY)),
(140, 16, 7, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'pending', NULL, 'Severe toothache on the lower right side.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY)),
(141, 11, 3, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '10:30:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(NOW(), INTERVAL 7 DAY)),
(142, 21, 1, DATE_ADD(CURDATE(), INTERVAL 8 DAY), '09:00:00', 'pending', NULL, 'Knee pain after playing football.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY)),
(143, 14, 7, DATE_ADD(CURDATE(), INTERVAL 8 DAY), '10:00:00', 'pending', NULL, 'Blurred vision when reading.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 8 DAY)),
(144, 2, 11, DATE_ADD(CURDATE(), INTERVAL 9 DAY), '13:00:00', 'pending', NULL, 'Itchy red rash on both arms.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY)),
(145, 8, 1, DATE_ADD(CURDATE(), INTERVAL 9 DAY), '11:00:00', 'pending', NULL, 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY)),
(146, 21, 12, DATE_ADD(CURDATE(), INTERVAL 9 DAY), '11:00:00', 'accepted', NULL, 'Frequent headaches and dizziness.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 6 DAY), DATE_ADD(NOW(), INTERVAL 9 DAY)),
(147, 21, 4, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '09:30:00', 'pending', NULL, 'Sore throat and difficulty swallowing.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY)),
(148, 18, 8, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '09:00:00', 'pending', NULL, 'Sore throat and difficulty swallowing.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 7 DAY), DATE_ADD(NOW(), INTERVAL 10 DAY)),
(149, 1, 5, DATE_ADD(CURDATE(), INTERVAL 11 DAY), '12:00:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 11 DAY)),
(150, 13, 10, DATE_ADD(CURDATE(), INTERVAL 11 DAY), '11:30:00', 'pending', NULL, 'Blurred vision when reading.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 8 DAY), DATE_ADD(NOW(), INTERVAL 11 DAY)),
(151, 1, 9, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '09:30:00', 'accepted', NULL, 'Severe toothache on the lower right side.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 9 DAY), DATE_ADD(NOW(), INTERVAL 12 DAY)),
(152, 9, 3, DATE_ADD(CURDATE(), INTERVAL 12 DAY), '11:30:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 9 DAY), DATE_ADD(NOW(), INTERVAL 12 DAY)),
(153, 15, 7, DATE_ADD(CURDATE(), INTERVAL 13 DAY), '11:30:00', 'pending', NULL, 'Severe toothache on the lower right side.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 10 DAY), DATE_ADD(NOW(), INTERVAL 13 DAY)),
(154, 6, 1, DATE_ADD(CURDATE(), INTERVAL 14 DAY), '09:00:00', 'pending', NULL, 'Frequent headaches and dizziness.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 11 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY)),
(155, 15, 11, DATE_ADD(CURDATE(), INTERVAL 14 DAY), '11:30:00', 'pending', NULL, 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 11 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY)),
(156, 13, 3, DATE_ADD(CURDATE(), INTERVAL 14 DAY), '11:00:00', 'accepted', NULL, 'Lower back pain when standing for a long time.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 11 DAY), DATE_ADD(NOW(), INTERVAL 14 DAY)),
(157, 17, 8, DATE_ADD(CURDATE(), INTERVAL 15 DAY), '11:30:00', 'accepted', NULL, 'Stomach pain after meals.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 12 DAY), DATE_ADD(NOW(), INTERVAL 15 DAY)),
(158, 8, 3, DATE_ADD(CURDATE(), INTERVAL 16 DAY), '12:30:00', 'accepted', NULL, 'Follow-up visit for blood pressure control.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 13 DAY), DATE_ADD(NOW(), INTERVAL 16 DAY)),
(159, 18, 1, DATE_ADD(CURDATE(), INTERVAL 16 DAY), '11:00:00', 'pending', NULL, 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 13 DAY), DATE_ADD(NOW(), INTERVAL 16 DAY)),
(160, 14, 5, DATE_ADD(CURDATE(), INTERVAL 16 DAY), '13:00:00', 'accepted', NULL, 'High fever and a persistent cough.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 13 DAY), DATE_ADD(NOW(), INTERVAL 16 DAY)),
(161, 5, 3, DATE_ADD(CURDATE(), INTERVAL 17 DAY), '12:00:00', 'pending', NULL, 'Chest pain and shortness of breath for three days.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 17 DAY)),
(162, 4, 5, DATE_ADD(CURDATE(), INTERVAL 18 DAY), '10:30:00', 'accepted', NULL, 'Blurred vision when reading.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY)),
(163, 23, 10, DATE_ADD(CURDATE(), INTERVAL 18 DAY), '12:00:00', 'pending', NULL, 'Frequent headaches and dizziness.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY)),
(164, 24, 11, DATE_ADD(CURDATE(), INTERVAL 18 DAY), '10:30:00', 'accepted', NULL, 'Blurred vision when reading.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 18 DAY)),
(165, 20, 8, DATE_ADD(CURDATE(), INTERVAL 19 DAY), '12:00:00', 'pending', NULL, 'Severe toothache on the lower right side.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 16 DAY), DATE_ADD(NOW(), INTERVAL 19 DAY)),
(166, 6, 4, DATE_ADD(CURDATE(), INTERVAL 19 DAY), '10:00:00', 'accepted', NULL, 'Itchy red rash on both arms.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 16 DAY), DATE_ADD(NOW(), INTERVAL 19 DAY)),
(167, 19, 4, DATE_ADD(CURDATE(), INTERVAL 20 DAY), '11:00:00', 'pending', NULL, 'Skin irritation after using a new cream.', NULL, NULL, DATE_ADD(NOW(), INTERVAL 17 DAY), DATE_ADD(NOW(), INTERVAL 20 DAY));


INSERT INTO `prescriptions` (`id`, `appointment_id`, `doctor_id`, `patient_id`, `instructions`, `created_at`, `updated_at`) VALUES
(1, 2, 5, 12, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(2, 3, 6, 2, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(3, 4, 2, 11, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(4, 6, 11, 2, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(5, 7, 7, 11, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(6, 9, 9, 3, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -57 DAY), DATE_ADD(NOW(), INTERVAL -57 DAY)),
(7, 10, 10, 8, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -56 DAY)),
(8, 11, 12, 25, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(9, 12, 2, 6, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(10, 13, 3, 3, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(11, 15, 2, 11, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -54 DAY), DATE_ADD(NOW(), INTERVAL -54 DAY)),
(12, 16, 6, 8, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(13, 18, 7, 9, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(14, 19, 5, 21, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -52 DAY), DATE_ADD(NOW(), INTERVAL -52 DAY)),
(15, 22, 11, 3, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -51 DAY), DATE_ADD(NOW(), INTERVAL -51 DAY)),
(16, 23, 8, 24, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -50 DAY), DATE_ADD(NOW(), INTERVAL -50 DAY)),
(17, 25, 5, 22, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -49 DAY), DATE_ADD(NOW(), INTERVAL -49 DAY)),
(18, 26, 1, 16, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(19, 27, 4, 15, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(20, 28, 5, 18, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(21, 29, 2, 1, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(22, 30, 9, 12, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(23, 31, 10, 4, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(24, 32, 11, 16, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(25, 34, 1, 9, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(26, 35, 2, 4, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(27, 36, 5, 11, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(28, 37, 12, 19, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(29, 38, 2, 24, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(30, 39, 4, 2, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -43 DAY), DATE_ADD(NOW(), INTERVAL -43 DAY)),
(31, 45, 11, 24, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(32, 46, 11, 22, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(33, 50, 6, 16, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -38 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(34, 51, 6, 7, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -38 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(35, 52, 2, 17, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(36, 53, 8, 11, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(37, 54, 1, 7, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(38, 56, 6, 11, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -35 DAY), DATE_ADD(NOW(), INTERVAL -35 DAY)),
(39, 57, 11, 14, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -34 DAY), DATE_ADD(NOW(), INTERVAL -34 DAY)),
(40, 58, 12, 15, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(41, 60, 10, 25, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(42, 61, 9, 3, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -32 DAY), DATE_ADD(NOW(), INTERVAL -32 DAY)),
(43, 62, 8, 14, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(44, 63, 11, 13, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(45, 64, 9, 2, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -30 DAY)),
(46, 65, 4, 23, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(47, 66, 6, 25, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(48, 67, 12, 22, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -28 DAY), DATE_ADD(NOW(), INTERVAL -28 DAY)),
(49, 73, 9, 1, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -25 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(50, 74, 8, 22, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(51, 75, 3, 15, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(52, 76, 12, 22, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(53, 77, 10, 25, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -23 DAY), DATE_ADD(NOW(), INTERVAL -23 DAY)),
(54, 79, 9, 10, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -22 DAY), DATE_ADD(NOW(), INTERVAL -22 DAY)),
(55, 80, 10, 17, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(56, 81, 12, 11, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(57, 82, 7, 23, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(58, 83, 10, 23, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -20 DAY)),
(59, 85, 3, 22, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -19 DAY)),
(60, 88, 2, 9, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(61, 89, 12, 1, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(62, 90, 9, 9, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(63, 91, 2, 18, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(64, 92, 7, 1, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(65, 93, 11, 3, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(66, 94, 10, 7, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(67, 95, 12, 4, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(68, 96, 3, 14, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -14 DAY), DATE_ADD(NOW(), INTERVAL -14 DAY)),
(69, 97, 8, 23, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(70, 99, 8, 21, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(71, 100, 2, 23, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(72, 101, 12, 24, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(73, 102, 5, 13, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -11 DAY), DATE_ADD(NOW(), INTERVAL -11 DAY)),
(74, 103, 3, 11, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -10 DAY)),
(75, 104, 2, 23, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -9 DAY), DATE_ADD(NOW(), INTERVAL -9 DAY)),
(76, 105, 5, 7, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(77, 106, 9, 10, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(78, 107, 4, 21, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(79, 108, 2, 7, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(80, 109, 2, 19, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(81, 111, 4, 17, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(82, 112, 7, 17, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(83, 113, 1, 9, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(84, 114, 1, 15, 'Avoid cold drinks and spicy food until the pain is gone.', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(85, 115, 12, 24, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -3 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(86, 116, 4, 23, 'Do the blood test before the next visit.', DATE_ADD(NOW(), INTERVAL -3 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(87, 118, 12, 2, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(88, 119, 1, 17, 'Apply the cream twice a day on the affected area only.', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(89, 120, 5, 9, 'Reduce salt in your food and walk 30 minutes every day.', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(90, 121, 3, 7, 'Drink plenty of water and rest for 5 days.', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(91, 123, 5, 6, 'Take the medicine after meals. Come back in two weeks.', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY));


INSERT INTO `medicine_prescription` (`id`, `prescription_id`, `medicine_id`, `dosage`, `frequency`, `duration`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '10ml', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(2, 1, 15, '500mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(3, 1, 21, '500mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(4, 2, 22, '75mg', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(5, 3, 19, '500mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(6, 3, 16, '10ml', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -59 DAY), DATE_ADD(NOW(), INTERVAL -59 DAY)),
(7, 4, 17, '500mg', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(8, 4, 15, '50mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(9, 4, 10, '250mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(10, 5, 14, '10ml', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(11, 5, 6, '50mg', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(12, 5, 17, '10ml', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -58 DAY), DATE_ADD(NOW(), INTERVAL -58 DAY)),
(13, 6, 24, '250mg', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -57 DAY), DATE_ADD(NOW(), INTERVAL -57 DAY)),
(14, 6, 23, '1g', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -57 DAY), DATE_ADD(NOW(), INTERVAL -57 DAY)),
(15, 6, 10, '50mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -57 DAY), DATE_ADD(NOW(), INTERVAL -57 DAY)),
(16, 7, 21, '75mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -56 DAY)),
(17, 7, 4, '250mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -56 DAY)),
(18, 7, 8, '500mg', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -56 DAY), DATE_ADD(NOW(), INTERVAL -56 DAY)),
(19, 8, 23, '250mg', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(20, 9, 7, '1g', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(21, 10, 16, '250mg', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(22, 10, 4, '1g', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -55 DAY), DATE_ADD(NOW(), INTERVAL -55 DAY)),
(23, 11, 1, '250mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -54 DAY), DATE_ADD(NOW(), INTERVAL -54 DAY)),
(24, 11, 4, '1g', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -54 DAY), DATE_ADD(NOW(), INTERVAL -54 DAY)),
(25, 11, 2, '75mg', '2x daily', '3 days', DATE_ADD(NOW(), INTERVAL -54 DAY), DATE_ADD(NOW(), INTERVAL -54 DAY)),
(26, 12, 11, '1g', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(27, 13, 17, '1g', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(28, 13, 24, '10ml', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -53 DAY), DATE_ADD(NOW(), INTERVAL -53 DAY)),
(29, 14, 3, '10ml', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -52 DAY), DATE_ADD(NOW(), INTERVAL -52 DAY)),
(30, 14, 14, '250mg', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -52 DAY), DATE_ADD(NOW(), INTERVAL -52 DAY)),
(31, 15, 10, '50mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -51 DAY), DATE_ADD(NOW(), INTERVAL -51 DAY)),
(32, 16, 24, '1g', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -50 DAY), DATE_ADD(NOW(), INTERVAL -50 DAY)),
(33, 16, 9, '10ml', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -50 DAY), DATE_ADD(NOW(), INTERVAL -50 DAY)),
(34, 17, 17, '1g', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -49 DAY), DATE_ADD(NOW(), INTERVAL -49 DAY)),
(35, 17, 24, '1g', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -49 DAY), DATE_ADD(NOW(), INTERVAL -49 DAY)),
(36, 18, 11, '10ml', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(37, 18, 14, '1g', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(38, 18, 16, '250mg', '2x daily', '10 days', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(39, 19, 14, '50mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(40, 19, 15, '250mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(41, 19, 20, '250mg', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -48 DAY), DATE_ADD(NOW(), INTERVAL -48 DAY)),
(42, 20, 9, '10ml', '2x daily', '3 days', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(43, 20, 18, '1g', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(44, 20, 6, '500mg', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(45, 21, 20, '75mg', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(46, 21, 3, '500mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(47, 22, 23, '1g', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -47 DAY), DATE_ADD(NOW(), INTERVAL -47 DAY)),
(48, 23, 11, '500mg', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(49, 23, 24, '250mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(50, 23, 5, '1g', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(51, 24, 5, '50mg', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(52, 24, 11, '500mg', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -46 DAY), DATE_ADD(NOW(), INTERVAL -46 DAY)),
(53, 25, 17, '10ml', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(54, 26, 17, '250mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(55, 26, 24, '1g', '2x daily', '3 days', DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(56, 26, 8, '1g', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -45 DAY), DATE_ADD(NOW(), INTERVAL -45 DAY)),
(57, 27, 17, '75mg', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(58, 27, 9, '500mg', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(59, 27, 23, '250mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(60, 28, 5, '75mg', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(61, 28, 6, '1g', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(62, 28, 7, '1g', '2x daily', '10 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(63, 29, 16, '75mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(64, 29, 3, '50mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(65, 29, 2, '50mg', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -44 DAY), DATE_ADD(NOW(), INTERVAL -44 DAY)),
(66, 30, 21, '500mg', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -43 DAY), DATE_ADD(NOW(), INTERVAL -43 DAY)),
(67, 30, 10, '75mg', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -43 DAY), DATE_ADD(NOW(), INTERVAL -43 DAY)),
(68, 30, 22, '75mg', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -43 DAY), DATE_ADD(NOW(), INTERVAL -43 DAY)),
(69, 31, 17, '1g', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(70, 31, 2, '250mg', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(71, 32, 19, '1g', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(72, 32, 5, '250mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -40 DAY), DATE_ADD(NOW(), INTERVAL -40 DAY)),
(73, 33, 18, '50mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -38 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(74, 34, 4, '10ml', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -38 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(75, 34, 10, '1g', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -38 DAY), DATE_ADD(NOW(), INTERVAL -38 DAY)),
(76, 35, 22, '250mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(77, 35, 23, '75mg', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(78, 35, 2, '250mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(79, 36, 4, '10ml', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(80, 37, 18, '1g', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(81, 37, 5, '1g', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -37 DAY), DATE_ADD(NOW(), INTERVAL -37 DAY)),
(82, 38, 9, '10ml', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -35 DAY), DATE_ADD(NOW(), INTERVAL -35 DAY)),
(83, 38, 8, '10ml', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -35 DAY), DATE_ADD(NOW(), INTERVAL -35 DAY)),
(84, 38, 10, '1g', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -35 DAY), DATE_ADD(NOW(), INTERVAL -35 DAY)),
(85, 39, 5, '500mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -34 DAY), DATE_ADD(NOW(), INTERVAL -34 DAY)),
(86, 39, 14, '75mg', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -34 DAY), DATE_ADD(NOW(), INTERVAL -34 DAY)),
(87, 40, 12, '250mg', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(88, 40, 11, '75mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(89, 40, 13, '1g', '2x daily', '10 days', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(90, 41, 3, '1g', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(91, 41, 6, '500mg', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(92, 41, 18, '10ml', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -33 DAY), DATE_ADD(NOW(), INTERVAL -33 DAY)),
(93, 42, 22, '10ml', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -32 DAY), DATE_ADD(NOW(), INTERVAL -32 DAY)),
(94, 42, 13, '250mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -32 DAY), DATE_ADD(NOW(), INTERVAL -32 DAY)),
(95, 42, 6, '50mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -32 DAY), DATE_ADD(NOW(), INTERVAL -32 DAY)),
(96, 43, 18, '500mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(97, 43, 3, '500mg', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(98, 43, 7, '1g', '2x daily', '3 days', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(99, 44, 8, '10ml', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(100, 44, 18, '1g', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -31 DAY), DATE_ADD(NOW(), INTERVAL -31 DAY)),
(101, 45, 17, '1g', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -30 DAY)),
(102, 45, 23, '75mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -30 DAY), DATE_ADD(NOW(), INTERVAL -30 DAY)),
(103, 46, 24, '75mg', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(104, 46, 13, '75mg', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(105, 46, 7, '75mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(106, 47, 17, '75mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -29 DAY), DATE_ADD(NOW(), INTERVAL -29 DAY)),
(107, 48, 23, '500mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -28 DAY), DATE_ADD(NOW(), INTERVAL -28 DAY)),
(108, 49, 18, '250mg', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -25 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(109, 49, 6, '10ml', '2x daily', '10 days', DATE_ADD(NOW(), INTERVAL -25 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(110, 49, 10, '1g', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -25 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(111, 50, 20, '250mg', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(112, 51, 17, '75mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(113, 51, 6, '500mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(114, 52, 12, '75mg', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -24 DAY), DATE_ADD(NOW(), INTERVAL -24 DAY)),
(115, 53, 8, '250mg', '2x daily', '3 days', DATE_ADD(NOW(), INTERVAL -23 DAY), DATE_ADD(NOW(), INTERVAL -23 DAY)),
(116, 53, 5, '500mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -23 DAY), DATE_ADD(NOW(), INTERVAL -23 DAY)),
(117, 54, 12, '10ml', '2x daily', '10 days', DATE_ADD(NOW(), INTERVAL -22 DAY), DATE_ADD(NOW(), INTERVAL -22 DAY)),
(118, 55, 7, '500mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(119, 55, 4, '250mg', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(120, 55, 11, '50mg', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(121, 56, 9, '10ml', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(122, 56, 4, '1g', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(123, 57, 1, '75mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -21 DAY), DATE_ADD(NOW(), INTERVAL -21 DAY)),
(124, 58, 3, '500mg', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -20 DAY)),
(125, 58, 23, '250mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -20 DAY)),
(126, 59, 3, '250mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -19 DAY)),
(127, 59, 21, '50mg', '3x daily', '5 days', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -19 DAY)),
(128, 59, 24, '1g', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -19 DAY), DATE_ADD(NOW(), INTERVAL -19 DAY)),
(129, 60, 4, '250mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(130, 60, 3, '50mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(131, 61, 16, '10ml', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(132, 61, 10, '500mg', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(133, 61, 15, '250mg', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(134, 62, 24, '250mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -17 DAY), DATE_ADD(NOW(), INTERVAL -17 DAY)),
(135, 63, 5, '500mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(136, 63, 12, '75mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(137, 63, 23, '500mg', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(138, 64, 14, '10ml', 'When needed', '3 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(139, 64, 23, '75mg', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(140, 64, 11, '1g', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(141, 65, 13, '1g', '2x daily', '3 days', DATE_ADD(NOW(), INTERVAL -16 DAY), DATE_ADD(NOW(), INTERVAL -16 DAY)),
(142, 66, 24, '1g', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(143, 66, 10, '10ml', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(144, 66, 7, '10ml', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(145, 67, 14, '250mg', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -15 DAY), DATE_ADD(NOW(), INTERVAL -15 DAY)),
(146, 68, 13, '250mg', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -14 DAY), DATE_ADD(NOW(), INTERVAL -14 DAY)),
(147, 68, 18, '75mg', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -14 DAY), DATE_ADD(NOW(), INTERVAL -14 DAY)),
(148, 68, 9, '250mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -14 DAY), DATE_ADD(NOW(), INTERVAL -14 DAY)),
(149, 69, 1, '10ml', '1x daily', '7 days', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(150, 69, 16, '50mg', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(151, 69, 4, '50mg', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(152, 70, 14, '250mg', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(153, 70, 24, '500mg', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(154, 70, 9, '500mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -13 DAY), DATE_ADD(NOW(), INTERVAL -13 DAY)),
(155, 71, 11, '75mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(156, 72, 15, '500mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(157, 72, 4, '500mg', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(158, 73, 23, '250mg', '2x daily', '10 days', DATE_ADD(NOW(), INTERVAL -11 DAY), DATE_ADD(NOW(), INTERVAL -11 DAY)),
(159, 73, 19, '1g', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -11 DAY), DATE_ADD(NOW(), INTERVAL -11 DAY)),
(160, 74, 6, '1g', '3x daily', '7 days', DATE_ADD(NOW(), INTERVAL -10 DAY), DATE_ADD(NOW(), INTERVAL -10 DAY)),
(161, 75, 17, '250mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -9 DAY), DATE_ADD(NOW(), INTERVAL -9 DAY)),
(162, 75, 5, '1g', '1x daily', '10 days', DATE_ADD(NOW(), INTERVAL -9 DAY), DATE_ADD(NOW(), INTERVAL -9 DAY)),
(163, 76, 13, '75mg', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(164, 76, 23, '250mg', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(165, 76, 5, '50mg', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(166, 77, 10, '500mg', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(167, 78, 20, '10ml', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(168, 79, 24, '500mg', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(169, 79, 7, '1g', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(170, 80, 1, '10ml', '1x daily', '3 days', DATE_ADD(NOW(), INTERVAL -7 DAY), DATE_ADD(NOW(), INTERVAL -7 DAY)),
(171, 81, 10, '75mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(172, 82, 1, '75mg', 'When needed', '7 days', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(173, 82, 9, '75mg', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(174, 82, 2, '500mg', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(175, 83, 12, '75mg', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(176, 83, 14, '1g', '2x daily', '7 days', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(177, 84, 1, '500mg', 'When needed', '1 month', DATE_ADD(NOW(), INTERVAL -4 DAY), DATE_ADD(NOW(), INTERVAL -4 DAY)),
(178, 85, 7, '75mg', '3x daily', '10 days', DATE_ADD(NOW(), INTERVAL -3 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(179, 85, 9, '10ml', '1x daily', '5 days', DATE_ADD(NOW(), INTERVAL -3 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(180, 86, 2, '500mg', '2x daily', '1 month', DATE_ADD(NOW(), INTERVAL -3 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY)),
(181, 87, 14, '10ml', 'When needed', '5 days', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(182, 88, 23, '75mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(183, 89, 9, '10ml', '3x daily', '1 month', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(184, 89, 3, '75mg', '3x daily', '3 days', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(185, 90, 20, '250mg', '1x daily', '1 month', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(186, 91, 16, '1g', '2x daily', '5 days', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(187, 91, 4, '10ml', 'When needed', '10 days', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY));


INSERT INTO `analyses` (`id`, `patient_id`, `appointment_id`, `title`, `file_name`, `file_path`, `file_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 29, 'Complete Blood Count', 'blood-test-sample.pdf', 'analyses/blood-test-sample.pdf', 'blood_test', 'Blood test done at the central lab, as requested by the doctor.', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(2, 1, NULL, 'Chest X-Ray', 'chest-xray-sample.png', 'analyses/chest-xray-sample.png', 'x_ray', 'Chest x-ray image taken last month.', DATE_ADD(NOW(), INTERVAL -25 DAY), DATE_ADD(NOW(), INTERVAL -25 DAY)),
(3, 2, NULL, 'Vitamin D Level', 'blood-test-sample.pdf', 'analyses/blood-test-sample.pdf', 'blood_test', 'Routine vitamin D check.', DATE_ADD(NOW(), INTERVAL -8 DAY), DATE_ADD(NOW(), INTERVAL -8 DAY)),
(4, 3, NULL, 'Knee MRI', 'chest-xray-sample.png', 'analyses/chest-xray-sample.png', 'mri', 'MRI of the right knee after a football injury.', DATE_ADD(NOW(), INTERVAL -5 DAY), DATE_ADD(NOW(), INTERVAL -5 DAY)),
(5, 4, NULL, 'Urine Analysis', 'blood-test-sample.pdf', 'analyses/blood-test-sample.pdf', 'urine_test', 'Urine analysis requested during the last visit.', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY));


INSERT INTO `medical_files` (`id`, `patient_id`, `uploaded_by`, `title`, `file_path`, `file_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'ECG Report', 'medical-files/ecg-report-sample.pdf', 'lab_result', 'ECG performed during the cardiology visit.', DATE_ADD(NOW(), INTERVAL -12 DAY), DATE_ADD(NOW(), INTERVAL -12 DAY)),
(2, 2, 5, 'Dental Panorama', 'medical-files/xray-sample.png', 'x_ray', 'Full mouth panoramic x-ray.', DATE_ADD(NOW(), INTERVAL -20 DAY), DATE_ADD(NOW(), INTERVAL -20 DAY)),
(3, 3, 6, 'Knee X-Ray', 'medical-files/xray-sample.png', 'x_ray', 'X-ray of the right knee.', DATE_ADD(NOW(), INTERVAL -6 DAY), DATE_ADD(NOW(), INTERVAL -6 DAY)),
(4, 5, 4, 'Lipid Profile', 'medical-files/ecg-report-sample.pdf', 'lab_result', 'Cholesterol panel results.', DATE_ADD(NOW(), INTERVAL -3 DAY), DATE_ADD(NOW(), INTERVAL -3 DAY));


INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`, `updated_at`) VALUES
(1, 1, 'auth.login', 'Admin User logged in.', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(2, 4, 'appointment.accepted', 'Accepted appointment #1', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(3, 4, 'diagnosis.saved', 'Diagnosis saved for appointment #1', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -2 DAY), DATE_ADD(NOW(), INTERVAL -2 DAY)),
(4, 16, 'appointment.booked', 'Patient booked an appointment', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY)),
(5, 2, 'patient.registered', 'Reception registered a new patient', '127.0.0.1', DATE_ADD(NOW(), INTERVAL -1 DAY), DATE_ADD(NOW(), INTERVAL -1 DAY));


SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
--  DONE.
--
--  Test accounts (the password for every account is: password123)
--    Admin      admin@clinic.com
--    Doctor     mohamed@clinic.com
--    Reception  reception@clinic.com
--    Patient    ahmed@clinic.com
-- ============================================================================

-- FILE: /database.sql
-- AI Homework Helper - Database Schema
-- MySQL 5.7+ / MariaDB compatible

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Create database
CREATE DATABASE IF NOT EXISTS `ai_homework_helper` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ai_homework_helper`;

-- ============================================================================
-- CORE TABLES
-- ============================================================================

-- Tenants table (education accounts: schools, tutoring centers, families)
CREATE TABLE `tenants` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('school', 'tutoring_center', 'family') NOT NULL DEFAULT 'school',
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NULL,
  `address` TEXT NULL,
  `logo` VARCHAR(255) NULL,
  `timezone` VARCHAR(50) NOT NULL DEFAULT 'UTC',
  `status` ENUM('active', 'suspended', 'cancelled') NOT NULL DEFAULT 'active',
  `api_key` VARCHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users table
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('platform_admin', 'tenant_admin', 'teacher', 'student') NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `avatar` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `tenant_id` (`tenant_id`),
  KEY `role` (`role`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SUBSCRIPTION & BILLING TABLES
-- ============================================================================

-- Subscription plans
CREATE TABLE `plans` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `price_monthly` DECIMAL(10,2) NOT NULL,
  `max_students` INT(11) NOT NULL DEFAULT 0,
  `max_classes` INT(11) NOT NULL DEFAULT 0,
  `max_ai_requests_per_month` INT(11) NOT NULL DEFAULT 0,
  `max_storage_mb` INT(11) NOT NULL DEFAULT 0,
  `features` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tenant subscriptions
CREATE TABLE `tenant_subscriptions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `plan_id` INT(11) UNSIGNED NOT NULL,
  `status` ENUM('active', 'cancelled', 'expired') NOT NULL DEFAULT 'active',
  `started_at` DATETIME NOT NULL,
  `expires_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `plan_id` (`plan_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invoices
CREATE TABLE `invoices` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `tax` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `paid_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments
CREATE TABLE `payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `invoice_id` INT(11) UNSIGNED NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_id` VARCHAR(100) NULL,
  `status` ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- EDUCATION MANAGEMENT TABLES
-- ============================================================================

-- Classes/Groups
CREATE TABLE `classes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `grade_level` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'archived') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects
CREATE TABLE `subjects` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NULL,
  `description` TEXT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class-Subject relationship (many-to-many)
CREATE TABLE `class_subject` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `class_id` INT(11) UNSIGNED NOT NULL,
  `subject_id` INT(11) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_subject_unique` (`class_id`, `subject_id`),
  KEY `tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Teacher-Class relationship (many-to-many)
CREATE TABLE `teacher_class` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `teacher_id` INT(11) UNSIGNED NOT NULL,
  `class_id` INT(11) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_class_unique` (`teacher_id`, `class_id`),
  KEY `tenant_id` (`tenant_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Students
CREATE TABLE `students` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NULL,
  `class_id` INT(11) UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `grade_level` VARCHAR(50) NULL,
  `parent_name` VARCHAR(255) NULL,
  `parent_email` VARCHAR(255) NULL,
  `parent_phone` VARCHAR(50) NULL,
  `avatar` VARCHAR(255) NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `class_id` (`class_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- HOMEWORK & AI TABLES
-- ============================================================================

-- Homework requests
CREATE TABLE `homework` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `student_id` INT(11) UNSIGNED NOT NULL,
  `subject_id` INT(11) UNSIGNED NOT NULL,
  `class_id` INT(11) UNSIGNED NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `image` VARCHAR(255) NULL,
  `deadline` DATETIME NULL,
  `status` ENUM('new', 'in_progress', 'answered', 'reviewed', 'closed') NOT NULL DEFAULT 'new',
  `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
  `created_by` INT(11) UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homework responses (AI answers and teacher comments)
CREATE TABLE `homework_responses` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `homework_id` INT(11) UNSIGNED NOT NULL,
  `response_type` ENUM('ai', 'teacher', 'student') NOT NULL,
  `user_id` INT(11) UNSIGNED NULL,
  `content` TEXT NOT NULL,
  `step_by_step` TEXT NULL,
  `examples` TEXT NULL,
  `related_topics` TEXT NULL,
  `is_helpful` TINYINT(1) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `homework_id` (`homework_id`),
  KEY `response_type` (`response_type`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`homework_id`) REFERENCES `homework` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI sessions (tracking all AI interactions)
CREATE TABLE `ai_sessions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NULL,
  `student_id` INT(11) UNSIGNED NULL,
  `subject_id` INT(11) UNSIGNED NULL,
  `session_type` ENUM('homework', 'quiz', 'explanation', 'practice') NOT NULL,
  `prompt` TEXT NOT NULL,
  `response` TEXT NOT NULL,
  `tokens_used` INT(11) NOT NULL DEFAULT 0,
  `processing_time_ms` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  KEY `session_type` (`session_type`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- QUIZ & ASSESSMENT TABLES
-- ============================================================================

-- Quizzes
CREATE TABLE `quizzes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `subject_id` INT(11) UNSIGNED NOT NULL,
  `student_id` INT(11) UNSIGNED NULL,
  `class_id` INT(11) UNSIGNED NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `difficulty` ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
  `total_questions` INT(11) NOT NULL DEFAULT 0,
  `passing_score` INT(11) NOT NULL DEFAULT 70,
  `time_limit_minutes` INT(11) NULL,
  `created_by` INT(11) UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `subject_id` (`subject_id`),
  KEY `student_id` (`student_id`),
  KEY `class_id` (`class_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz questions
CREATE TABLE `quiz_questions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `quiz_id` INT(11) UNSIGNED NOT NULL,
  `question_type` ENUM('mcq', 'true_false', 'short_answer') NOT NULL,
  `question_text` TEXT NOT NULL,
  `options` TEXT NULL,
  `correct_answer` TEXT NOT NULL,
  `explanation` TEXT NULL,
  `points` INT(11) NOT NULL DEFAULT 1,
  `order_num` INT(11) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `quiz_id` (`quiz_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Quiz results
CREATE TABLE `quiz_results` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `quiz_id` INT(11) UNSIGNED NOT NULL,
  `student_id` INT(11) UNSIGNED NOT NULL,
  `score` DECIMAL(5,2) NOT NULL,
  `total_points` INT(11) NOT NULL,
  `earned_points` INT(11) NOT NULL,
  `answers` TEXT NOT NULL,
  `started_at` DATETIME NOT NULL,
  `completed_at` DATETIME NOT NULL,
  `time_taken_seconds` INT(11) NOT NULL,
  `passed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `quiz_id` (`quiz_id`),
  KEY `student_id` (`student_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATION TABLE
-- ============================================================================

-- Notifications
CREATE TABLE `notifications` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` INT(11) UNSIGNED NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `link` VARCHAR(255) NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SEED DATA
-- ============================================================================

-- Insert subscription plans
INSERT INTO `plans` (`id`, `name`, `description`, `price_monthly`, `max_students`, `max_classes`, `max_ai_requests_per_month`, `max_storage_mb`, `features`) VALUES
(1, 'Free', 'Perfect for families and small tutoring', 0.00, 5, 2, 100, 100, 'Up to 5 students, 2 classes, 100 AI requests/month'),
(2, 'School Basic', 'For small schools and tutoring centers', 49.00, 50, 10, 1000, 1000, 'Up to 50 students, 10 classes, 1000 AI requests/month'),
(3, 'School Pro', 'For medium to large schools', 99.00, 200, 50, 5000, 5000, 'Up to 200 students, 50 classes, 5000 AI requests/month'),
(4, 'Enterprise', 'Unlimited access for large institutions', 299.00, 10000, 1000, 50000, 50000, 'Unlimited students, classes, and AI requests');

-- Insert demo tenants
INSERT INTO `tenants` (`id`, `name`, `type`, `email`, `phone`, `address`, `timezone`, `status`, `api_key`) VALUES
(1, 'Greenwood High School', 'school', 'admin@greenwood-high.edu', '555-0100', '123 Education St, Springfield', 'America/New_York', 'active', SHA2('greenwood_api_key_2024', 256)),
(2, 'Johnson Family Account', 'family', 'sarah.johnson@email.com', '555-0200', '456 Oak Ave, Springfield', 'America/Chicago', 'active', SHA2('johnson_api_key_2024', 256));

-- Insert tenant subscriptions
INSERT INTO `tenant_subscriptions` (`tenant_id`, `plan_id`, `status`, `started_at`, `expires_at`) VALUES
(1, 3, 'active', '2025-01-01 00:00:00', '2025-12-31 23:59:59'),
(2, 1, 'active', '2025-01-15 00:00:00', NULL);

-- Insert demo users (password is 'password123' for all)
INSERT INTO `users` (`id`, `tenant_id`, `name`, `email`, `password`, `role`, `status`) VALUES
(1, NULL, 'Platform Admin', 'admin@aihomework.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'platform_admin', 'active'),
(2, 1, 'Principal Roberts', 'principal@greenwood-high.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active'),
(3, 1, 'Ms. Emily Davis', 'emily.davis@greenwood-high.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
(4, 1, 'Mr. James Wilson', 'james.wilson@greenwood-high.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'active'),
(5, 1, 'Alex Thompson', 'alex.thompson@student.greenwood.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(6, 1, 'Sarah Martinez', 'sarah.martinez@student.greenwood.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active'),
(7, 2, 'Sarah Johnson', 'sarah.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tenant_admin', 'active'),
(8, 2, 'Tommy Johnson', 'tommy.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active');

-- Insert demo classes
INSERT INTO `classes` (`id`, `tenant_id`, `name`, `grade_level`, `description`, `status`) VALUES
(1, 1, 'Grade 10A', '10', 'Advanced Mathematics and Science class', 'active'),
(2, 1, 'Grade 10B', '10', 'General studies class', 'active'),
(3, 1, 'Grade 9A', '9', 'Freshman class', 'active'),
(4, 2, 'Johnson Kids', 'Mixed', 'Home learning group', 'active');

-- Insert demo subjects
INSERT INTO `subjects` (`id`, `tenant_id`, `name`, `code`, `description`, `status`) VALUES
(1, 1, 'Mathematics', 'MATH', 'Algebra, Geometry, Calculus', 'active'),
(2, 1, 'Physics', 'PHYS', 'Classical and Modern Physics', 'active'),
(3, 1, 'Chemistry', 'CHEM', 'General Chemistry', 'active'),
(4, 1, 'Biology', 'BIO', 'Life Sciences', 'active'),
(5, 1, 'English', 'ENG', 'Literature and Composition', 'active'),
(6, 2, 'Math', 'MATH', 'General Mathematics', 'active'),
(7, 2, 'Science', 'SCI', 'General Science', 'active'),
(8, 2, 'Reading', 'READ', 'Reading Comprehension', 'active');

-- Insert class-subject relationships
INSERT INTO `class_subject` (`tenant_id`, `class_id`, `subject_id`) VALUES
(1, 1, 1), (1, 1, 2), (1, 1, 3),
(1, 2, 1), (1, 2, 4), (1, 2, 5),
(1, 3, 1), (1, 3, 5),
(2, 4, 6), (2, 4, 7), (2, 4, 8);

-- Insert teacher-class relationships
INSERT INTO `teacher_class` (`tenant_id`, `teacher_id`, `class_id`) VALUES
(1, 3, 1), (1, 3, 2),
(1, 4, 2), (1, 4, 3);

-- Insert demo students
INSERT INTO `students` (`id`, `tenant_id`, `user_id`, `class_id`, `name`, `email`, `grade_level`, `parent_name`, `parent_email`, `parent_phone`, `status`) VALUES
(1, 1, 5, 1, 'Alex Thompson', 'alex.thompson@student.greenwood.edu', '10', 'Michael Thompson', 'michael.thompson@email.com', '555-1001', 'active'),
(2, 1, 6, 1, 'Sarah Martinez', 'sarah.martinez@student.greenwood.edu', '10', 'Maria Martinez', 'maria.martinez@email.com', '555-1002', 'active'),
(3, 1, NULL, 2, 'David Lee', 'david.lee@student.greenwood.edu', '10', 'Jennifer Lee', 'jennifer.lee@email.com', '555-1003', 'active'),
(4, 1, NULL, 3, 'Emma Wilson', 'emma.wilson@student.greenwood.edu', '9', 'Robert Wilson', 'robert.wilson@email.com', '555-1004', 'active'),
(5, 2, 8, 4, 'Tommy Johnson', 'tommy.johnson@email.com', '7', 'Sarah Johnson', 'sarah.johnson@email.com', '555-0200', 'active'),
(6, 2, NULL, 4, 'Emma Johnson', NULL, '5', 'Sarah Johnson', 'sarah.johnson@email.com', '555-0200', 'active');

-- Insert demo homework requests
INSERT INTO `homework` (`id`, `tenant_id`, `student_id`, `subject_id`, `class_id`, `title`, `description`, `status`, `priority`, `created_by`, `created_at`) VALUES
(1, 1, 1, 1, 1, 'Quadratic Equations Help', 'I need help solving quadratic equations using the quadratic formula. Specifically x^2 + 5x + 6 = 0', 'answered', 'high', 5, '2025-01-20 10:00:00'),
(2, 1, 2, 2, 1, 'Newton Laws of Motion', 'Can you explain Newtons second law with examples?', 'answered', 'medium', 6, '2025-01-21 14:30:00'),
(3, 1, 1, 3, 1, 'Chemical Bonding', 'What is the difference between ionic and covalent bonds?', 'new', 'medium', 5, '2025-01-22 09:15:00'),
(4, 2, 5, 6, 4, 'Fraction Division', 'How do I divide fractions? Example: 3/4 ÷ 2/3', 'answered', 'high', 8, '2025-01-21 16:00:00'),
(5, 2, 6, 8, 4, 'Reading Comprehension', 'Need help understanding the main idea of a story', 'in_progress', 'low', 7, '2025-01-22 11:00:00');

-- Insert demo homework responses
INSERT INTO `homework_responses` (`tenant_id`, `homework_id`, `response_type`, `content`, `step_by_step`, `examples`, `is_helpful`) VALUES
(1, 1, 'ai', 'To solve the quadratic equation x^2 + 5x + 6 = 0, we use the quadratic formula: x = (-b ± √(b^2 - 4ac)) / 2a',
'Step 1: Identify a=1, b=5, c=6\nStep 2: Calculate discriminant: b^2 - 4ac = 25 - 24 = 1\nStep 3: Apply formula: x = (-5 ± 1) / 2\nStep 4: Solutions: x = -2 or x = -3',
'Example 1: x^2 + 7x + 12 = 0 → x = -3 or x = -4\nExample 2: x^2 - 5x + 6 = 0 → x = 2 or x = 3', 1),
(1, 2, 'ai', 'Newtons Second Law states that Force = mass × acceleration (F = ma). This means the force acting on an object is equal to its mass multiplied by its acceleration.',
'Step 1: Understand the relationship F = ma\nStep 2: Force is measured in Newtons (N)\nStep 3: Mass in kilograms (kg), acceleration in m/s^2\nStep 4: If you push harder (more force), the object accelerates more',
'Example: A 10kg box pushed with 50N force accelerates at 5 m/s^2\nExample: A car (1000kg) accelerating at 2 m/s^2 needs 2000N of force', 1),
(2, 4, 'ai', 'To divide fractions, you multiply by the reciprocal of the second fraction. For 3/4 ÷ 2/3: flip the second fraction and multiply.',
'Step 1: Write the problem: 3/4 ÷ 2/3\nStep 2: Flip the second fraction: 2/3 becomes 3/2\nStep 3: Change division to multiplication: 3/4 × 3/2\nStep 4: Multiply numerators: 3 × 3 = 9\nStep 5: Multiply denominators: 4 × 2 = 8\nStep 6: Answer: 9/8 or 1 1/8',
'Example: 1/2 ÷ 1/4 = 1/2 × 4/1 = 4/2 = 2\nExample: 5/6 ÷ 2/3 = 5/6 × 3/2 = 15/12 = 5/4', 1);

-- Insert demo AI sessions
INSERT INTO `ai_sessions` (`tenant_id`, `user_id`, `student_id`, `subject_id`, `session_type`, `prompt`, `response`, `tokens_used`, `processing_time_ms`, `created_at`) VALUES
(1, 5, 1, 1, 'homework', 'Solve x^2 + 5x + 6 = 0', 'Using quadratic formula...', 250, 1200, '2025-01-20 10:05:00'),
(1, 6, 2, 2, 'homework', 'Explain Newtons second law', 'F = ma explanation...', 320, 1500, '2025-01-21 14:35:00'),
(2, 8, 5, 6, 'homework', 'How to divide fractions', 'Multiply by reciprocal...', 180, 900, '2025-01-21 16:05:00'),
(1, 5, 1, 1, 'quiz', 'Generate math quiz on quadratic equations', 'Quiz with 10 questions...', 450, 2000, '2025-01-20 15:00:00'),
(2, 8, 5, 6, 'practice', 'Practice problems for fraction division', '10 practice problems...', 280, 1100, '2025-01-22 10:00:00');

-- Insert demo quizzes
INSERT INTO `quizzes` (`id`, `tenant_id`, `subject_id`, `student_id`, `class_id`, `title`, `description`, `difficulty`, `total_questions`, `passing_score`, `created_by`, `created_at`) VALUES
(1, 1, 1, NULL, 1, 'Quadratic Equations Quiz', 'Test your understanding of quadratic equations', 'medium', 5, 70, 3, '2025-01-20 15:00:00'),
(2, 1, 2, NULL, 1, 'Newtons Laws Quiz', 'Quiz on three laws of motion', 'easy', 5, 60, 3, '2025-01-21 16:00:00'),
(3, 2, 6, 5, 4, 'Fraction Practice', 'Practice quiz for fraction operations', 'easy', 5, 70, 7, '2025-01-22 10:00:00');

-- Insert demo quiz questions
INSERT INTO `quiz_questions` (`tenant_id`, `quiz_id`, `question_type`, `question_text`, `options`, `correct_answer`, `explanation`, `points`, `order_num`) VALUES
(1, 1, 'mcq', 'What is the quadratic formula?', '["x = -b/2a", "x = (-b ± √(b^2 - 4ac)) / 2a", "x = b^2 - 4ac", "x = a + b + c"]', 'x = (-b ± √(b^2 - 4ac)) / 2a', 'The quadratic formula is used to find the roots of ax^2 + bx + c = 0', 1, 1),
(1, 1, 'mcq', 'Solve: x^2 - 4 = 0', '["x = 2", "x = -2", "x = ±2", "x = 4"]', 'x = ±2', 'x^2 = 4, so x = 2 or x = -2', 1, 2),
(1, 2, 'true_false', 'Newtons first law states that an object at rest stays at rest unless acted upon by a force', '["True", "False"]', 'True', 'This is the law of inertia', 1, 1),
(2, 3, 'mcq', 'What is 1/2 + 1/4?', '["1/6", "2/6", "3/4", "1/8"]', '3/4', 'Find common denominator: 2/4 + 1/4 = 3/4', 1, 1),
(2, 3, 'mcq', 'What is 3/4 ÷ 1/2?', '["3/8", "3/2", "6/4", "1/2"]', '3/2', 'Multiply by reciprocal: 3/4 × 2/1 = 6/4 = 3/2', 1, 2);

-- Insert demo quiz results
INSERT INTO `quiz_results` (`tenant_id`, `quiz_id`, `student_id`, `score`, `total_points`, `earned_points`, `answers`, `started_at`, `completed_at`, `time_taken_seconds`, `passed`) VALUES
(1, 1, 1, 80.00, 5, 4, '{"1":"x = (-b ± √(b^2 - 4ac)) / 2a","2":"x = ±2"}', '2025-01-20 15:30:00', '2025-01-20 15:45:00', 900, 1),
(2, 3, 5, 100.00, 5, 5, '{"1":"3/4","2":"3/2"}', '2025-01-22 10:15:00', '2025-01-22 10:25:00', 600, 1);

-- Insert demo invoices
INSERT INTO `invoices` (`tenant_id`, `invoice_number`, `amount`, `tax`, `total`, `status`, `due_date`, `paid_at`, `created_at`) VALUES
(1, 'INV-2025-001', 99.00, 9.90, 108.90, 'paid', '2025-01-31', '2025-01-15 10:00:00', '2025-01-01 00:00:00'),
(1, 'INV-2025-002', 99.00, 9.90, 108.90, 'pending', '2025-02-28', NULL, '2025-02-01 00:00:00');

-- Insert demo payments
INSERT INTO `payments` (`tenant_id`, `invoice_id`, `amount`, `payment_method`, `transaction_id`, `status`, `created_at`) VALUES
(1, 1, 108.90, 'credit_card', 'TXN-20250115-ABC123', 'completed', '2025-01-15 10:00:00');

-- Insert demo notifications
INSERT INTO `notifications` (`tenant_id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`) VALUES
(1, 5, 'homework_answered', 'Your homework has been answered', 'AI has provided an answer to your quadratic equations question', '/homework/1', 1),
(1, 3, 'new_homework', 'New homework request', 'Alex Thompson submitted a new homework question in Mathematics', '/homework/1', 1),
(2, 7, 'subscription_warning', 'Approaching student limit', 'You have 6 students out of 5 allowed in your Free plan. Please upgrade.', '/admin/subscription', 0),
(2, 8, 'quiz_completed', 'Quiz graded', 'Your Fraction Practice quiz has been graded. Score: 100%', '/ai/quiz/3', 0);

-- ============================================================================
-- END OF SEED DATA
-- ============================================================================

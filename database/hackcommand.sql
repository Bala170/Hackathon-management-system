-- ============================================================
-- HackCommand – Smart Hackathon Operations Management System
-- Database Schema + Seed Data
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `hackcommand` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hackcommand`;

-- ============================================================
-- TABLE: roles
-- ============================================================
CREATE TABLE `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `role_name` VARCHAR(30) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: teams
-- ============================================================
CREATE TABLE `teams` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_name` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: halls
-- ============================================================
CREATE TABLE `halls` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `hall_name` VARCHAR(100) NOT NULL,
  `capacity` INT UNSIGNED NOT NULL DEFAULT 30,
  `location_url` VARCHAR(500) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_hall_name` (`hall_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: users
-- ============================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `team_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role_id`),
  INDEX `idx_team` (`team_id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_users_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: hall_assignments  (team → hall)
-- ============================================================
CREATE TABLE `hall_assignments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id` INT UNSIGNED NOT NULL,
  `hall_id` INT UNSIGNED NOT NULL,
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_team_hall` (`team_id`),
  INDEX `idx_hall` (`hall_id`),
  CONSTRAINT `fk_ha_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ha_hall` FOREIGN KEY (`hall_id`) REFERENCES `halls`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: seats
-- ============================================================
CREATE TABLE `seats` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `hall_id` INT UNSIGNED NOT NULL,
  `seat_number` VARCHAR(10) NOT NULL,
  `user_id` INT UNSIGNED DEFAULT NULL,
  INDEX `idx_seat_hall` (`hall_id`),
  INDEX `idx_seat_user` (`user_id`),
  UNIQUE KEY `uq_hall_seat` (`hall_id`, `seat_number`),
  CONSTRAINT `fk_seats_hall` FOREIGN KEY (`hall_id`) REFERENCES `halls`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_seats_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: announcements
-- ============================================================
CREATE TABLE `announcements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `message` TEXT NOT NULL,
  `posted_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ann_posted` (`posted_by`),
  CONSTRAINT `fk_ann_user` FOREIGN KEY (`posted_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: announcement_reads  (tracks which user read which announcement)
-- ============================================================
CREATE TABLE `announcement_reads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `announcement_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `read_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_ann_user` (`announcement_id`, `user_id`),
  CONSTRAINT `fk_ar_ann` FOREIGN KEY (`announcement_id`) REFERENCES `announcements`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ar_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: attendance
-- ============================================================
CREATE TABLE `attendance` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `hall_id` INT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `status` ENUM('present','absent') NOT NULL DEFAULT 'absent',
  `marked_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_attendance` (`user_id`, `hall_id`, `date`),
  INDEX `idx_att_hall` (`hall_id`),
  INDEX `idx_att_date` (`date`),
  CONSTRAINT `fk_att_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_att_hall` FOREIGN KEY (`hall_id`) REFERENCES `halls`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_att_marker` FOREIGN KEY (`marked_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: submissions
-- ============================================================
CREATE TABLE `submissions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `file_url` VARCHAR(500) DEFAULT NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_sub_team` (`team_id`),
  CONSTRAINT `fk_sub_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: scores
-- ============================================================
CREATE TABLE `scores` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `team_id` INT UNSIGNED NOT NULL,
  `judge_name` VARCHAR(100) NOT NULL,
  `innovation` DECIMAL(4,1) NOT NULL DEFAULT 0.0,
  `technical` DECIMAL(4,1) NOT NULL DEFAULT 0.0,
  `ui_ux` DECIMAL(4,1) NOT NULL DEFAULT 0.0,
  `impact` DECIMAL(4,1) NOT NULL DEFAULT 0.0,
  `total` DECIMAL(5,1) GENERATED ALWAYS AS (`innovation` + `technical` + `ui_ux` + `impact`) STORED,
  `scored_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_team_judge` (`team_id`, `judge_name`),
  INDEX `idx_score_team` (`team_id`),
  CONSTRAINT `fk_score_team` FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_score_user` FOREIGN KEY (`scored_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Roles
INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(2, 'student'),
(3, 'coordinator');

-- Admin user  (password: admin123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role_id`, `team_id`) VALUES
(1, 'System Admin', 'admin@hackcommand.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NULL);

-- Sample Coordinator (password: coord123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role_id`, `team_id`) VALUES
(2, 'Rahul Sharma', 'coordinator@hackcommand.com', '$2y$10$kF1GDqEqSYFmhQ3JkVxP8eJ.d3ZpBXnSg65v4fGHJACo5RLVMPWGG', 3, NULL);

-- Sample Teams
INSERT INTO `teams` (`id`, `team_name`) VALUES
(1, 'Code Crushers'),
(2, 'Bug Busters'),
(3, 'Neural Nexus');

-- Sample Students (password: student123)
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role_id`, `team_id`) VALUES
(3,  'Ananya Verma',   'ananya@hackcommand.com',  '$2y$10$vGxKmEzJhFN6xHZ92HahAeontBt8fE5IHb.XiE9OYFrpc3bE3lhkq', 2, 1),
(4,  'Karan Mehta',    'karan@hackcommand.com',   '$2y$10$vGxKmEzJhFN6xHZ92HahAeontBt8fE5IHb.XiE9OYFrpc3bE3lhkq', 2, 1),
(5,  'Priya Singh',    'priya@hackcommand.com',   '$2y$10$vGxKmEzJhFN6xHZ92HahAeontBt8fE5IHb.XiE9OYFrpc3bE3lhkq', 2, 2),
(6,  'Rohan Das',      'rohan@hackcommand.com',   '$2y$10$vGxKmEzJhFN6xHZ92HahAeontBt8fE5IHb.XiE9OYFrpc3bE3lhkq', 2, 2),
(7,  'Sneha Patel',    'sneha@hackcommand.com',   '$2y$10$vGxKmEzJhFN6xHZ92HahAeontBt8fE5IHb.XiE9OYFrpc3bE3lhkq', 2, 3),
(8,  'Amit Kumar',     'amit@hackcommand.com',    '$2y$10$vGxKmEzJhFN6xHZ92HahAeontBt8fE5IHb.XiE9OYFrpc3bE3lhkq', 2, 3);

-- Sample Halls
INSERT INTO `halls` (`id`, `hall_name`, `capacity`, `location_url`) VALUES
(1, 'Auditorium A', 50, 'https://maps.google.com/?q=Auditorium+A'),
(2, 'Lab Block B',  30, 'https://maps.google.com/?q=Lab+Block+B'),
(3, 'Seminar Hall C', 40, 'https://maps.google.com/?q=Seminar+Hall+C');

-- Sample Hall Assignments
INSERT INTO `hall_assignments` (`team_id`, `hall_id`) VALUES
(1, 1),
(2, 1),
(3, 2);

-- Sample Seats for Auditorium A (first 10 of 50)
INSERT INTO `seats` (`hall_id`, `seat_number`, `user_id`) VALUES
(1, 'A-1', 3),
(1, 'A-2', 4),
(1, 'A-3', 5),
(1, 'A-4', 6),
(1, 'A-5', NULL),
(1, 'A-6', NULL),
(1, 'A-7', NULL),
(1, 'A-8', NULL),
(1, 'A-9', NULL),
(1, 'A-10', NULL);

-- Sample Seats for Lab Block B (first 6 of 30)
INSERT INTO `seats` (`hall_id`, `seat_number`, `user_id`) VALUES
(2, 'B-1', 7),
(2, 'B-2', 8),
(2, 'B-3', NULL),
(2, 'B-4', NULL),
(2, 'B-5', NULL),
(2, 'B-6', NULL);

-- Sample Announcements
INSERT INTO `announcements` (`title`, `message`, `posted_by`) VALUES
('Welcome to HackCommand 2026!', 'We are excited to kick off this hackathon. Please check your hall assignments and seating arrangements on the dashboard. Let the coding begin!', 1),
('Lunch Break at 1:00 PM', 'Lunch will be served in the cafeteria from 1:00 PM to 2:00 PM. Please carry your ID badges.', 1),
('Submission Deadline Extended', 'The final submission deadline has been extended to 6:00 PM. Make sure to submit your projects before then.', 2);

COMMIT;

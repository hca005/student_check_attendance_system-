-- ============================================================
-- schema.sql
-- Attendance & Classroom Engagement Tracker
-- Topic 3 – INS3064 Multimedia Design & Web Development
-- Nhóm 3 thành viên | 12 bảng core | chuẩn 3NF
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `attendance_system`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `attendance_system`;

-- ────────────────────────────────────────────
-- THÀNH VIÊN 1: users · courses · course_enrollments · class_sessions
-- ────────────────────────────────────────────

-- 1. users – Tài khoản hệ thống (Admin / Teacher / Student)
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `full_name`     VARCHAR(100)    NOT NULL,
    `email`         VARCHAR(100)    NOT NULL,
    `password_hash` VARCHAR(255)    NOT NULL,
    `role`          ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
    `student_code`  VARCHAR(20)     DEFAULT NULL COMMENT 'Mã SV, NULL nếu không phải student',
    `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. courses – Học phần / Lớp học
CREATE TABLE IF NOT EXISTS `courses` (
    `id`                        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `course_code`               VARCHAR(20)     NOT NULL,
    `course_name`               VARCHAR(150)    NOT NULL,
    `semester`                  VARCHAR(20)     NOT NULL COMMENT 'VD: 2025-2',
    `absence_threshold`         INT             NOT NULL DEFAULT 3   COMMENT 'Số buổi vắng tối đa trước khi alert',
    `low_engagement_threshold`  DECIMAL(5,2)    NOT NULL DEFAULT 40.00 COMMENT 'Ngưỡng engagement_index thấp (0-100)',
    `attend_score`              DECIMAL(4,2)    NOT NULL DEFAULT 2.00 COMMENT 'Điểm/lần điểm danh đúng giờ',
    `quiz_correct_score`        DECIMAL(4,2)    NOT NULL DEFAULT 2.00 COMMENT 'Điểm/câu quiz đúng',
    `discussion_score`          DECIMAL(4,2)    NOT NULL DEFAULT 1.00 COMMENT 'Điểm/lần tham gia thảo luận',
    `is_active`                 TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_code_semester` (`course_code`, `semester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. course_enrollments – Gán Teacher / Student vào course (N-N)
CREATE TABLE IF NOT EXISTS `course_enrollments` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL,
    `role`        ENUM('teacher','student') NOT NULL,
    `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_user` (`course_id`, `user_id`),
    CONSTRAINT `fk_ce_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ce_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. class_sessions – Từng buổi học của course
CREATE TABLE IF NOT EXISTS `class_sessions` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`    INT UNSIGNED NOT NULL,
    `teacher_id`   INT UNSIGNED NOT NULL,
    `session_date` DATE         NOT NULL,
    `start_time`   TIME         NOT NULL,
    `end_time`     TIME         NOT NULL,
    `title`        VARCHAR(200) DEFAULT NULL,
    `status`       ENUM('upcoming','active','ended') NOT NULL DEFAULT 'upcoming',
    `notes`        TEXT         DEFAULT NULL,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_cs_course`  (`course_id`),
    INDEX `idx_cs_date`    (`session_date`),
    CONSTRAINT `fk_cs_course`   FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cs_teacher`  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────
-- THÀNH VIÊN 2: attendance_methods · attendance_records · quiz_sessions · quiz_questions
-- ────────────────────────────────────────────

-- 5. attendance_methods – Phương thức điểm danh (QR / OTP / Manual)
CREATE TABLE IF NOT EXISTS `attendance_methods` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`  INT UNSIGNED NOT NULL,
    `method_type` ENUM('qr','otp','manual') NOT NULL,
    `token`       VARCHAR(128) DEFAULT NULL COMMENT 'QR token hoặc OTP code',
    `expires_at`  DATETIME     DEFAULT NULL COMMENT 'Thời gian hết hạn token/OTP',
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_am_session` (`session_id`),
    INDEX `idx_am_token`   (`token`),
    CONSTRAINT `fk_am_session` FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. attendance_records – Bản ghi điểm danh của từng student theo buổi
CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`     INT UNSIGNED NOT NULL,
    `student_id`     INT UNSIGNED NOT NULL,
    `method_id`      INT UNSIGNED DEFAULT NULL,
    `status`         ENUM('present','absent','late','excused') NOT NULL DEFAULT 'absent',
    `checked_in_at`  DATETIME     DEFAULT NULL,
    `note`           TEXT         DEFAULT NULL,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_session_student` (`session_id`, `student_id`),
    CONSTRAINT `fk_ar_session`  FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_ar_student`  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)               ON DELETE CASCADE,
    CONSTRAINT `fk_ar_method`   FOREIGN KEY (`method_id`)  REFERENCES `attendance_methods`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. quiz_sessions – Phiên quiz trong buổi học
CREATE TABLE IF NOT EXISTS `quiz_sessions` (
    `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`          INT UNSIGNED NOT NULL,
    `title`               VARCHAR(200) NOT NULL,
    `description`         TEXT         DEFAULT NULL,
    `time_limit_minutes`  INT          DEFAULT NULL COMMENT 'NULL = không giới hạn thời gian',
    `status`              ENUM('draft','open','closed') NOT NULL DEFAULT 'draft',
    `allow_retake`        TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_qs_session` (`session_id`),
    CONSTRAINT `fk_qs_session` FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. quiz_questions – Câu hỏi trắc nghiệm trong quiz
CREATE TABLE IF NOT EXISTS `quiz_questions` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id`        INT UNSIGNED NOT NULL,
    `question_text`  TEXT         NOT NULL,
    `option_a`       VARCHAR(255) NOT NULL,
    `option_b`       VARCHAR(255) NOT NULL,
    `option_c`       VARCHAR(255) DEFAULT NULL,
    `option_d`       VARCHAR(255) DEFAULT NULL,
    `correct_option` ENUM('A','B','C','D') NOT NULL,
    `points`         DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    `order_num`      INT          NOT NULL DEFAULT 1,
    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_qq_quiz` (`quiz_id`),
    CONSTRAINT `fk_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────
-- THÀNH VIÊN 3: quiz_submissions · interaction_logs · engagement_scores · alert_logs
-- ────────────────────────────────────────────

-- 9. quiz_submissions – Bài nộp quiz của student (auto-graded)
CREATE TABLE IF NOT EXISTS `quiz_submissions` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id`      INT UNSIGNED NOT NULL,
    `student_id`   INT UNSIGNED NOT NULL,
    `total_score`  DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `max_score`    DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `answers`      JSON         DEFAULT NULL COMMENT 'JSON object: {"question_id": "chosen_option"}',
    `submitted_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_quiz_student` (`quiz_id`, `student_id`),
    CONSTRAINT `fk_sub_quiz`    FOREIGN KEY (`quiz_id`)    REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. interaction_logs – Ghi log mọi hành động tương tác của student
CREATE TABLE IF NOT EXISTS `interaction_logs` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED NOT NULL,
    `session_id`    INT UNSIGNED NOT NULL,
    `action_type`   ENUM('check_in','submit_quiz','answer_question','discussion','other') NOT NULL,
    `reference_id`  INT UNSIGNED DEFAULT NULL COMMENT 'ID bản ghi liên quan (attendance_records.id, quiz_submissions.id...)',
    `description`   VARCHAR(255) DEFAULT NULL,
    `points_earned` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_il_user`    (`user_id`),
    INDEX `idx_il_session` (`session_id`),
    CONSTRAINT `fk_il_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)          ON DELETE CASCADE,
    CONSTRAINT `fk_il_session` FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. engagement_scores – Tổng hợp điểm tham gia theo student × course
CREATE TABLE IF NOT EXISTS `engagement_scores` (
    `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`               INT UNSIGNED NOT NULL,
    `course_id`                INT UNSIGNED NOT NULL,
    `total_sessions`           INT          NOT NULL DEFAULT 0,
    `attended_sessions`        INT          NOT NULL DEFAULT 0,
    `total_quiz_score`         DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `total_interaction_points` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `engagement_index`         DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Điểm tổng hợp 0–100',
    `calculated_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_student_course` (`student_id`, `course_id`),
    CONSTRAINT `fk_es_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_es_course`  FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. alert_logs – Cảnh báo sinh viên có nguy cơ chuyên cần / tương tác thấp
CREATE TABLE IF NOT EXISTS `alert_logs` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`    INT UNSIGNED NOT NULL,
    `course_id`     INT UNSIGNED NOT NULL,
    `alert_type`    ENUM('high_absence','low_engagement','missed_quiz') NOT NULL,
    `alert_message` TEXT         NOT NULL,
    `status`        ENUM('open','resolved','ignored') NOT NULL DEFAULT 'open',
    `resolved_by`   INT UNSIGNED DEFAULT NULL,
    `resolved_at`   DATETIME     DEFAULT NULL,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_al_student` (`student_id`),
    INDEX `idx_al_status`  (`status`),
    CONSTRAINT `fk_al_student`   FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_al_course`    FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_al_resolver`  FOREIGN KEY (`resolved_by`) REFERENCES `users`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
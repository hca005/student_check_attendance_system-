-- ============================================================
-- student_attendance_database.sql
-- Student Check Attendance System
-- Topic 3 – INS3064 Multimedia Design & Web Development
-- Nhóm 3 thành viên | 12 bảng core | chuẩn 3NF
-- Import order: 1. student_attendance_database.sql  2. seed_demo.sql
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `student_attendance_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `student_attendance_db`;

-- ============================================================
-- THÀNH VIÊN: Cẩm Anh – Admin
-- Bảng phụ trách: users · courses · enrollments · alerts
-- ============================================================

-- 1. users
-- Lưu tài khoản hệ thống: admin (phòng đào tạo), teacher, student.
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`     VARCHAR(100) NOT NULL,
    `email`         VARCHAR(100) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('admin','teacher','student') NOT NULL DEFAULT 'student',
    `student_code`  VARCHAR(20)  DEFAULT NULL COMMENT 'Mã sinh viên – NULL nếu không phải student',
    `phone`         VARCHAR(20)  DEFAULT NULL,
    `avatar`        VARCHAR(255) DEFAULT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_email` (`email`),
    UNIQUE KEY `uq_student_code` (`student_code`),
    INDEX `idx_role`      (`role`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tài khoản hệ thống: admin (phòng đào tạo), teacher, student';

-- 2. courses
-- Lưu thông tin học phần / lớp học từng kỳ.
-- Các trường scoring (attend_score, quiz_correct_score, discussion_score) là giá trị mặc định/fallback.
-- Trọng số thực tế khi tính engagement_index được lưu chi tiết trong bảng engagement_rules.
-- low_engagement_threshold là ngưỡng để hệ thống tự động tạo alert khi engagement_index < ngưỡng.
CREATE TABLE IF NOT EXISTS `courses` (
    `id`                       INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_code`              VARCHAR(20)   NOT NULL,
    `course_name`              VARCHAR(150)  NOT NULL,
    `credits`                  TINYINT       NOT NULL DEFAULT 3,
    `semester`                 VARCHAR(20)   NOT NULL   COMMENT 'VD: 2025-2',
    `room`                     VARCHAR(50)   DEFAULT NULL,
    `schedule_info`            VARCHAR(200)  DEFAULT NULL COMMENT 'VD: Thứ 5, 07:30-09:30',
    `absence_threshold`        INT           NOT NULL DEFAULT 3
                               COMMENT 'Số buổi vắng tối đa – vượt ngưỡng thì tạo alert low_attendance',
    `low_engagement_threshold` DECIMAL(5,2)  NOT NULL DEFAULT 40.00
                               COMMENT 'Ngưỡng engagement_index (0-100) – thấp hơn thì tạo alert low_engagement',
    `attend_score`             DECIMAL(4,2)  NOT NULL DEFAULT 2.00
                               COMMENT 'Điểm mặc định/fallback mỗi lần điểm danh đúng giờ',
    `quiz_correct_score`       DECIMAL(4,2)  NOT NULL DEFAULT 2.00
                               COMMENT 'Điểm mặc định/fallback mỗi câu quiz đúng',
    `discussion_score`         DECIMAL(4,2)  NOT NULL DEFAULT 1.00
                               COMMENT 'Điểm mặc định/fallback mỗi lần tham gia thảo luận',
    `is_active`                TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`               DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`               DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_semester` (`course_code`, `semester`),
    INDEX `idx_semester`  (`semester`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Học phần / lớp học theo từng kỳ';

-- 3. enrollments
-- Quan hệ N-N giữa users và courses (role = teacher hoặc student).
CREATE TABLE IF NOT EXISTS `enrollments` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL,
    `role`        ENUM('teacher','student') NOT NULL,
    `enrolled_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_user` (`course_id`, `user_id`),
    INDEX `idx_en_user` (`user_id`),
    INDEX `idx_en_role` (`role`),
    CONSTRAINT `fk_en_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_en_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Gán teacher / student vào course (N-N)';

-- 4. alerts
-- Cảnh báo tự động tạo khi:
--   low_attendance : số buổi vắng vượt courses.absence_threshold
--   low_engagement : engagement_index thấp hơn courses.low_engagement_threshold
CREATE TABLE IF NOT EXISTS `alerts` (
    `alert_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`  INT UNSIGNED NOT NULL,
    `course_id`   INT UNSIGNED NOT NULL,
    `alert_type`  ENUM('low_attendance','low_engagement') NOT NULL,
    `message`     TEXT         NOT NULL,
    `severity`    ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    `status`      ENUM('pending','reviewed','resolved') NOT NULL DEFAULT 'pending',
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_by` INT UNSIGNED DEFAULT NULL COMMENT 'admin hoặc teacher đã xem/xử lý',
    `reviewed_at` DATETIME     DEFAULT NULL,
    PRIMARY KEY (`alert_id`),
    INDEX `idx_al_student`  (`student_id`),
    INDEX `idx_al_course`   (`course_id`),
    INDEX `idx_al_type`     (`alert_type`),
    INDEX `idx_al_severity` (`severity`),
    INDEX `idx_al_status`   (`status`),
    INDEX `idx_al_created`  (`created_at`),
    CONSTRAINT `fk_al_student`  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_al_course`   FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_al_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Cảnh báo sinh viên vắng nhiều hoặc tương tác thấp';

-- ============================================================
-- THÀNH VIÊN: Khổng Linh – Teacher
-- Bảng phụ trách: class_sessions · attendance_methods · quiz_sessions · engagement_rules
-- ============================================================

-- 5. class_sessions
CREATE TABLE IF NOT EXISTS `class_sessions` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`    INT UNSIGNED NOT NULL,
    `teacher_id`   INT UNSIGNED NOT NULL,
    `session_date` DATE         NOT NULL,
    `start_time`   TIME         NOT NULL,
    `end_time`     TIME         NOT NULL,
    `title`        VARCHAR(200) DEFAULT NULL,
    `description`  TEXT         DEFAULT NULL,
    `location`     VARCHAR(100) DEFAULT NULL,
    `status`       ENUM('upcoming','active','ended') NOT NULL DEFAULT 'upcoming',
    `notes`        TEXT         DEFAULT NULL,
    `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_cs_course`  (`course_id`),
    INDEX `idx_cs_date`    (`session_date`),
    INDEX `idx_cs_status`  (`status`),
    CONSTRAINT `fk_cs_course`   FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cs_teacher`  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Từng buổi học cụ thể của một course';

-- 6. attendance_methods
-- Mỗi buổi học có thể có nhiều phương thức điểm danh (QR, OTP, thủ công).
CREATE TABLE IF NOT EXISTS `attendance_methods` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`  INT UNSIGNED NOT NULL,
    `method_type` ENUM('qr','otp','manual') NOT NULL,
    `token`       VARCHAR(128) DEFAULT NULL COMMENT 'QR token hoặc OTP code',
    `expires_at`  DATETIME     DEFAULT NULL COMMENT 'Thời gian hết hạn – NULL nếu manual',
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_am_session` (`session_id`),
    INDEX `idx_am_token`   (`token`),
    INDEX `idx_am_active`  (`is_active`),
    CONSTRAINT `fk_am_session` FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Phương thức điểm danh: QR Code, OTP, thủ công';

-- 7. quiz_sessions
CREATE TABLE IF NOT EXISTS `quiz_sessions` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`         INT UNSIGNED NOT NULL,
    `title`              VARCHAR(200) NOT NULL,
    `description`        TEXT         DEFAULT NULL,
    `time_limit_minutes` INT          DEFAULT NULL COMMENT 'NULL = không giới hạn',
    `status`             ENUM('draft','open','closed') NOT NULL DEFAULT 'draft',
    `allow_retake`       TINYINT(1)   NOT NULL DEFAULT 0,
    `questions`          TEXT         DEFAULT NULL COMMENT 'JSON string chứa mảng câu hỏi: [{"id":"1","text":"...","options":{"A":"...","B":"..."},"correct":"A"}]',
    `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_qs_session` (`session_id`),
    INDEX `idx_qs_status`  (`status`),
    CONSTRAINT `fk_qs_session` FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Phiên quiz trắc nghiệm trong buổi học (chứa luôn JSON câu hỏi)';

-- 8. engagement_rules
-- Lưu trọng số / quy tắc tính engagement_index theo từng course.
-- Ưu tiên: nếu course có engagement_rules thì dùng rule_value thay vì giá trị mặc định trong courses.
-- Công thức mẫu: engagement_index = (attended/total * attendance_weight)
--                                  + (quiz_ratio   * quiz_weight)
--                                  + (interact_pts * interaction_weight)
CREATE TABLE IF NOT EXISTS `engagement_rules` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `rule_name`   VARCHAR(100) NOT NULL COMMENT 'Tên mô tả, VD: Trọng số chuyên cần',
    `rule_key`    VARCHAR(50)  NOT NULL COMMENT 'Key: attendance_weight | quiz_weight | interaction_weight | discussion_bonus',
    `rule_value`  DECIMAL(6,2) NOT NULL COMMENT 'Giá trị trọng số (%)',
    `description` TEXT         DEFAULT NULL,
    `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_course_rule_key` (`course_id`, `rule_key`),
    INDEX `idx_er_course` (`course_id`),
    CONSTRAINT `fk_er_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Trọng số / quy tắc tính engagement_index theo từng học phần';

-- ============================================================
-- THÀNH VIÊN: Hòa – Student
-- Bảng phụ trách: attendance_records · quiz_submissions · interaction_logs · engagement_scores
-- ============================================================

-- 9. attendance_records
CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`    INT UNSIGNED NOT NULL,
    `student_id`    INT UNSIGNED NOT NULL,
    `method_id`     INT UNSIGNED DEFAULT NULL,
    `status`        ENUM('present','absent','late','excused') NOT NULL DEFAULT 'absent',
    `checked_in_at` DATETIME     DEFAULT NULL,
    `note`          TEXT         DEFAULT NULL,
    `verified_by`   INT UNSIGNED DEFAULT NULL COMMENT 'Teacher ID nếu điểm danh thủ công',
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_session_student` (`session_id`, `student_id`),
    INDEX `idx_ar_student` (`student_id`),
    INDEX `idx_ar_status`  (`status`),
    CONSTRAINT `fk_ar_session`  FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_ar_student`  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)              ON DELETE CASCADE,
    CONSTRAINT `fk_ar_method`   FOREIGN KEY (`method_id`)  REFERENCES `attendance_methods`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ar_verified` FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`)             ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Kết quả điểm danh từng sinh viên theo từng buổi học';

-- 10. quiz_submissions
-- Cột answers dùng TEXT (không dùng JSON) để tương thích MySQL 5.x / MariaDB phổ biến trong XAMPP.
-- Lưu dạng JSON string: {"question_id":"chosen_option"}
CREATE TABLE IF NOT EXISTS `quiz_submissions` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id`      INT UNSIGNED NOT NULL,
    `student_id`   INT UNSIGNED NOT NULL,
    `total_score`  DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `max_score`    DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `answers`      TEXT         DEFAULT NULL COMMENT 'JSON string: {"question_id":"chosen_option"}',
    `submitted_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_quiz_student` (`quiz_id`, `student_id`),
    INDEX `idx_sub_student` (`student_id`),
    CONSTRAINT `fk_sub_quiz`    FOREIGN KEY (`quiz_id`)    REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Bài nộp và điểm quiz của sinh viên (chấm tự động)';

-- 11. interaction_logs
CREATE TABLE IF NOT EXISTS `interaction_logs` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED NOT NULL,
    `session_id`    INT UNSIGNED NOT NULL,
    `action_type`   ENUM('check_in','submit_quiz','answer_question','discussion','other') NOT NULL,
    `reference_id`  INT UNSIGNED DEFAULT NULL COMMENT 'ID bản ghi liên quan (attendance_records, quiz_submissions...)',
    `description`   VARCHAR(255) DEFAULT NULL,
    `points_earned` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_il_user`    (`user_id`),
    INDEX `idx_il_session` (`session_id`),
    INDEX `idx_il_action`  (`action_type`),
    CONSTRAINT `fk_il_user`    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)          ON DELETE CASCADE,
    CONSTRAINT `fk_il_session` FOREIGN KEY (`session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log từng hành động tương tác của sinh viên trong buổi học';

-- 12. engagement_scores
-- Tổng hợp điểm chuyên cần + tương tác theo cặp student × course.
-- Được cập nhật mỗi khi có sự kiện check-in / quiz / discussion.
CREATE TABLE IF NOT EXISTS `engagement_scores` (
    `id`                       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `student_id`               INT UNSIGNED NOT NULL,
    `course_id`                INT UNSIGNED NOT NULL,
    `total_sessions`           INT          NOT NULL DEFAULT 0,
    `attended_sessions`        INT          NOT NULL DEFAULT 0,
    `late_sessions`            INT          NOT NULL DEFAULT 0,
    `absent_sessions`          INT          NOT NULL DEFAULT 0,
    `total_quiz_score`         DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `total_interaction_points` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `engagement_index`         DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng hợp 0–100; ngưỡng alert lấy từ courses.low_engagement_threshold',
    `last_activity_at`         DATETIME     DEFAULT NULL,
    `calculated_at`            DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_student_course` (`student_id`, `course_id`),
    INDEX `idx_es_course`      (`course_id`),
    INDEX `idx_es_engagement`  (`engagement_index`),
    CONSTRAINT `fk_es_student` FOREIGN KEY (`student_id`) REFERENCES `users`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_es_course`  FOREIGN KEY (`course_id`)  REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tổng hợp điểm chuyên cần & tương tác theo student × course';

SET FOREIGN_KEY_CHECKS = 1;

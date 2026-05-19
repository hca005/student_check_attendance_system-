-- ============================================================
-- seed.sql
-- Dữ liệu mẫu cho Attendance & Classroom Engagement Tracker
-- MẬT KHẨU TẤT CẢ TÀI KHOẢN DEMO: password
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM `alert_logs`;
DELETE FROM `engagement_scores`;
DELETE FROM `interaction_logs`;
DELETE FROM `quiz_submissions`;
DELETE FROM `quiz_questions`;
DELETE FROM `quiz_sessions`;
DELETE FROM `attendance_records`;
DELETE FROM `attendance_methods`;
DELETE FROM `class_sessions`;
DELETE FROM `course_enrollments`;
DELETE FROM `courses`;
DELETE FROM `users`;

ALTER TABLE `alert_logs` AUTO_INCREMENT = 1;
ALTER TABLE `engagement_scores` AUTO_INCREMENT = 1;
ALTER TABLE `interaction_logs` AUTO_INCREMENT = 1;
ALTER TABLE `quiz_submissions` AUTO_INCREMENT = 1;
ALTER TABLE `quiz_questions` AUTO_INCREMENT = 1;
ALTER TABLE `quiz_sessions` AUTO_INCREMENT = 1;
ALTER TABLE `attendance_records` AUTO_INCREMENT = 1;
ALTER TABLE `attendance_methods` AUTO_INCREMENT = 1;
ALTER TABLE `class_sessions` AUTO_INCREMENT = 1;
ALTER TABLE `course_enrollments` AUTO_INCREMENT = 1;
ALTER TABLE `courses` AUTO_INCREMENT = 1;
ALTER TABLE `users` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- ────────────────────────────────────────────
-- 1. USERS
-- Hash bên dưới là bcrypt của chuỗi "password"
-- Dùng để test: password_verify("password", $hash) = true
-- ────────────────────────────────────────────
INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `student_code`, `is_active`) VALUES
(1, 'Admin System',       'admin@ischool.vn',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',   NULL,        1),
(2, 'Nguyễn Văn An',     'an.teacher@ischool.vn',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', NULL,        1),
(3, 'Trần Thị Bình',     'binh.teacher@ischool.vn',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', NULL,        1),
(4, 'Lê Văn Cường',      'cuong@ischool.vn',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'SV2021001', 1),
(5, 'Phạm Thị Dung',     'dung@ischool.vn',          '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'SV2021002', 1),
(6, 'Hoàng Văn Em',      'em@ischool.vn',            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'SV2021003', 1),
(7, 'Ngô Thị Phương',    'phuong@ischool.vn',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'SV2021004', 1),
(8, 'Vũ Đức Giang',      'giang@ischool.vn',         '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'SV2021005', 1),
(9, 'Đặng Thị Hoa',      'hoa@ischool.vn',           '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'SV2021006', 1);

-- ────────────────────────────────────────────
-- 2. COURSES
-- ────────────────────────────────────────────
INSERT INTO `courses` (`id`, `course_code`, `course_name`, `semester`, `absence_threshold`, `low_engagement_threshold`, `attend_score`, `quiz_correct_score`, `discussion_score`) VALUES
(1, 'INS3064', 'Multimedia Design and Web Development', '2025-2', 3, 40.00, 2.00, 2.00, 1.00),
(2, 'INS2045', 'Database Systems',                      '2025-2', 4, 35.00, 2.00, 3.00, 1.00);

-- ────────────────────────────────────────────
-- 3. COURSE ENROLLMENTS
-- ────────────────────────────────────────────
INSERT INTO `course_enrollments` (`course_id`, `user_id`, `role`) VALUES
-- INS3064: GV An dạy, 6 sinh viên
(1, 2, 'teacher'),
(1, 4, 'student'), (1, 5, 'student'), (1, 6, 'student'),
(1, 7, 'student'), (1, 8, 'student'), (1, 9, 'student'),
-- INS2045: GV Bình dạy, 3 sinh viên
(2, 3, 'teacher'),
(2, 4, 'student'), (2, 5, 'student'), (2, 6, 'student');

-- ────────────────────────────────────────────
-- 4. CLASS SESSIONS
-- ────────────────────────────────────────────
INSERT INTO `class_sessions` (`id`, `course_id`, `teacher_id`, `session_date`, `start_time`, `end_time`, `title`, `status`) VALUES
(1, 1, 2, '2025-05-01', '07:30:00', '09:30:00', 'Buổi 1 – HTML & CSS cơ bản',   'ended'),
(2, 1, 2, '2025-05-08', '07:30:00', '09:30:00', 'Buổi 2 – JavaScript & DOM',    'ended'),
(3, 1, 2, '2025-05-15', '07:30:00', '09:30:00', 'Buổi 3 – PHP căn bản',         'active'),
(4, 1, 2, '2025-05-22', '07:30:00', '09:30:00', 'Buổi 4 – MySQL & PDO',         'upcoming'),
(5, 2, 3, '2025-05-02', '13:00:00', '15:00:00', 'Buổi 1 – ER Diagram',          'ended'),
(6, 2, 3, '2025-05-09', '13:00:00', '15:00:00', 'Buổi 2 – Normalization 3NF',   'ended');

-- ────────────────────────────────────────────
-- 5. ATTENDANCE METHODS
-- ────────────────────────────────────────────
INSERT INTO `attendance_methods` (`id`, `session_id`, `method_type`, `token`, `expires_at`, `is_active`) VALUES
(1, 1, 'qr',     'QR_S1_ABC123DEF456', '2025-05-01 10:00:00', 0),
(2, 2, 'otp',    '847261',              '2025-05-08 08:00:00', 0),
(3, 3, 'qr',     'QR_S3_GHI789JKL012', '2025-05-15 10:00:00', 1),
(4, 3, 'manual', NULL,                  NULL,                   1),
(5, 5, 'otp',    '193847',              '2025-05-02 15:00:00', 0),
(6, 6, 'qr',     'QR_S6_MNO345PQR678', '2025-05-09 16:00:00', 0);

-- ────────────────────────────────────────────
-- 6. ATTENDANCE RECORDS
-- ────────────────────────────────────────────
INSERT INTO `attendance_records` (`session_id`, `student_id`, `method_id`, `status`, `checked_in_at`) VALUES
-- Buổi 1 INS3064
(1, 4, 1, 'present', '2025-05-01 07:35:00'),
(1, 5, 1, 'present', '2025-05-01 07:40:00'),
(1, 6, 1, 'absent',  NULL),
(1, 7, 1, 'present', '2025-05-01 07:38:00'),
(1, 8, 1, 'late',    '2025-05-01 08:10:00'),
(1, 9, 1, 'present', '2025-05-01 07:32:00'),
-- Buổi 2 INS3064
(2, 4, 2, 'present', '2025-05-08 07:36:00'),
(2, 5, 2, 'absent',  NULL),
(2, 6, 2, 'absent',  NULL),
(2, 7, 2, 'present', '2025-05-08 07:40:00'),
(2, 8, 2, 'present', '2025-05-08 07:35:00'),
(2, 9, 2, 'excused', NULL),
-- Buổi 1 INS2045
(5, 4, 5, 'present', '2025-05-02 13:05:00'),
(5, 5, 5, 'present', '2025-05-02 13:02:00'),
(5, 6, 5, 'absent',  NULL),
-- Buổi 2 INS2045
(6, 4, 6, 'present', '2025-05-09 13:08:00'),
(6, 5, 6, 'late',    '2025-05-09 13:45:00'),
(6, 6, 6, 'absent',  NULL);

-- ────────────────────────────────────────────
-- 7. QUIZ SESSIONS
-- ────────────────────────────────────────────
INSERT INTO `quiz_sessions` (`id`, `session_id`, `title`, `description`, `time_limit_minutes`, `status`, `allow_retake`) VALUES
(1, 1, 'Quiz HTML & CSS',       'Kiểm tra kiến thức buổi 1',          10, 'closed', 0),
(2, 2, 'Quiz JavaScript',       'Kiểm tra DOM manipulation',           15, 'closed', 0),
(3, 3, 'Quiz PHP Căn bản',     'Kiểm tra kiến thức PHP cơ bản',       10, 'open',   0);

-- ────────────────────────────────────────────
-- 8. QUIZ QUESTIONS
-- ────────────────────────────────────────────
INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `points`, `order_num`) VALUES
-- Quiz 1
(1, 1, 'Thẻ nào tạo tiêu đề lớn nhất trong HTML?',            '<h1>',                   '<head>',                '<title>',              '<header>', 'A', 1.00, 1),
(2, 1, 'CSS là viết tắt của?',                                  'Creative Style System',  'Cascading Style Sheets','Computer Style Syntax', 'Coded Style Sheet', 'B', 1.00, 2),
-- Quiz 2
(3, 2, 'Chọn phần tử theo id trong JavaScript bằng hàm nào?',  'document.getElement()',  'document.getElementById()', 'document.queryId()', 'document.selectId()', 'B', 1.00, 1),
(4, 2, 'typeof null trong JavaScript trả về gì?',               '"undefined"',            '"null"',                '"object"',             '"boolean"', 'C', 1.00, 2),
-- Quiz 3
(5, 3, 'PHP dùng ký hiệu nào để khai báo biến?',               '@',                      '#',                     '$',                    '&', 'C', 1.00, 1),
(6, 3, 'Hàm/cách nào dùng kết nối MySQL trong PHP hiện đại?',  'mysql_connect()',        'new PDO()',             'mysqli_connect()',     'Cả B và C đều đúng', 'D', 1.00, 2);

-- ────────────────────────────────────────────
-- 9. QUIZ SUBMISSIONS
-- ────────────────────────────────────────────
INSERT INTO `quiz_submissions` (`quiz_id`, `student_id`, `total_score`, `max_score`, `answers`, `submitted_at`) VALUES
(1, 4, 2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:30:00'),
(1, 5, 1.00, 2.00, '{"1":"A","2":"A"}', '2025-05-01 08:32:00'),
(1, 7, 2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:28:00'),
(1, 8, 1.00, 2.00, '{"1":"B","2":"B"}', '2025-05-01 08:35:00'),
(1, 9, 2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:27:00'),
(2, 4, 2.00, 2.00, '{"3":"B","4":"C"}', '2025-05-08 08:45:00'),
(2, 7, 1.00, 2.00, '{"3":"B","4":"A"}', '2025-05-08 08:50:00'),
(2, 8, 2.00, 2.00, '{"3":"B","4":"C"}', '2025-05-08 08:43:00');

-- ────────────────────────────────────────────
-- 10. INTERACTION LOGS
-- ────────────────────────────────────────────
INSERT INTO `interaction_logs` (`user_id`, `session_id`, `action_type`, `reference_id`, `description`, `points_earned`) VALUES
-- Buổi 1: check-in
(4, 1, 'check_in', 1, 'Check-in qua QR', 2.00),
(5, 1, 'check_in', 2, 'Check-in qua QR', 2.00),
(7, 1, 'check_in', 4, 'Check-in qua QR', 2.00),
(8, 1, 'check_in', 5, 'Check-in muộn',   1.00),
(9, 1, 'check_in', 6, 'Check-in qua QR', 2.00),
-- Buổi 1: submit quiz 1
(4, 1, 'submit_quiz', 1, 'Quiz 1 – 2/2 điểm', 2.00),
(5, 1, 'submit_quiz', 2, 'Quiz 1 – 1/2 điểm', 1.00),
(7, 1, 'submit_quiz', 3, 'Quiz 1 – 2/2 điểm', 2.00),
(8, 1, 'submit_quiz', 4, 'Quiz 1 – 1/2 điểm', 1.00),
(9, 1, 'submit_quiz', 5, 'Quiz 1 – 2/2 điểm', 2.00),
-- Buổi 2: check-in
(4, 2, 'check_in', 7,  'Check-in qua OTP', 2.00),
(7, 2, 'check_in', 10, 'Check-in qua OTP', 2.00),
(8, 2, 'check_in', 11, 'Check-in qua OTP', 2.00),
-- Buổi 2: submit quiz 2
(4, 2, 'submit_quiz', 6, 'Quiz 2 – 2/2 điểm', 2.00),
(7, 2, 'submit_quiz', 7, 'Quiz 2 – 1/2 điểm', 1.00),
(8, 2, 'submit_quiz', 8, 'Quiz 2 – 2/2 điểm', 2.00);

-- ────────────────────────────────────────────
-- 11. ENGAGEMENT SCORES
-- Công thức demo: (attended/total * 50) + (quiz + interaction points / max * 50)
-- ────────────────────────────────────────────
INSERT INTO `engagement_scores` (`student_id`, `course_id`, `total_sessions`, `attended_sessions`, `total_quiz_score`, `total_interaction_points`, `engagement_index`) VALUES
-- INS3064
(4, 1, 2, 2, 4.00,  8.00, 88.00),   -- Cường:  đều đặn, quiz tốt
(5, 1, 2, 1, 1.00,  3.00, 32.00),   -- Dung:   vắng 1 buổi, quiz yếu → ALERT low_engagement
(6, 1, 2, 0, 0.00,  0.00,  0.00),   -- Em:     vắng cả 2 buổi → ALERT high_absence
(7, 1, 2, 2, 3.00,  7.00, 78.00),   -- Phương: ổn
(8, 1, 2, 2, 3.00,  7.00, 72.00),   -- Giang:  ổn
(9, 1, 2, 1, 2.00,  4.00, 55.00),   -- Hoa:    excused 1 buổi
-- INS2045
(4, 2, 2, 2, 0.00,  4.00, 65.00),
(5, 2, 2, 2, 0.00,  4.00, 55.00),
(6, 2, 2, 0, 0.00,  0.00,  0.00);   -- Em vắng INS2045 cũng luôn

-- ────────────────────────────────────────────
-- 12. ALERT LOGS
-- ────────────────────────────────────────────
INSERT INTO `alert_logs` (`student_id`, `course_id`, `alert_type`, `alert_message`, `status`) VALUES
(6, 1, 'high_absence',    'Sinh viên Hoàng Văn Em đã vắng 2/2 buổi học môn INS3064. Đề nghị liên hệ sinh viên.', 'open'),
(5, 1, 'low_engagement',  'Sinh viên Phạm Thị Dung có điểm tương tác 32/100, thấp hơn ngưỡng 40 của môn INS3064.', 'open'),
(6, 2, 'high_absence',    'Sinh viên Hoàng Văn Em vắng 2/2 buổi học môn INS2045.', 'open');
SET FOREIGN_KEY_CHECKS = 1;
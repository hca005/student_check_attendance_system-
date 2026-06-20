-- ============================================================
-- seed_demo.sql
-- Dữ liệu mẫu – Student Check Attendance System
-- ⚠️  CHỈ DÙNG CHO DEMO / DEVELOPMENT
--     File này có TRUNCATE TABLE – KHÔNG import vào database thật
--     vì sẽ xóa sạch toàn bộ dữ liệu hiện có.
-- Import order: 1. student_attendance_database.sql  2. seed_demo.sql
-- ============================================================
-- TÀI KHOẢN DEMO – mật khẩu tất cả: 123456
--   admin@ischool.vn          (Admin – Phòng Đào Tạo)
--   an.teacher@ischool.vn     (Teacher – Nguyễn Văn An)
--   binh.teacher@ischool.vn   (Teacher – Trần Thị Bình)
--   cuong@ischool.vn          (Student SV2021001)
--   dung@ischool.vn           (Student SV2021002)
--   em@ischool.vn             (Student SV2021003 – nhiều alert)
--   phuong@ischool.vn         (Student SV2021004)
--   giang@ischool.vn          (Student SV2021005)
--   hoa@ischool.vn            (Student SV2021006)
--   khang@ischool.vn          (Student SV2021007)
--   lananh@ischool.vn         (Student SV2021008)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

USE `student_attendance_db`;

-- ⚠️  TRUNCATE – chỉ dùng khi reset demo data
TRUNCATE TABLE `alerts`;
TRUNCATE TABLE `engagement_scores`;
TRUNCATE TABLE `engagement_rules`;
TRUNCATE TABLE `interaction_logs`;
TRUNCATE TABLE `quiz_submissions`;
TRUNCATE TABLE `quiz_sessions`;
TRUNCATE TABLE `attendance_records`;
TRUNCATE TABLE `attendance_methods`;
TRUNCATE TABLE `class_sessions`;
TRUNCATE TABLE `enrollments`;
TRUNCATE TABLE `courses`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ════════════════════════════════════════════
-- 1. USERS
-- password_hash = bcrypt(cost=10) của chuỗi "123456"
-- Đã verify: password_verify('123456', '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm') = true
-- ════════════════════════════════════════════
INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `student_code`, `phone`, `is_active`) VALUES
(1,  'Phòng Đào Tạo Admin', 'admin@ischool.vn',          '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'admin',   NULL,        '0241000001', 1),
(2,  'Nguyễn Văn An',       'an.teacher@ischool.vn',     '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'teacher', NULL,        '0911000002', 1),
(3,  'Trần Thị Bình',       'binh.teacher@ischool.vn',   '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'teacher', NULL,        '0911000003', 1),
(4,  'Lê Văn Cường',        'cuong@ischool.vn',          '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021001', '0912000004', 1),
(5,  'Phạm Thị Dung',       'dung@ischool.vn',           '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021002', '0912000005', 1),
(6,  'Hoàng Văn Em',        'em@ischool.vn',             '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021003', '0912000006', 1),
(7,  'Ngô Thị Phương',      'phuong@ischool.vn',         '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021004', '0912000007', 1),
(8,  'Vũ Đức Giang',        'giang@ischool.vn',          '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021005', '0912000008', 1),
(9,  'Đặng Thị Hoa',        'hoa@ischool.vn',            '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021006', '0912000009', 1),
(10, 'Bùi Quốc Khang',      'khang@ischool.vn',          '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021007', '0912000010', 1),
(11, 'Trịnh Lan Anh',       'lananh@ischool.vn',         '$2y$10$1BpKQWkE0PPM1cvaw27FHutEOkGoYyFi5mlmrsYC8JJA0oiilKAbm', 'student', 'SV2021008', '0912000011', 1);

-- ════════════════════════════════════════════
-- 2. COURSES
-- absence_threshold : vắng >= ngưỡng → alert low_attendance
-- low_engagement_threshold : engagement_index < ngưỡng → alert low_engagement
-- attend_score, quiz_correct_score, discussion_score : điểm fallback (ghi đè bởi engagement_rules)
-- ════════════════════════════════════════════
INSERT INTO `courses` (`id`, `course_code`, `course_name`, `credits`, `semester`,
                        `room`, `schedule_info`,
                        `absence_threshold`, `low_engagement_threshold`,
                        `attend_score`, `quiz_correct_score`, `discussion_score`, `is_active`) VALUES
(1, 'INS3064', 'Multimedia Design and Web Development', 3, '2025-2', 'A2.01', 'Thứ 5, 07:30-09:30',     3, 40.00, 2.00, 2.00, 1.00, 1),
(2, 'INS2045', 'Database Systems',                      3, '2025-2', 'B1.03', 'Thứ 6, 13:00-15:00',     4, 35.00, 2.00, 3.00, 1.00, 1),
(3, 'INS1010', 'Introduction to Programming',           3, '2025-2', 'C3.05', 'Thứ 3 & 5, 09:30-11:30', 3, 40.00, 2.00, 2.00, 1.00, 1);

-- ════════════════════════════════════════════
-- 3. ENROLLMENTS
-- INS3064 : teacher=2,  students=4..11  (8 sinh viên)
-- INS2045 : teacher=3,  students=4..8   (5 sinh viên)
-- INS1010 : teacher=2,  students=6,9,10,11
-- ════════════════════════════════════════════
INSERT INTO `enrollments` (`course_id`, `user_id`, `role`) VALUES
(1, 2,  'teacher'),
(1, 4,  'student'), (1, 5,  'student'), (1, 6,  'student'), (1, 7,  'student'),
(1, 8,  'student'), (1, 9,  'student'), (1, 10, 'student'), (1, 11, 'student'),
(2, 3,  'teacher'),
(2, 4,  'student'), (2, 5,  'student'), (2, 6,  'student'), (2, 7,  'student'), (2, 8, 'student'),
(3, 2,  'teacher'),
(3, 6,  'student'), (3, 9,  'student'), (3, 10, 'student'), (3, 11, 'student');

-- ════════════════════════════════════════════
-- 4. CLASS SESSIONS
-- id=4  (INS3064) : ACTIVE – dùng CURDATE(), thời gian 00:00:00-23:59:59 để không fail lệch múi giờ demo
-- id=8  (INS2045) : ACTIVE – tương tự
-- Còn lại: ended hoặc upcoming
-- ════════════════════════════════════════════
INSERT INTO `class_sessions`
    (`id`, `course_id`, `teacher_id`, `session_date`, `start_time`, `end_time`, `title`, `location`, `status`)
VALUES
-- INS3064
(1,  1, 2, '2025-05-01', '07:30:00', '09:30:00', 'Buổi 1 – HTML & CSS cơ bản',             'A2.01', 'ended'),
(2,  1, 2, '2025-05-08', '07:30:00', '09:30:00', 'Buổi 2 – JavaScript & DOM',              'A2.01', 'ended'),
(3,  1, 2, '2025-05-15', '07:30:00', '09:30:00', 'Buổi 3 – PHP căn bản',                   'A2.01', 'ended'),
-- ACTIVE session INS3064 (00:00:00-23:59:59 = luôn trong giờ học khi demo)
(4,  1, 2, CURDATE(),    '00:00:00', '23:59:59', 'Buổi 4 – MySQL & PDO (Đang diễn ra)',     'A2.01', 'active'),
(5,  1, 2, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '07:30:00', '09:30:00', 'Buổi 5 – Framework Laravel', 'A2.01', 'upcoming'),
-- INS2045
(6,  2, 3, '2025-05-02', '13:00:00', '15:00:00', 'Buổi 1 – ER Diagram',                    'B1.03', 'ended'),
(7,  2, 3, '2025-05-09', '13:00:00', '15:00:00', 'Buổi 2 – Normalization 3NF',             'B1.03', 'ended'),
-- ACTIVE session INS2045
(8,  2, 3, CURDATE(),    '00:00:00', '23:59:59', 'Buổi 3 – SQL Queries nâng cao (Đang diễn ra)', 'B1.03', 'active'),
(9,  2, 3, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '13:00:00', '15:00:00', 'Buổi 4 – Stored Procedures', 'B1.03', 'upcoming'),
-- INS1010
(10, 3, 2, '2025-05-03', '09:30:00', '11:30:00', 'Buổi 1 – Giới thiệu lập trình',          'C3.05', 'ended'),
(11, 3, 2, '2025-05-10', '09:30:00', '11:30:00', 'Buổi 2 – Biến và kiểu dữ liệu',          'C3.05', 'ended');

-- ════════════════════════════════════════════
-- 5. ATTENDANCE METHODS
-- id=5,6  : gắn session ACTIVE id=4 (INS3064) – is_active=1, expires_at=NOW()+2h
-- id=9,10 : gắn session ACTIVE id=8 (INS2045) – is_active=1, expires_at=NOW()+2h
-- Còn lại : is_active=0, ngày cố định quá khứ
-- ════════════════════════════════════════════
INSERT INTO `attendance_methods`
    (`id`, `session_id`, `method_type`, `token`, `expires_at`, `is_active`)
VALUES
-- Ended sessions
(1,  1,  'qr',     'QR_S1_A1B2C3D4E5F6',      '2025-05-01 09:45:00',              0),
(2,  2,  'otp',    '847261',                   '2025-05-08 08:00:00',              0),
(3,  3,  'qr',     'QR_S3_G7H8I9J0K1L2',      '2025-05-15 09:45:00',              0),
(4,  3,  'manual', NULL,                        NULL,                               0),
-- ACTIVE session id=4 (INS3064) – expires_at = NOW() + 2 giờ, luôn còn hiệu lực khi demo
(5,  4,  'qr',     'QR_S4_DEMO_ACTIVE_QR',    DATE_ADD(NOW(), INTERVAL 1 DAY),   1),
(6,  4,  'otp',    '362819',                   DATE_ADD(NOW(), INTERVAL 1 DAY),   1),
-- Ended INS2045
(7,  6,  'otp',    '193847',                   '2025-05-02 14:30:00',              0),
(8,  7,  'qr',     'QR_S7_S9T0U1V2W3X4',      '2025-05-09 15:45:00',              0),
-- ACTIVE session id=8 (INS2045)
(9,  8,  'qr',     'QR_S8_DEMO_ACTIVE_QR',    DATE_ADD(NOW(), INTERVAL 1 DAY),   1),
(10, 8,  'otp',    '748293',                   DATE_ADD(NOW(), INTERVAL 1 DAY),   1),
-- Ended INS1010
(11, 10, 'qr',     'QR_S10_E1F2G3H4I5J6',     '2025-05-03 11:15:00',              0),
(12, 11, 'otp',    '501923',                   '2025-05-10 10:00:00',              0);

-- ════════════════════════════════════════════
-- 6. ATTENDANCE RECORDS
-- Sessions ended: insert đầy đủ
-- Session ACTIVE id=4 (INS3064): chỉ insert một phần (students 4,5,6,7)
--   → students 8,9,10,11 KHÔNG có record → có thể check attendance thật khi demo
-- Session ACTIVE id=8 (INS2045): chỉ insert một phần (student 4)
--   → students 5,6,7,8 KHÔNG có record → có thể check attendance thật khi demo
-- ════════════════════════════════════════════
INSERT INTO `attendance_records`
    (`session_id`, `student_id`, `method_id`, `status`, `checked_in_at`)
VALUES
-- INS3064 – Buổi 1 (ended, session=1)
(1, 4,  1, 'present', '2025-05-01 07:35:00'),
(1, 5,  1, 'present', '2025-05-01 07:40:00'),
(1, 6,  1, 'absent',  NULL),
(1, 7,  1, 'present', '2025-05-01 07:38:00'),
(1, 8,  1, 'late',    '2025-05-01 08:10:00'),
(1, 9,  1, 'present', '2025-05-01 07:32:00'),
(1, 10, 1, 'present', '2025-05-01 07:36:00'),
(1, 11, 1, 'excused', NULL),
-- INS3064 – Buổi 2 (ended, session=2)
(2, 4,  2, 'present', '2025-05-08 07:36:00'),
(2, 5,  2, 'absent',  NULL),
(2, 6,  2, 'absent',  NULL),
(2, 7,  2, 'present', '2025-05-08 07:40:00'),
(2, 8,  2, 'present', '2025-05-08 07:35:00'),
(2, 9,  2, 'excused', NULL),
(2, 10, 2, 'present', '2025-05-08 07:33:00'),
(2, 11, 2, 'late',    '2025-05-08 08:05:00'),
-- INS3064 – Buổi 3 (ended, session=3)
(3, 4,  3, 'present', '2025-05-15 07:34:00'),
(3, 5,  3, 'absent',  NULL),
(3, 6,  3, 'absent',  NULL),
(3, 7,  3, 'present', '2025-05-15 07:39:00'),
(3, 8,  3, 'present', '2025-05-15 07:37:00'),
(3, 9,  3, 'present', '2025-05-15 07:31:00'),
(3, 10, 3, 'late',    '2025-05-15 08:00:00'),
(3, 11, 3, 'present', '2025-05-15 07:38:00'),
-- INS3064 – Buổi 4 ACTIVE (session=4): chỉ 4 student, còn 8,9,10,11 trống để demo
(4, 4,  5, 'present', NOW()),
(4, 5,  NULL, 'absent',  NULL),
(4, 6,  NULL, 'absent',  NULL),
(4, 7,  6, 'late',    NOW()),
-- students 8,9,10,11 trong session 4 KHÔNG được insert → để demo check-in thật
-- INS2045 – Buổi 1 (ended, session=6)
(6, 4,  7, 'present', '2025-05-02 13:05:00'),
(6, 5,  7, 'present', '2025-05-02 13:02:00'),
(6, 6,  7, 'absent',  NULL),
(6, 7,  7, 'present', '2025-05-02 13:10:00'),
(6, 8,  7, 'late',    '2025-05-02 13:35:00'),
-- INS2045 – Buổi 2 (ended, session=7)
(7, 4,  8, 'present', '2025-05-09 13:08:00'),
(7, 5,  8, 'late',    '2025-05-09 13:45:00'),
(7, 6,  8, 'absent',  NULL),
(7, 7,  8, 'present', '2025-05-09 13:04:00'),
(7, 8,  8, 'present', '2025-05-09 13:06:00'),
-- INS2045 – Buổi 3 ACTIVE (session=8): chỉ student 4, còn 5,6,7,8 trống để demo
(8, 4,  9, 'present', NOW()),
-- students 5,6,7,8 trong session 8 KHÔNG được insert → để demo check-in thật
-- INS1010 – Buổi 1 (ended, session=10)
(10, 6,  11, 'absent',  NULL),
(10, 9,  11, 'present', '2025-05-03 09:35:00'),
(10, 10, 11, 'present', '2025-05-03 09:32:00'),
(10, 11, 11, 'late',    '2025-05-03 10:00:00'),
-- INS1010 – Buổi 2 (ended, session=11)
(11, 6,  12, 'absent',  NULL),
(11, 9,  12, 'present', '2025-05-10 09:33:00'),
(11, 10, 12, 'present', '2025-05-10 09:30:00'),
(11, 11, 12, 'present', '2025-05-10 09:38:00');

-- ════════════════════════════════════════════
-- 7. QUIZ SESSIONS
-- id=6 : open, gắn session ACTIVE id=4 (INS3064) → để demo submit quiz
-- id=7 : open, gắn session ACTIVE id=8 (INS2045) → để demo submit quiz
-- ════════════════════════════════════════════
INSERT INTO `quiz_sessions`
    (`id`, `session_id`, `title`, `description`, `time_limit_minutes`, `status`, `allow_retake`, `questions`)
VALUES
(1, 1,  'Quiz HTML & CSS',      'Kiểm tra kiến thức buổi 1',           10, 'closed', 0, '[{"id":"1","text":"Thẻ nào dùng để in đậm chữ trong HTML?","options":{"A":"<b>","B":"<i>","C":"<u>","D":"<p>"},"correct":"A"},{"id":"2","text":"Thuộc tính CSS nào đổi màu nền?","options":{"A":"color","B":"background-color","C":"bg-color","D":"font-color"},"correct":"B"}]'),
(2, 2,  'Quiz JavaScript',      'Kiểm tra DOM manipulation',            15, 'closed', 0, '[{"id":"3","text":"Hàm nào dùng để tìm thẻ theo ID?","options":{"A":"getElementById","B":"querySelector","C":"Cả A và B đều đúng","D":"getElementsByTagName"},"correct":"C"},{"id":"4","text":"Sự kiện click chuột là?","options":{"A":"onchange","B":"onhover","C":"onclick","D":"onsubmit"},"correct":"C"}]'),
(3, 3,  'Quiz PHP Căn bản',     'Kiểm tra kiến thức PHP cơ bản',       10, 'closed', 0, '[{"id":"5","text":"Ký hiệu nào khai báo biến trong PHP?","options":{"A":"@","B":"&","C":"$","D":"#"},"correct":"C"},{"id":"6","text":"Hàm nào in dữ liệu ra màn hình trong PHP?","options":{"A":"print_r()","B":"var_dump()","C":"echo","D":"Tất cả đều đúng"},"correct":"D"}]'),
(4, 6,  'Quiz ER Diagram',      'Ký hiệu & quan hệ trong ER',          10, 'closed', 0, '[{"id":"7","text":"Hình thoi trong ERD biểu diễn gì?","options":{"A":"Thực thể","B":"Mối quan hệ","C":"Thuộc tính","D":"Khóa chính"},"correct":"B"},{"id":"8","text":"Khóa chính dùng để làm gì?","options":{"A":"Phân biệt các dòng dữ liệu","B":"Tăng tốc độ truy vấn","C":"Liên kết bảng","D":"Lọc dữ liệu"},"correct":"A"}]'),
(5, 7,  'Quiz Normalization',   'Chuẩn 1NF, 2NF, 3NF',                12, 'closed', 0, '[{"id":"9","text":"Chuẩn 1NF yêu cầu gì?","options":{"A":"Không có nhóm lặp","B":"Khóa chính duy nhất","C":"Giá trị nguyên tử","D":"Tất cả đều đúng"},"correct":"C"},{"id":"10","text":"Chuẩn 2NF giải quyết vấn đề gì?","options":{"A":"Phụ thuộc một phần","B":"Phụ thuộc bắc cầu","C":"Dữ liệu lặp","D":"Khóa ngoại"},"correct":"A"},{"id":"11","text":"Đặc điểm của 3NF là gì?","options":{"A":"Không phụ thuộc bắc cầu","B":"Khóa ngoại bắt buộc","C":"Không có NULL","D":"Dữ liệu đã chuẩn hóa hoàn toàn"},"correct":"A"}]'),
-- OPEN – để demo: sinh viên có thể vào submit quiz thật
(6, 4,  'Quiz MySQL & PDO',     'Truy vấn SQL nâng cao và PDO',        15, 'open',   0, '[{"id":"14","text":"PDO là viết tắt của?","options":{"A":"PHP Data Objects","B":"PHP Database Output","C":"Personal Data Object","D":"Public Data Oriented"},"correct":"A"},{"id":"15","text":"Hàm nào chuẩn bị một câu lệnh PDO?","options":{"A":"prepare()","B":"query()","C":"execute()","D":"bind()"},"correct":"A"}]'),
(7, 8,  'Quiz SQL Queries',     'JOIN, subquery, aggregate functions',  15, 'open',   0, '[{"id":"16","text":"Lệnh JOIN nào lấy tất cả dữ liệu từ bảng bên trái?","options":{"A":"INNER JOIN","B":"LEFT JOIN","C":"RIGHT JOIN","D":"FULL JOIN"},"correct":"B"},{"id":"17","text":"Hàm nào tính tổng các giá trị?","options":{"A":"COUNT()","B":"SUM()","C":"AVG()","D":"MAX()"},"correct":"B"}]'),
(8, 10, 'Quiz Giới thiệu LP',   'Khái niệm cơ bản lập trình',          8,  'closed', 0, '[{"id":"12","text":"Thuật toán là gì?","options":{"A":"Các bước giải quyết bài toán","B":"Một ngôn ngữ lập trình","C":"Hệ điều hành","D":"Một dạng dữ liệu"},"correct":"A"},{"id":"13","text":"Cấu trúc lặp nào không kiểm tra điều kiện trước khi chạy lần đầu?","options":{"A":"while","B":"do...while","C":"for","D":"foreach"},"correct":"B"}]');

-- ════════════════════════════════════════════
-- 8. QUIZ SUBMISSIONS
-- Quizzes 1,2,3,4,5,8: insert đầy đủ (đã closed)
-- Quiz 6 (open, session active INS3064): KHÔNG insert → sinh viên submit thật khi demo
-- Quiz 7 (open, session active INS2045): KHÔNG insert → sinh viên submit thật khi demo
-- ════════════════════════════════════════════
INSERT INTO `quiz_submissions`
    (`quiz_id`, `student_id`, `total_score`, `max_score`, `answers`, `submitted_at`)
VALUES
-- Quiz 1 – HTML & CSS (closed)
(1, 4,  2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:30:00'),
(1, 5,  1.00, 2.00, '{"1":"A","2":"A"}', '2025-05-01 08:32:00'),
(1, 7,  2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:28:00'),
(1, 8,  1.00, 2.00, '{"1":"B","2":"B"}', '2025-05-01 08:35:00'),
(1, 9,  2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:27:00'),
(1, 10, 2.00, 2.00, '{"1":"A","2":"B"}', '2025-05-01 08:31:00'),
-- Quiz 2 – JavaScript (closed)
(2, 4,  2.00, 2.00, '{"3":"B","4":"C"}', '2025-05-08 08:45:00'),
(2, 7,  1.00, 2.00, '{"3":"B","4":"A"}', '2025-05-08 08:50:00'),
(2, 8,  2.00, 2.00, '{"3":"B","4":"C"}', '2025-05-08 08:43:00'),
(2, 10, 2.00, 2.00, '{"3":"B","4":"C"}', '2025-05-08 08:44:00'),
-- Quiz 3 – PHP (closed)
(3, 4,  2.00, 2.00, '{"5":"C","6":"D"}', '2025-05-15 08:20:00'),
(3, 7,  2.00, 2.00, '{"5":"C","6":"D"}', '2025-05-15 08:22:00'),
(3, 8,  1.00, 2.00, '{"5":"C","6":"B"}', '2025-05-15 08:25:00'),
(3, 9,  2.00, 2.00, '{"5":"C","6":"D"}', '2025-05-15 08:18:00'),
(3, 10, 1.00, 2.00, '{"5":"A","6":"D"}', '2025-05-15 08:30:00'),
(3, 11, 2.00, 2.00, '{"5":"C","6":"D"}', '2025-05-15 08:21:00'),
-- Quiz 4 – ER Diagram (closed)
(4, 4,  2.00, 2.00, '{"7":"B","8":"A"}', '2025-05-02 14:00:00'),
(4, 5,  2.00, 2.00, '{"7":"B","8":"A"}', '2025-05-02 14:05:00'),
(4, 7,  1.00, 2.00, '{"7":"A","8":"A"}', '2025-05-02 14:10:00'),
(4, 8,  2.00, 2.00, '{"7":"B","8":"A"}', '2025-05-02 14:08:00'),
-- Quiz 5 – Normalization (closed)
(5, 4,  3.00, 3.00, '{"9":"C","10":"B","11":"A"}', '2025-05-09 14:15:00'),
(5, 5,  2.00, 3.00, '{"9":"C","10":"A","11":"A"}', '2025-05-09 14:20:00'),
(5, 7,  3.00, 3.00, '{"9":"C","10":"B","11":"A"}', '2025-05-09 14:12:00'),
(5, 8,  1.00, 3.00, '{"9":"A","10":"B","11":"C"}', '2025-05-09 14:25:00'),
-- Quiz 8 – Giới thiệu LP (closed)
(8, 9,  2.00, 2.00, '{"12":"A","13":"B"}', '2025-05-03 10:30:00'),
(8, 10, 2.00, 2.00, '{"12":"A","13":"B"}', '2025-05-03 10:28:00'),
(8, 11, 1.00, 2.00, '{"12":"B","13":"B"}', '2025-05-03 10:35:00');
-- Quiz 6 & Quiz 7 KHÔNG INSERT → để sinh viên submit thật khi demo

-- ════════════════════════════════════════════
-- 9. INTERACTION LOGS
-- Chỉ ghi log cho các session đã ended; session active (id=4,8) sẽ sinh log thật khi demo
-- ════════════════════════════════════════════
INSERT INTO `interaction_logs`
    (`user_id`, `session_id`, `action_type`, `reference_id`, `description`, `points_earned`)
VALUES
-- Buổi 1 INS3064 (session=1)
(4,  1, 'check_in',    1,  'Check-in qua QR',              2.00),
(5,  1, 'check_in',    2,  'Check-in qua QR',              2.00),
(7,  1, 'check_in',    4,  'Check-in qua QR',              2.00),
(8,  1, 'check_in',    5,  'Check-in muộn',                1.00),
(9,  1, 'check_in',    6,  'Check-in qua QR',              2.00),
(10, 1, 'check_in',    7,  'Check-in qua QR',              2.00),
(4,  1, 'submit_quiz', 1,  'Quiz 1 – 2/2 điểm',            2.00),
(5,  1, 'submit_quiz', 2,  'Quiz 1 – 1/2 điểm',            1.00),
(7,  1, 'submit_quiz', 3,  'Quiz 1 – 2/2 điểm',            2.00),
(8,  1, 'submit_quiz', 4,  'Quiz 1 – 1/2 điểm',            1.00),
(9,  1, 'submit_quiz', 5,  'Quiz 1 – 2/2 điểm',            2.00),
(10, 1, 'submit_quiz', 6,  'Quiz 1 – 2/2 điểm',            2.00),
(4,  1, 'discussion',  NULL, 'Phát biểu về cấu trúc HTML', 1.00),
(7,  1, 'discussion',  NULL, 'Hỏi về flexbox',              1.00),
-- Buổi 2 INS3064 (session=2)
(4,  2, 'check_in',    9,  'Check-in qua OTP',             2.00),
(7,  2, 'check_in',    12, 'Check-in qua OTP',             2.00),
(8,  2, 'check_in',    13, 'Check-in qua OTP',             2.00),
(10, 2, 'check_in',    15, 'Check-in qua OTP',             2.00),
(11, 2, 'check_in',    16, 'Check-in muộn',                1.00),
(4,  2, 'submit_quiz', 7,  'Quiz 2 – 2/2 điểm',            2.00),
(7,  2, 'submit_quiz', 8,  'Quiz 2 – 1/2 điểm',            1.00),
(8,  2, 'submit_quiz', 9,  'Quiz 2 – 2/2 điểm',            2.00),
(10, 2, 'submit_quiz', 10, 'Quiz 2 – 2/2 điểm',            2.00),
-- Buổi 3 INS3064 (session=3)
(4,  3, 'check_in',    17, 'Check-in qua QR',              2.00),
(7,  3, 'check_in',    20, 'Check-in qua QR',              2.00),
(8,  3, 'check_in',    21, 'Check-in qua QR',              2.00),
(9,  3, 'check_in',    22, 'Check-in qua QR',              2.00),
(10, 3, 'check_in',    23, 'Check-in muộn',                1.00),
(11, 3, 'check_in',    24, 'Check-in qua QR',              2.00),
(4,  3, 'submit_quiz', 11, 'Quiz 3 – 2/2 điểm',            2.00),
(7,  3, 'submit_quiz', 12, 'Quiz 3 – 2/2 điểm',            2.00),
(8,  3, 'submit_quiz', 13, 'Quiz 3 – 1/2 điểm',            1.00),
(9,  3, 'submit_quiz', 14, 'Quiz 3 – 2/2 điểm',            2.00),
(10, 3, 'submit_quiz', 15, 'Quiz 3 – 1/2 điểm',            1.00),
(11, 3, 'submit_quiz', 16, 'Quiz 3 – 2/2 điểm',            2.00),
-- Buổi 1 INS2045 (session=6)
(4,  6, 'check_in',    25, 'Check-in qua OTP',             2.00),
(5,  6, 'check_in',    26, 'Check-in qua OTP',             2.00),
(7,  6, 'check_in',    28, 'Check-in qua OTP',             2.00),
(8,  6, 'check_in',    29, 'Check-in muộn',                1.00),
(4,  6, 'submit_quiz', 17, 'Quiz 4 – 2/2 điểm',            2.00),
(5,  6, 'submit_quiz', 18, 'Quiz 4 – 2/2 điểm',            2.00),
(7,  6, 'submit_quiz', 19, 'Quiz 4 – 1/2 điểm',            1.00),
(8,  6, 'submit_quiz', 20, 'Quiz 4 – 2/2 điểm',            2.00),
-- Buổi 2 INS2045 (session=7)
(4,  7, 'check_in',    30, 'Check-in qua QR',              2.00),
(5,  7, 'check_in',    31, 'Check-in muộn',                1.00),
(7,  7, 'check_in',    33, 'Check-in qua QR',              2.00),
(8,  7, 'check_in',    34, 'Check-in qua QR',              2.00),
(4,  7, 'submit_quiz', 21, 'Quiz 5 – 3/3 điểm',            3.00),
(5,  7, 'submit_quiz', 22, 'Quiz 5 – 2/3 điểm',            2.00),
(7,  7, 'submit_quiz', 23, 'Quiz 5 – 3/3 điểm',            3.00),
(8,  7, 'submit_quiz', 24, 'Quiz 5 – 1/3 điểm',            1.00),
-- Buổi 1 INS1010 (session=10)
(9,  10, 'check_in',   36, 'Check-in qua QR',              2.00),
(10, 10, 'check_in',   37, 'Check-in qua QR',              2.00),
(11, 10, 'check_in',   38, 'Check-in muộn',                1.00),
(9,  10, 'submit_quiz',25, 'Quiz 8 – 2/2 điểm',            2.00),
(10, 10, 'submit_quiz',26, 'Quiz 8 – 2/2 điểm',            2.00),
(11, 10, 'submit_quiz',27, 'Quiz 8 – 1/2 điểm',            1.00),
-- Buổi 2 INS1010 (session=11)
(9,  11, 'check_in',   40, 'Check-in qua OTP',             2.00),
(10, 11, 'check_in',   41, 'Check-in qua OTP',             2.00),
(11, 11, 'check_in',   42, 'Check-in qua OTP',             2.00);

-- ════════════════════════════════════════════
-- 10. ENGAGEMENT RULES
-- Trọng số tính engagement_index, ghi đè giá trị mặc định trong courses.
-- ════════════════════════════════════════════
INSERT INTO `engagement_rules`
    (`course_id`, `rule_name`, `rule_key`, `rule_value`, `description`, `is_active`)
VALUES
-- INS3064: attendance 50% + quiz 30% + interaction 20%
(1, 'Trọng số chuyên cần',   'attendance_weight',  50.00, 'Tỉ lệ % chuyên cần trong engagement_index',    1),
(1, 'Trọng số quiz',         'quiz_weight',         30.00, 'Tỉ lệ % quiz trong engagement_index',          1),
(1, 'Trọng số tương tác',    'interaction_weight',  20.00, 'Tỉ lệ % tương tác trong engagement_index',     1),
(1, 'Điểm thưởng thảo luận', 'discussion_bonus',     1.00, 'Điểm cộng mỗi lần phát biểu/thảo luận',       1),
-- INS2045: attendance 45% + quiz 40% + interaction 15%
(2, 'Trọng số chuyên cần',   'attendance_weight',  45.00, 'Tỉ lệ % chuyên cần trong engagement_index',    1),
(2, 'Trọng số quiz',         'quiz_weight',         40.00, 'Tỉ lệ % quiz trong engagement_index',          1),
(2, 'Trọng số tương tác',    'interaction_weight',  15.00, 'Tỉ lệ % tương tác trong engagement_index',     1),
-- INS1010: attendance 50% + quiz 35% + interaction 15%
(3, 'Trọng số chuyên cần',   'attendance_weight',  50.00, 'Tỉ lệ % chuyên cần trong engagement_index',    1),
(3, 'Trọng số quiz',         'quiz_weight',         35.00, 'Tỉ lệ % quiz trong engagement_index',          1),
(3, 'Trọng số tương tác',    'interaction_weight',  15.00, 'Tỉ lệ % tương tác trong engagement_index',     1);

-- ════════════════════════════════════════════
-- 11. ENGAGEMENT SCORES
-- Phản ánh dữ liệu từ các buổi đã ended; active sessions sẽ cập nhật khi demo
-- engagement_index < courses.low_engagement_threshold → alert low_engagement
-- ════════════════════════════════════════════
INSERT INTO `engagement_scores`
    (`student_id`, `course_id`, `total_sessions`, `attended_sessions`, `late_sessions`,
     `absent_sessions`, `total_quiz_score`, `total_interaction_points`,
     `engagement_index`, `last_activity_at`)
VALUES
-- INS3064 (3 buổi ended)
(4,  1, 3, 3, 0, 0,  6.00, 15.00, 92.00, '2025-05-15 08:20:00'),  -- đều đặn, quiz tốt
(5,  1, 3, 1, 0, 2,  1.00,  3.00, 22.00, '2025-05-01 08:32:00'),  -- vắng 2 buổi → alert
(6,  1, 3, 0, 0, 3,  0.00,  0.00,  0.00, NULL),                   -- vắng cả 3 → alert HIGH
(7,  1, 3, 3, 0, 0,  5.00, 12.00, 85.00, '2025-05-15 08:22:00'),
(8,  1, 3, 3, 1, 0,  4.00, 10.00, 78.00, '2025-05-15 08:25:00'),
(9,  1, 3, 2, 0, 0,  4.00,  8.00, 68.00, '2025-05-15 08:18:00'),
(10, 1, 3, 2, 1, 0,  5.00, 11.00, 72.00, '2025-05-15 08:30:00'),
(11, 1, 3, 2, 1, 0,  2.00,  5.00, 55.00, '2025-05-15 08:21:00'),
-- INS2045 (2 buổi ended)
(4,  2, 2, 2, 0, 0,  5.00, 11.00, 88.00, '2025-05-09 14:15:00'),
(5,  2, 2, 2, 1, 0,  4.00,  7.00, 72.00, '2025-05-09 14:20:00'),
(6,  2, 2, 0, 0, 2,  0.00,  0.00,  0.00, NULL),                   -- vắng cả 2 → alert HIGH
(7,  2, 2, 2, 0, 0,  4.00, 10.00, 82.00, '2025-05-09 14:12:00'),
(8,  2, 2, 2, 1, 0,  3.00,  7.00, 68.00, '2025-05-09 14:25:00'),
-- INS1010 (2 buổi ended)
(6,  3, 2, 0, 0, 2,  0.00,  0.00,  0.00, NULL),                   -- vắng cả 2 → alert HIGH
(9,  3, 2, 2, 0, 0,  2.00,  8.00, 82.00, '2025-05-10 09:33:00'),
(10, 3, 2, 2, 0, 0,  2.00,  8.00, 85.00, '2025-05-10 09:30:00'),
(11, 3, 2, 2, 1, 0,  1.00,  5.00, 62.00, '2025-05-10 09:38:00');

-- ════════════════════════════════════════════
-- 12. ALERTS
-- Một số alert dùng NOW() / DATE_SUB(NOW()) để hiện trên dashboard "today/recent"
-- Đủ 3 severity: low · medium · high
-- Đủ 3 status : pending · reviewed · resolved
-- ════════════════════════════════════════════
INSERT INTO `alerts`
    (`alert_id`, `student_id`, `course_id`, `alert_type`, `message`,
     `severity`, `status`, `created_at`, `reviewed_by`, `reviewed_at`)
VALUES

-- [HIGH / PENDING / TODAY] Em vắng cả 3 buổi INS3064
(1, 6, 1, 'low_attendance',
 'Sinh viên Hoàng Văn Em (SV2021003) đã vắng mặt 3/3 buổi học môn INS3064. Vượt ngưỡng cho phép (3 buổi). Đề nghị liên hệ sinh viên ngay.',
 'high', 'pending', NOW(), NULL, NULL),

-- [HIGH / PENDING / TODAY] Em engagement = 0 trong INS3064
(2, 6, 1, 'low_engagement',
 'Sinh viên Hoàng Văn Em (SV2021003) có chỉ số tương tác 0.00/100 trong môn INS3064, thấp hơn ngưỡng 40. Không có hoạt động nào được ghi nhận trong 3 buổi học.',
 'high', 'pending', NOW(), NULL, NULL),

-- [MEDIUM / REVIEWED / HÔM QUA] Dung vắng 2/3 buổi INS3064
(3, 5, 1, 'low_attendance',
 'Sinh viên Phạm Thị Dung (SV2021002) đã vắng mặt 2/3 buổi học môn INS3064. Đang tiệm cận ngưỡng cảnh báo. Đề nghị giảng viên nhắc nhở.',
 'medium', 'reviewed', DATE_SUB(NOW(), INTERVAL 1 DAY), 2, NOW()),

-- [MEDIUM / REVIEWED / HÔM QUA] Dung engagement thấp INS3064
(4, 5, 1, 'low_engagement',
 'Sinh viên Phạm Thị Dung (SV2021002) có chỉ số tương tác 22.00/100 trong môn INS3064, thấp hơn ngưỡng 40. Tổng điểm quiz: 1/6.',
 'medium', 'reviewed', DATE_SUB(NOW(), INTERVAL 1 DAY), 2, NOW()),

-- [HIGH / PENDING / TODAY] Em vắng cả 2 buổi INS2045
(5, 6, 2, 'low_attendance',
 'Sinh viên Hoàng Văn Em (SV2021003) đã vắng mặt 2/2 buổi học môn INS2045. Cần can thiệp ngay từ phòng đào tạo.',
 'high', 'pending', NOW(), NULL, NULL),

-- [HIGH / RESOLVED] Em engagement = 0 INS2045 → đã xử lý
(6, 6, 2, 'low_engagement',
 'Sinh viên Hoàng Văn Em (SV2021003) có chỉ số tương tác 0.00/100 trong môn INS2045. Không nộp bất kỳ quiz nào.',
 'high', 'resolved', DATE_SUB(NOW(), INTERVAL 2 DAY), 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),

-- [HIGH / PENDING / TODAY] Em vắng cả 2 buổi INS1010
(7, 6, 3, 'low_attendance',
 'Sinh viên Hoàng Văn Em (SV2021003) đã vắng mặt 2/2 buổi học môn INS1010. Tình trạng nghỉ học nghiêm trọng ở nhiều môn.',
 'high', 'pending', NOW(), NULL, NULL),

-- [LOW / REVIEWED] Giang quiz yếu INS2045
(8, 8, 2, 'low_engagement',
 'Sinh viên Vũ Đức Giang (SV2021005) có điểm quiz INS2045 chỉ đạt 3/5 điểm. Chỉ số tương tác 68.00/100, cần cải thiện.',
 'low', 'reviewed', DATE_SUB(NOW(), INTERVAL 3 DAY), 3, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- [LOW / PENDING / HÔM QUA] Lan Anh engagement cận ngưỡng INS3064
(9, 11, 1, 'low_engagement',
 'Sinh viên Trịnh Lan Anh (SV2021008) có chỉ số tương tác 55.00/100 trong môn INS3064, tiệm cận ngưỡng thấp. Điểm quiz chỉ đạt 2/6.',
 'low', 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;

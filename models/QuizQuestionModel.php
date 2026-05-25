<?php
// ============================================================
// models/QuizQuestionModel.php
// Quản lý câu hỏi trắc nghiệm trong quiz
// Repository Pattern – CRUD + ordering
// ============================================================

require_once APP_ROOT . '/config/Database.php';

class QuizQuestionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ──────────────────────────────────────────────────────
    // CREATE – Tạo câu hỏi quiz
    // ──────────────────────────────────────────────────────
    public function create(
        int $quizId,
        string $questionText,
        string $optionA,
        string $optionB,
        ?string $optionC = null,
        ?string $optionD = null,
        string $correctOption = 'A',
        float $points = 1.0,
        int $orderNum = 1
    ): int {
        $stmt = $this->db->prepare(
            'INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, order_num)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $quizId,
            $questionText,
            $optionA,
            $optionB,
            $optionC,
            $optionD,
            $correctOption,
            $points,
            $orderNum
        ]);
        return (int) $this->db->lastInsertId();
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy 1 câu hỏi theo ID
    // ──────────────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, order_num, created_at
             FROM quiz_questions WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // ──────────────────────────────────────────────────────
    // READ – Lấy tất cả câu hỏi theo quiz (sắp xếp theo thứ tự)
    // ──────────────────────────────────────────────────────
    public function getByQuizId(int $quizId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, points, order_num, created_at
             FROM quiz_questions WHERE quiz_id = ? ORDER BY order_num ASC'
        );
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────
    // UPDATE – Cập nhật câu hỏi
    // ──────────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $allowedFields = ['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'points', 'order_num'];
        $updates = [];
        $values = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $id;
        $sql = 'UPDATE quiz_questions SET ' . implode(', ', $updates) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    // ──────────────────────────────────────────────────────
    // DELETE – Xóa câu hỏi
    // ──────────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM quiz_questions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Reorder questions (khi xóa hoặc di chuyển)
    // ──────────────────────────────────────────────────────
    public function reorderByQuizId(int $quizId): bool
    {
        // Lấy tất cả câu hỏi, sắp xếp theo order_num, sau đó reindex
        $questions = $this->getByQuizId($quizId);
        
        if (empty($questions)) {
            return true;
        }

        $newOrder = 1;
        foreach ($questions as $q) {
            $stmt = $this->db->prepare('UPDATE quiz_questions SET order_num = ? WHERE id = ?');
            $stmt->execute([$newOrder, $q['id']]);
            $newOrder++;
        }
        
        return true;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Di chuyển câu hỏi lên/xuống
    // ──────────────────────────────────────────────────────
    public function moveQuestion(int $questionId, string $direction): bool
    {
        $question = $this->getById($questionId);
        if (!$question) {
            return false;
        }

        $quizId = $question['quiz_id'];
        $currentOrder = $question['order_num'];
        
        $allQuestions = $this->getByQuizId($quizId);
        $totalQuestions = count($allQuestions);

        if ($direction === 'up' && $currentOrder > 1) {
            // Swap với câu hỏi trước
            $prevQuestion = null;
            foreach ($allQuestions as $q) {
                if ($q['order_num'] == $currentOrder - 1) {
                    $prevQuestion = $q;
                    break;
                }
            }
            
            if ($prevQuestion) {
                $this->update($questionId, ['order_num' => $currentOrder - 1]);
                $this->update($prevQuestion['id'], ['order_num' => $currentOrder]);
                return true;
            }
        } elseif ($direction === 'down' && $currentOrder < $totalQuestions) {
            // Swap với câu hỏi sau
            $nextQuestion = null;
            foreach ($allQuestions as $q) {
                if ($q['order_num'] == $currentOrder + 1) {
                    $nextQuestion = $q;
                    break;
                }
            }
            
            if ($nextQuestion) {
                $this->update($questionId, ['order_num' => $currentOrder + 1]);
                $this->update($nextQuestion['id'], ['order_num' => $currentOrder]);
                return true;
            }
        }
        
        return false;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Lấy max order_num để tạo câu hỏi tiếp theo
    // ──────────────────────────────────────────────────────
    public function getNextOrderNum(int $quizId): int
    {
        $stmt = $this->db->prepare(
            'SELECT MAX(order_num) as max_order FROM quiz_questions WHERE quiz_id = ?'
        );
        $stmt->execute([$quizId]);
        $result = $stmt->fetch();
        return ($result['max_order'] ?? 0) + 1;
    }

    // ──────────────────────────────────────────────────────
    // HELPER – Validate đáp án đúng
    // ──────────────────────────────────────────────────────
    public function isValidCorrectOption(string $option, int $questionId): bool
    {
        $question = $this->getById($questionId);
        if (!$question) {
            return false;
        }

        // Kiểm tra option có tồn tại không
        $optionField = 'option_' . strtolower($option);
        if ($option === 'C' && is_null($question['option_c'])) {
            return false;
        }
        if ($option === 'D' && is_null($question['option_d'])) {
            return false;
        }

        return in_array($option, ['A', 'B', 'C', 'D']);
    }
}

<?php
// ============================================================
// helpers/N8nEmailService.php
// Dịch vụ gửi Webhook sang n8n để kích hoạt gửi Email cảnh báo
// ============================================================

require_once __DIR__ . '/../config/config.php';

class N8nEmailService
{
    /**
     * Gửi webhook cảnh báo học tập sang n8n
     *
     * @param string $studentEmail
     * @param string $studentName
     * @param string $courseName
     * @param string $alertType 'low_attendance' | 'low_engagement'
     * @param string $message
     * @param string $severity
     * @return bool True nếu gửi cURL thành công (không đồng nghĩa n8n chạy email thành công, chỉ báo là gửi tới webhook ok)
     */
    public static function sendAlertWebhook(
        string $studentEmail,
        string $studentName,
        string $courseName,
        string $alertType,
        string $message,
        string $severity
    ): bool {
        // Kiểm tra xem tính năng gửi n8n có đang được bật trong config.php không
        if (!defined('ENABLE_N8N_EMAILS') || !ENABLE_N8N_EMAILS) {
            return false;
        }

        if (!defined('N8N_WEBHOOK_URL') || empty(N8N_WEBHOOK_URL)) {
            error_log('N8nEmailService Error: N8N_WEBHOOK_URL is not defined or empty.');
            return false;
        }

        $payload = [
            'student_email' => $studentEmail,
            'student_name'  => $studentName,
            'course_name'   => $courseName,
            'alert_type'    => $alertType,
            'alert_message' => $message,
            'severity'      => $severity,
            'timestamp'     => date('c') // ISO 8601 date
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);

        // Khởi tạo cURL
        $ch = curl_init(N8N_WEBHOOK_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonPayload)
        ]);
        
        // Timeout ngắn để tránh việc hệ thống bị treo nếu server n8n phản hồi chậm hoặc sập
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);

        // n8n webhook HTTP methods thường trả về 200 OK
        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        } else {
            error_log("N8nEmailService Webhook Failed. HTTP Code: $httpCode. Error: $curlError");
            return false;
        }
    }
}

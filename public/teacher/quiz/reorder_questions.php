<?php
define("APP_ROOT", dirname(dirname(dirname(__DIR__))));
require_once APP_ROOT . "/config/config.php";
require_once APP_ROOT . "/helpers/middleware.php";
require_once APP_ROOT . "/config/database.php";

Middleware::teacher();

$data = json_decode(file_get_contents("php://input"), true);
$ids = $data["ids"] ?? [];

$db = Database::getInstance()->getConnection();
foreach ($ids as $order => $id) {
    $stmt = $db->prepare("UPDATE quiz_questions SET order_num = ? WHERE id = ?");
    $stmt->execute([$order + 1, $id]);
}

echo json_encode(["success" => true]);

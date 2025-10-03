<?php
// DB 接続
require_once __DIR__ . '/../localhost/db_open.php';

// CORS対応
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = ["http://localhost:3000", "http://127.0.0.1:3000"];
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// データ取得テスト
try {
    $stmt = $pdo->query("SELECT `お知らせID 主` AS id, `内容` AS content, `投稿日時` AS created_at FROM announcements ORDER BY `投稿日時` DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "announcements" => $rows
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "SQLエラー: " . $e->getMessage()]);
}

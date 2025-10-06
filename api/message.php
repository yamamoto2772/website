<?php
// api/message.php
declare(strict_types=1);
session_start();

// CORS設定
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = ['http://localhost', 'http://127.0.0.1', 'http://localhost:3000', 'http://127.0.0.1:3000'];
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// DB接続
require_once __DIR__ . '/../localhost/db_open.php';

// action取得
$action = $_GET['action'] ?? ($_POST['action'] ?? '');


// 🟦 メッセージ一覧取得
if ($action === 'list') {
    $roomId = $_GET['room_id'] ?? $_GET['roomId'] ?? null;
    if (!$roomId) {
        echo json_encode(["success" => false, "error" => "room_id が未指定です"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM message WHERE room_id = ? ORDER BY id ASC");
        $stmt->execute([$roomId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "success" => true,
            "messages" => $messages
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "SQLエラー: " . $e->getMessage()]);
    }
    exit;
}


// 🟩 メッセージ作成
if ($action === 'create') {
    $roomId = $_POST['room_id'] ?? '';
    $content = $_POST['message'] ?? '';
    $workspace_id = $_POST['workspace_id'] ?? null;

    // セッションから送信者識別を取得（未設定なら student 扱い）
    $sender = $_SESSION['role'] ?? 'student';

    if (!$roomId || !$content) {
        echo json_encode(["success" => false, "error" => "必要なデータが未入力です"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO message (room_id, sender_type, content, created_at, workspace_id)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->execute([$roomId, $sender, $content, $workspace_id]);
        $msgId = $pdo->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => [
                "id" => $msgId,
                "room_id" => $roomId,
                "sender_type" => $sender,
                "content" => $content,
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "SQLエラー: " . $e->getMessage()]);
    }
    exit;
}


// 🟥 無効アクション
echo json_encode(["success" => false, "error" => "無効なアクション"]);

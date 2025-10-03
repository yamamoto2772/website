<?php
// api/chat.php

// CORS対応: localhost と 127.0.0.1 の両方を許可
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    "http://localhost:3000",
    "http://127.0.0.1:3000"
];

if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

<<<<<<< HEAD
<<<<<<< HEAD

=======
>>>>>>> 6bdb6c2 (中村1)
=======

>>>>>>> 0aa4cfb (aa)
// OPTIONS プリフライトリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// DB 接続
require_once __DIR__ . '/../localhost/db_open.php';
header("Content-Type: application/json; charset=UTF-8");

// action パラメータ取得
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

// メッセージ一覧取得
if ($action === 'list') {
    $roomId = $_GET['room_id'] ?? '';
    if (!$roomId) {
        echo json_encode(["success" => false, "error" => "room_id が指定されていません"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM message WHERE `チャットルームID` = ? ORDER BY `チャットID` ASC");
        $stmt->execute([$roomId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $converted = array_map(function($m) {
            return [
                "id" => $m['チャットID'],
                "room_id" => $m['チャットルームID'],
                "sender_type" => $m['送信者識別'],
                "content" => $m['メッセージ'],
                "image" => $m['画像'] ?? null,
                "created_at" => $m['作成日時']
            ];
        }, $messages);

        echo json_encode([
            "success" => true,
            "messages" => $converted
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => "SQLエラー: " . $e->getMessage()
        ]);
    }
    exit;
}

// メッセージ送信
if ($action === 'post') {
    $roomId = $_POST['room_id'] ?? '';
    $senderType = $_POST['sender_type'] ?? '';
    $content = $_POST['content'] ?? '';

    if (!$roomId || !$senderType || !$content) {
        echo json_encode(["success" => false, "error" => "room_id, sender_type, content が必須です"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO message (`チャットルームID`, `送信者識別`, `メッセージ`, `作成日時`) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$roomId, $senderType, $content]);

        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => "SQLエラー: " . $e->getMessage()
        ]);
    }
    exit;
}

// 無効なアクション
echo json_encode([
    "success" => false,
    "error" => "無効なアクション"
]);

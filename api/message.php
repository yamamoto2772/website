<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../localhost/db_open.php';
header("Content-Type: application/json; charset=UTF-8");

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $roomId = $_GET['roomId'] ?? '';
    if (!$roomId) {
        echo json_encode(["success" => false, "error" => "roomId が未指定です"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM message WHERE チャットルームID = ? ORDER BY チャットID ASC");
        $stmt->execute([$roomId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $converted = array_map(function($m) {
            return [
                "id" => $m['チャットID'],
                "room_id" => $m['チャットルームID'],
                "sender" => $m['送信者識別'],
                "message" => $m['メッセージ'],
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

if ($action === 'create') {
    $roomId = $_POST['room_id'] ?? '';
    $sender = $_POST['sender'] ?? '';
    $messageText = $_POST['message'] ?? '';
    $image = $_POST['image'] ?? null;

    if (!$roomId || !$sender || !$messageText) {
        echo json_encode(["success" => false, "error" => "必要なデータが未入力です"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO message (チャットルームID, 送信者識別, メッセージ, 画像, 作成日時) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$roomId, $sender, $messageText, $image]);
        $msgId = $pdo->lastInsertId();

        echo json_encode([
            "success" => true,
            "message" => [
                "id" => $msgId,
                "room_id" => $roomId,
                "sender" => $sender,
                "message" => $messageText,
                "image" => $image,
                "created_at" => date("Y-m-d H:i:s")
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => "SQLエラー: " . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode([
    "success" => false,
    "error" => "無効なアクション"
]);

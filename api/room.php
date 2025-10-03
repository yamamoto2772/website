<?php
// api/room.php

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

// ルーム一覧取得
if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM chat_room ORDER BY `ルームID` ASC");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 日本語カラムを英語キーに変換
        $converted = array_map(function($r) {
            return [
                'id' => $r['ルームID'],
                'title' => $r['タイトル'],
                'workspace_id' => $r['ワークスペースID'] ?? null,
                'created_at' => $r['作成日時']
            ];
        }, $rooms);

        echo json_encode([
            "success" => true,
            "rooms" => $converted
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "error" => "SQLエラー: " . $e->getMessage()
        ]);
    }
    exit;
}

// 新規ルーム作成
if ($action === 'create') {
    $title = $_POST['title'] ?? '';
    if (!$title) {
        echo json_encode(["success" => false, "error" => "タイトルが未入力です"]);
        exit;
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO chat_room (タイトル, 作成日時) VALUES (?, NOW())");
        $stmt->execute([$title]);
        $roomId = $pdo->lastInsertId();

        echo json_encode([
            "success" => true,
            "room" => [
                "id" => $roomId,
                "title" => $title
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

// 無効なアクション
echo json_encode([
    "success" => false,
    "error" => "無効なアクション"
]);

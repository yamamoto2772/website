<?php
// api/room.php

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = ["http://localhost:3000", "http://127.0.0.1:3000"];
if (in_array($origin, $allowed_origins, true)) header("Access-Control-Allow-Origin: $origin");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../localhost/db_open.php';
header("Content-Type: application/json; charset=UTF-8");

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

/** ルーム一覧 */
if ($action === 'list') {
  try {
     $stmt = $pdo->query("SELECT room_id, title, workspace_id, created_at FROM chat_room ORDER BY room_id ASC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rooms = array_map(fn($r) => [
      'id'           => (int)$r['room_id'],
      'title'        => $r['title'],
      'workspace_id' => (int)$r['workspace_id'],
      'created_at'   => $r['created_at'],
    ], $rows);

    echo json_encode(['success' => true, 'rooms' => $rooms]);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => "SQLエラー: ".$e->getMessage()]);
  }
  exit;
}

/** 新規ルーム作成 */
if ($action === 'create') {
  $title = trim($_POST['title'] ?? '');
  $workspace_id = isset($_POST['workspace_id']) ? (int)$_POST['workspace_id'] : 0;

  if ($title === '' || $workspace_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'title or workspace_id missing']);
    exit;
  }

  try {
    // 事前に親の存在を確認（FKエラーの前に人間向けに弾く）
    $chk = $pdo->prepare('SELECT 1 FROM workspaces WHERE id = ?');
    $chk->execute([$workspace_id]);
    if (!$chk->fetchColumn()) {
      http_response_code(400);
      echo json_encode(['success' => false, 'error' => 'unknown workspace']);
      exit;
    }

    // INSERT（workspace_id を必ず入れる）
    $ins = $pdo->prepare('INSERT INTO chat_room (title, workspace_id, created_at) VALUES (?, ?, NOW())');
    $ins->execute([$title, $workspace_id]);
    $roomId = (int)$pdo->lastInsertId();

    echo json_encode(['success' => true, 'room' => ['id' => $roomId, 'title' => $title]]);
  } catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => "SQLエラー: ".$e->getMessage()]);
  }
  exit;
}

echo json_encode(['success' => false, 'error' => '無効なアクション']);

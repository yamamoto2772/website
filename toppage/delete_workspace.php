<?php
require_once("../localhost/db_open.php");

if ($_COOKIE['admin'] !== 'true') {
  http_response_code(403);
  echo json_encode(["error" => "許可されていません"]);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id || !is_numeric($id)) {
  http_response_code(400);
  echo json_encode(["error" => "IDが不正です"]);
  exit;
}

try {
  $stmt = $pdo->prepare("DELETE FROM workspaces WHERE id = ?");
  $stmt->execute([$id]);

  echo json_encode(["success" => true]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => "削除に失敗しました"]);
}

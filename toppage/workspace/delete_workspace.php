<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

// 管理者チェック
if (empty($_SESSION['is_admin'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'forbidden']);
  exit;
}

// CSRFチェック
$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'invalid_csrf']);
  exit;
}

require_once("../../localhost/db_open.php");

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

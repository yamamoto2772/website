<?php

declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['is_admin'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'forbidden']);
  exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'invalid_csrf']);
  exit;
}

require_once("../../localhost/db_open.php");

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? null;
$name = $data["name"] ?? null;

if (!$id || !$name) {
  echo json_encode(["success" => false, "error" => "IDまたは名前が無効です"]);
  exit;
}

$stmt = $pdo->prepare("UPDATE workspaces SET name = :name WHERE id = :id");
$success = $stmt->execute([':name' => $name, ':id' => $id]);

echo json_encode(["success" => $success]);

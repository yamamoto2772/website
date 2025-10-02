<?php
require_once("../localhost/db_open.php");

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

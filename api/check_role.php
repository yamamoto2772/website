<?php
// api/check_role.php
declare(strict_types=1);
session_start();
header("Content-Type: application/json; charset=UTF-8");

$role = $_SESSION['role'] ?? null;
echo json_encode(["role" => $role]);

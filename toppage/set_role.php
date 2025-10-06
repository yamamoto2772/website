<?php
// set_role.php
declare(strict_types=1);
session_start();

$role = $_POST['role'] ?? '';   // ← 開発しやすいようにGETも許可(任意)
$allow = ['student','company','admin'];
if (!in_array($role, $allow, true)) {
  // 不正値は選択画面へ
  header('Location: sentaku.html');
  exit;
}

session_regenerate_id(true);                     // ← セッション固定化対策
$_SESSION['role'] = $role;


header('Location:top.php');
exit;

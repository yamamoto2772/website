<?php
// set_role.php
declare(strict_types=1);
session_start();

$role = $_POST['role'] ?? '';
$allow = ['student','company'];
if (!in_array($role, $allow, true)) {
  // 不正値は選択画面へ戻す
  header('Location: sentaku.html');
  exit;
}

$_SESSION['role'] = $role;
header('Location: top.php');
exit;

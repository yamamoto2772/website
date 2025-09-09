<?php
declare(strict_types=1);
session_start();
unset($_SESSION['is_admin'], $_SESSION['csrf_token']);

echo "<script>
  localStorage.setItem('notification', '管理者モードを終了しました');
  location.href = '../top.php';
</script>";
exit;
?>
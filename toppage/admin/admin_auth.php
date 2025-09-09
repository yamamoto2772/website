<?php
declare(script_types=1);
session_start();
session_regenerate_id(true);

const ADMIN_PASS_HASH ='$argon2id$v=19$m=65536,t=4,p=1$V09NVTZoY3dHQlJBQm05ZQ$KGY7cHne5NAuYWnyW7SnBJrvBh/RsJU3WyeMW378gW8';

$pass = $_POST['password'] ?? '';
if (!$pass || !password_verify($pass, ADMIN_PASS_HASH)){
    $_SESSION['login_error'] = 'パスワードが違います。';
    header('Location: admin_login.php');
    exit;
}

//ログイン成功：管理者フラグ　&　CSRFトークンを発行
$_SESSION['is_admin'] = true;
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

echo "<script>
  localStorage.setItem('notification', '管理者モードが有効になりました');
  location.href = '../top.php';
</script>";
exit;
?>
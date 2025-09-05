<?php
// return_to_selection.php
declare(strict_types=1);
session_start();

// セッション変数全消し
$_SESSION = [];

// セッションクッキー破棄
if (ini_get('session.use_cookies')) {
  $p = session_get_cookie_params();
  setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// 完全終了
session_destroy();

// ここでスクリプト返してもOK（簡易実装なら以下）
echo "<script>
  try{
    localStorage.removeItem('userRole');
    localStorage.removeItem('admin');
    localStorage.removeItem('notification');
  }catch(e){}
  location.href='sentaku.html';
</script>";

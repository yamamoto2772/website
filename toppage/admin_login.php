<?php
declare(strict_types=1);
session_start();

/* すでに管理者ログイン済みならトップへ */
if (!empty($_SESSION['is_admin'])) {
  header('Location: top.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>管理者ログイン</title>
  <link rel="icon" href="../img/favicon.png" type="image/png" sizes="32x32">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    /* 全体レイアウト：上にヘッダー、下にメイン */
    body {
      font-family: sans-serif;
      margin: 0;
      background-image: url('../img/background.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
      display: flex;
      flex-direction: column; /* ← 縦並び */
    }

    /* ヘッダー（top.php と同系統） */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 24px 40px;
      border-bottom: 2px solid #000;
      background: #3f51b5;
      color: #fff;
    }
    .header-left {
      display: flex;
      gap: 24px;
      align-items: baseline;
    }
    .role-badge {
      opacity: .85;
      font-size: 14px;
    }

    /* メイン：フォームを中央寄せ（上寄せにしたいなら align-items:flex-start） */
    main {
      flex: 1;
      display: flex;
      justify-content: center;  /* 横中央 */
      align-items: flex-start;  /* 上寄せ。中央にしたいなら center */
      padding: 48px 16px;
    }

    /* ログインカード */
    .card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,.08);
      padding: 24px;
    }
    .card h1 {
      font-size: 20px;
      margin: 0 0 16px;
    }
    label { display: block; margin: 12px 0 6px; }
    input[type="password"] {
      width: 85%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 16px;
    }
    button {
      margin-top: 16px;
      width: 90%;
      padding: 12px;
      background: #3f51b5;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover { filter: brightness(.95); }

    .msg { color: #e53935; margin-top: 10px; }
    .back-link { display: inline-block; margin-top: 14px; text-decoration: none; }
  </style>
</head>
<body>
  <header class="header">
    <div class="header-left">
      <h2 style="margin:0;">企業学生間共有フォーム</h2>
      <div class="role-badge">
        <?php
          if (!empty($_SESSION['role'])) {
            echo $_SESSION['role'] === 'student' ? '学生として作業中です' :
                 ($_SESSION['role'] === 'company' ? '企業として作業中です' : '役割未設定');
          } else {
            echo '役割未設定';
          }
        ?>
      </div>
    </div>
    <div></div>
  </header>

  <main>
    <div class="card">
      <h1>管理者ログイン</h1>
      <form action="admin_auth.php" method="post" autocomplete="off">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">ログイン</button>
      </form>

      <a class="back-link" href="top.php">戻る</a>

      <?php if (!empty($_SESSION['login_error'])): ?>
        <div class="msg"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>

<?php
declare(strict_types=1);
$hashOut   = '';
$verifyOut = null;
$algoName  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $mode     = $_POST['mode'] ?? 'hash';

    if ($mode === 'hash' && $password !== '') {
        // 利用可能なら argon2id、無ければ bcrypt を使用
        if (defined('PASSWORD_ARGON2ID')) {
            $hashOut  = password_hash($password, PASSWORD_ARGON2ID);
            $algoName = 'argon2id';
        } else {
            // cost （10〜12）
            $hashOut  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $algoName = 'bcrypt';
        }
    }

    if ($mode === 'verify') {
        $hash = $_POST['hash'] ?? '';
        if ($password !== '' && $hash !== '') {
            $verifyOut = password_verify($password, $hash);
        }
    }
}
?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>パスワードハッシュツール</title>
<style>
  body { font-family: system-ui, sans-serif; margin: 40px; }
  h1 { font-size: 20px; margin-bottom: 12px; }
  form { margin: 16px 0; padding: 16px; border: 1px solid #ddd; border-radius: 8px; }
  label { display: block; margin: 8px 0 6px; font-weight: 600; }
  input[type="password"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; }
  textarea { height: 92px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
  button { margin-top: 12px; padding: 10px 14px; background: #3f51b5; color: #fff; border: 0; border-radius: 8px; cursor: pointer; }
  .note { color:#555; font-size: 13px; margin-top: 6px; }
  .ok { color: #2e7d32; font-weight: 700; }
  .ng { color: #c62828; font-weight: 700; }
  .algo { font-size: 13px; color: #333; }
</style>
</head>
<body>
  <h1>パスワードハッシュツール</h1>

  <form method="post">
    <label for="pw">パスワード（例：Corelista）</label>
    <input type="password" id="pw" name="password" required>
    <button type="submit" name="mode" value="hash">ハッシュを生成</button>
    <div class="note">※ 実行のたびにランダムソルトで毎回違う値になる</div>
  </form>

  <?php if ($hashOut): ?>
    <div class="algo">アルゴリズム: <?= htmlspecialchars($algoName) ?></div>
    <label>生成されたハッシュ（これを <code>admin_auth.php</code> に貼る）</label>
    <textarea readonly><?= htmlspecialchars($hashOut) ?></textarea>
  <?php endif; ?>


  <form method="post">
    <label for="pw2">照合するパスワード</label>
    <input type="password" id="pw2" name="password" required>

    <label for="hash">既存ハッシュ（admin_auth.php の定数を貼ってテスト）</label>
    <textarea id="hash" name="hash" required></textarea>

    <button type="submit" name="mode" value="verify">password_verify() で照合</button>

    <?php if ($verifyOut !== null): ?>
      <div style="margin-top:10px;">
        判定：<?= $verifyOut ? '<span class="ok">OK（一致）</span>' : '<span class="ng">NG（不一致）</span>' ?>
      </div>
    <?php endif; ?>
  </form>

  <p class="note">
    生成したハッシュは <code>admin_auth.php</code> の定数
    <code>ADMIN_PASS_HASH</code> にそのまま貼り付けてください。<br>
    例）<code>const ADMIN_PASS_HASH = 'ここに貼る';</code>
  </p>
</body>
</html>

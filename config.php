<?php
// データベース設定
define('DB_HOST', 'mysql325.phy.lolipop.lan');
define('DB_NAME', 'LAA1617951-team4');
define('DB_USER', 'LAA1617951');     // ← ユーザー名に書き換えてください
define('DB_PASS', 'passwd');     // ← パスワードに書き換えてください

// DSN（Data Source Name）
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    // PDOで接続
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 成功メッセージ（本番では表示しない）
    // echo "接続成功";
} catch (PDOException $e) {
    // エラー処理
    echo "接続失敗: " . $e->getMessage();
    exit;
}
?>

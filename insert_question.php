<?php
require 'config.php';

// 変数取得
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$image = '';

// 入力チェック
if (trim($title) === '' || trim($content) === '') {
    echo "すべての項目を入力してください。";
    exit;
}

// データベースに登録
try {
    $sql = "INSERT INTO questions (タイトル, 内容, 画像) VALUES (:title, :content, :image)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':image' => $image // 今は空でもOK
    ]);

    // 成功したら一覧ページへリダイレクト
    header("Location: workspace.php");
    exit;

} catch (PDOException $e) {
    echo "登録エラー: " . $e->getMessage();
    exit;
}

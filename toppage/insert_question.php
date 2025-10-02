<?php
// データベース接続ファイルの読み込み
require_once('../localhost/db_open.php');

$message = '';
$workspaces_id = $_GET['id'] ?? 1;

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $questioner = $_POST['questioner'] ?? '';
    $workspaces_id_post = $_POST['workspaces_id'] ?? 1;
    $image = '';

    // 画像アップロードの処理
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $upload_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
            $image = $upload_file;
        } else {
            $message = '画像のアップロードに失敗しました。';
        }
    }
    
    // 必須フィールドの確認
    if (empty($title) || empty($content) || empty($questioner)) {
        $message = 'タイトル、内容、質問者名は必須です。';
    } else {
        try {
            // プリペアドステートメントを使用
            $stmt = $pdo->prepare("INSERT INTO questions (タイトル, 内容, 質問者識別, 画像, 作成日時, workspaces_id) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt->execute([$title, $content, $questioner, $image, $workspaces_id_post]);

            // 投稿後に一覧ページへリダイレクト
            header('Location: question.php?id=' . urlencode($workspaces_id_post) . '&message=' . urlencode('質問が正常に投稿されました。'));
            exit();

        } catch (PDOException $e) {
            $message = 'データベースエラー: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>質問と回答 - 質問投稿</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .post-button, .back-button {
            display: inline-block;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s;
        }
        .post-button:hover, .back-button:hover {
            background-color: #0056b3;
        }
        main .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        .question-list {
            padding: 20px;
        }
        .question-item {
            background: #e9e9e9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 5px solid #007bff;
            overflow: hidden;
        }
        .question-item h2 {
            margin-top: 0;
            font-size: 1.2em;
        }
        .question-item p {
            margin: 5px 0;
        }
        .question-meta {
            font-size: 0.8em;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            margin-top: 10px;
        }
        .question-image-container {
            text-align: center;
            margin-top: 10px;
        }
        .question-image {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .post-form {
            padding: 20px;
        }
        .post-form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .post-form input[type="text"],
        .post-form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .post-form textarea {
            height: 100px;
        }
        .post-form button {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .post-form button:hover {
            background-color: #218838;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            float: right;
            margin-top: 5px;
            font-size: 0.9em;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container">
        
        <a href="question.php?id=<?= htmlspecialchars($workspaces_id) ?>" class="back-button">一覧に戻る</a>
    </div>
</header>

<main>
    <div class="container">
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="insert_question.php" method="post" enctype="multipart/form-data" class="post-form">
            <label for="title">タイトル:</label>
            <input type="text" id="title" name="title" required>

            <label for="content">内容:</label>
            <textarea id="content" name="content" required></textarea>

            <label for="questioner">質問者名:</label>
            <input type="text" id="questioner" name="questioner" required>

            <label for="image">画像（任意）:</label>
            <input type="file" id="image" name="image" accept="image/*">
            
            <input type="hidden" name="workspaces_id" value="<?= htmlspecialchars($workspaces_id) ?>">

            <button type="submit">投稿する</button>
        </form>
    </div>
</main>

</body>
</html>
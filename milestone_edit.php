<?php
require_once 'config.php';

// POST送信時の処理（保存・更新）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $date = $_POST['date'] ?? '';
    $detail = trim($_POST['detail'] ?? '');
    $now = date('Y-m-d H:i:s');

    if ($title === '' || $date === '') {
        $error = "タイトルと日付は必須です。";
    } else {
        if ($id === '') {
            // 新規登録
            $id = uniqid();
            $stmt = $pdo->prepare("INSERT INTO milestones (マイルストーンID, タイトル, 詳細, 日付, 作成日時) VALUES (:id, :title, :detail, :date, :created)");
            $stmt->execute([
                ':id' => $id,
                ':title' => $title,
                ':detail' => $detail,
                ':date' => $date,
                ':created' => $now,
            ]);
        } else {
            // 更新
            $stmt = $pdo->prepare("UPDATE milestones SET タイトル = :title, 詳細 = :detail, 日付 = :date WHERE マイルストーンID = :id");
            $stmt->execute([
                ':title' => $title,
                ':detail' => $detail,
                ':date' => $date,
                ':id' => $id,
            ]);
        }
        // 保存後はindex.phpにリダイレクト
        header("Location: index.php");
        exit;
    }
}

// 編集・新規フォーム用の初期値取得
$id = $_GET['id'] ?? '';
$date = $_GET['date'] ?? '';
$title = '';
$detail = '';

if ($id !== '') {
    // 編集モードでDBから取得
    $stmt = $pdo->prepare("SELECT * FROM milestones WHERE マイルストーンID = :id");
    $stmt->execute([':id' => $id]);
    $milestone = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($milestone) {
        $title = $milestone['タイトル'];
        $detail = $milestone['詳細'];
        $date = $milestone['日付'];
    } else {
        $error = "指定されたマイルストーンが存在しません。";
    }
} elseif ($date === '') {
    // 新規作成で日付指定なしの場合は今日の日付をセット
    $date = date('Y-m-d');
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<title>マイルストーン編集・作成</title>
<style>
  body {
    font-family: sans-serif;
    max-width: 600px;
    margin: 2em auto;
    padding: 1em;
    border: 1px solid #ccc;
    border-radius: 8px;
    background: #f9f9f9;
  }
  h1 {
    text-align: center;
    color: #3f51b5;
  }
  form {
    display: flex;
    flex-direction: column;
  }
  label {
    margin-top: 1em;
    font-weight: bold;
  }
  input[type="text"],
  input[type="date"],
  textarea {
    padding: 8px;
    font-size: 1em;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 0.3em;
  }
  textarea {
    resize: vertical;
  }
  .buttons {
    margin-top: 1.5em;
    display: flex;
    gap: 10px;
  }
  button {
    flex: 1;
    padding: 10px;
    font-size: 1em;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    color: white;
    background-color: #3f51b5;
    transition: background-color 0.3s ease;
  }
  button:hover {
    background-color: #2c387e;
  }
  .cancel-btn {
    background-color: #888;
  }
  .cancel-btn:hover {
    background-color: #555;
  }
  .error {
    margin-top: 1em;
    color: red;
    font-weight: bold;
  }
</style>
</head>
<body>

<h1><?= $id !== '' ? 'マイルストーン編集' : 'マイルストーン作成' ?></h1>

<?php if (!empty($error)): ?>
  <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="">
  <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">

  <label for="title">タイトル<span style="color:red;">*</span></label>
  <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required>

  <label for="date">日付<span style="color:red;">*</span></label>
  <input type="date" id="date" name="date" value="<?= htmlspecialchars($date) ?>" required>

  <label for="detail">詳細</label>
  <textarea id="detail" name="detail" rows="5"><?= htmlspecialchars($detail) ?></textarea>

  <div class="buttons">
    <button type="submit">保存</button>
    <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">キャンセル</button>
  </div>
</form>

</body>
</html>

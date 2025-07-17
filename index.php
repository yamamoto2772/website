<?php
session_start(); // セッション開始
require_once 'config.php';

// 管理者モード切替（GETパラメータでON/OFF）
if (isset($_GET['admin'])) {
    if ($_GET['admin'] === 'on') {
        $_SESSION['is_admin'] = true;
    } elseif ($_GET['admin'] === 'off') {
        unset($_SESSION['is_admin']);
    }
}

// POST処理：作成と削除
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['workspace_name'])) {
        $name = htmlspecialchars($_POST['workspace_name'], ENT_QUOTES, 'UTF-8');
        $stmt = $pdo->prepare("INSERT INTO workspaces (name) VALUES (:name)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
    }

    if (isset($_POST['delete_id']) && !empty($_SESSION['is_admin'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $pdo->prepare("DELETE FROM workspaces WHERE id = :id");
        $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

// データ取得
$workspaces = [];
try {
    $stmt = $pdo->query("SELECT * FROM workspaces ORDER BY id DESC");
    $workspaces = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // echo 'データ取得エラー: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>ワークスペース一覧</title>
  <style>
    body {
      font-family: "Helvetica Neue", sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 0;
    }
    .header {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 20px;
      padding: 25px 10px;
      background: #ffffff;
      border-bottom: 2px solid #ddd;
    }
    .button {
      text-decoration: none;
      background: #3f51b5;
      color: white;
      padding: 12px 24px;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      transition: background 0.3s ease;
      text-align: center;
      cursor: pointer;
    }
    .button:hover {
      background: #2c3e99;
    }
    .container {
      max-width: 800px;
      margin: 40px auto;
      padding: 20px;
    }
    .create-workspace {
      display: block;
      width: 100%;
      padding: 15px;
      margin-bottom: 20px;
      background: #4caf50;
      color: white;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    .create-workspace:hover {
      background-color: #388e3c;
    }
    .input {
      width: 100%;
      padding: 12px;
      font-size: 16px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .workspace {
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      padding: 20px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 15px;
    }
    .workspace-left {
      flex: 1;
      margin: 0;
    }
    .workspace-name {
      background-color: transparent;
      color: #333;
      border: none;
      font-size: 18px;
      font-weight: bold;
      text-align: left;
      cursor: pointer;
      width: 100%;
    }
    .workspace-name:hover {
      text-decoration: underline;
    }
    .workspace-right {
      margin: 0;
    }
    .delete-button {
      background-color: #ff4d4d;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
    }
    #form-area {
      display: none;
      animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @media (max-width: 600px) {
      .button, .create-workspace, .workspace-name {
        width: 100%;
        font-size: 14px;
        padding: 12px;
      }
      .header {
        flex-direction: column;
        align-items: center;
      }
      .workspace {
        flex-direction: column;
        align-items: stretch;
      }
      .workspace-right {
        text-align: right;
        margin-top: 10px;
      }
    }
  </style>
  <script>
    function showForm() {
      document.getElementById('form-area').style.display = 'block';
      document.getElementById('create-btn').style.display = 'none';
    }
  </script>
</head>
<body>
  <header class="header">
    <a href="#" class="button">企業学生間共有フォーム</a>
    <?php if (!empty($_SESSION['is_admin'])): ?>
      <a href="?admin=off" class="button" style="background:#999;">管理者モード終了</a>
    <?php else: ?>
      <a href="?admin=on" class="button">管理者として作業を開始する</a>
    <?php endif; ?>
  </header>

  <main class="container">
    <button id="create-btn" class="create-workspace" onclick="showForm()">＋ 新規ワークスペース作成</button>

    <form id="form-area" method="POST" action="">
      <input type="text" name="workspace_name" class="input" placeholder="ワークスペース名を入力" required />
      <button type="submit" class="create-workspace">作成する</button>
    </form>

    <?php foreach ($workspaces as $workspace): ?>
      <div class="workspace">
        <form method="GET" action="workspace.php" class="workspace-left">
          <input type="hidden" name="id" value="<?= $workspace['id'] ?>">
          <button type="submit" class="workspace-name">
            <?= htmlspecialchars($workspace['name'], ENT_QUOTES, 'UTF-8') ?>
          </button>
        </form>

        <?php if (!empty($_SESSION['is_admin'])): ?>
          <form method="POST" action="" class="workspace-right">
            <input type="hidden" name="delete_id" value="<?= $workspace['id'] ?>">
            <button type="submit" class="delete-button" onclick="return confirm('このワークスペースを削除しますか？');">削除</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </main>
</body>
</html>

<?php
require_once("../localhost/db_open.php");

$workspace_id = $_GET['id'] ?? '';
if (!ctype_digit($workspace_id)) {
  exit("不正なIDです");
}

$stmt = $pdo->prepare("SELECT * FROM workspaces WHERE workspaces_id = ?");
$stmt->execute([$workspace_id]);
$workspace = $stmt->fetch();

if (!$workspace) {
  exit("ワークスペースが見つかりません。");
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($workspace['name']) ?> | 企業学生間共有フォーム</title>
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      display: grid;
      grid-template-areas:
        "header header"
        "sidebar main";
      grid-template-columns: 220px 1fr;
      grid-template-rows: auto 1fr;
      height: 100vh;
    }

    header {
      grid-area: header;
      background-color: #3f51b5;
      color: white;
      padding: 1em;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    header h1 {
      margin: 0;
      font-size: 1.4em;
    }

    header p {
      margin: 0;
      font-size: 1em;
      font-weight: normal;
      opacity: 0.9;
    }

    nav {
      grid-area: sidebar;
      background-color: #f0f0f0;
      padding: 1em;
      border-right: 1px solid #ccc;
    }

    nav button {
      display: block;
      width: 100%;
      margin-bottom: 10px;
      padding: 10px;
      font-size: 0.95em;
      background-color: #ddd;
      border: none;
      cursor: pointer;
    }

    main {
      grid-area: main;
      padding: 1em;
      overflow-y: auto;
    }

    #scrollTopBtn {
      position: fixed;
      bottom: 30px;
      right: 50px;
      display: none;
      padding: 20px;
      font-size: 20px;
      background-color: #4a68d1;
      color: white;
      border: none;
      border-radius: 50%;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
      width: 50px;
      height: 50px;
    }
  </style>
</head>
<body>

  <header>
    <div>
      <h1>企業学生間共有フォーム</h1>
      <p><?= htmlspecialchars($workspace['name']) ?> のワークスペース</p>
    </div>
     <div>
    <a href="top.php" style="color: white; text-decoration: none; background: #ffffff33; padding: 8px 16px; border-radius: 8px; border: 1px solid #fff;">← トップに戻る</a>
  </div>
  </header>

  <nav>
    <button onclick="loadPage('home.html')">ワークスペーストップ</button>
    <button onclick="loadPage('question.php?id=<?= $workspace_id ?>')">質問フォーム</button>
    <button onclick="loadPage('chat.html')">チャットフォーム</button>
    <button onclick="loadPage('task.html')">課題提示フォーム</button>
    <button onclick="loadPage('result.html')">成果物提出フォーム</button>
    <button onclick="loadPage('request.html')">管理者への要望</button>
  </nav>

  <main id="main-content">
    <p>読み込みを行うにはサイドバーのボタンを押してください。</p>
  </main>

  <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>

  <script>
    function loadPage(page) {
      fetch(page)
        .then(response => {
          if (!response.ok) throw new Error("読み込み失敗");
          return response.text();
        })
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
        })
        .catch(error => {
          document.getElementById('main-content').innerHTML =
            `<p style="color:red;">${page} の読み込みに失敗しました。</p>`;
        });
    }

    const mainContent = document.getElementById('main-content');
    const btn = document.getElementById('scrollTopBtn');

    mainContent.addEventListener('scroll', function () {
      if (mainContent.scrollTop > 200) {
        btn.style.display = 'block';
      } else {
        btn.style.display = 'none';
      }
    });

    function scrollToTop() {
      mainContent.scrollTo({ top: 0, behavior: 'smooth' });
    }
  </script>

</body>
</html>

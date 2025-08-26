<?php
require_once("../localhost/db_open.php");

$stmt = $pdo->query("SELECT workspaces_id, name FROM workspaces ORDER BY created_at DESC");
$workspaces = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>トップページ</title>
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
      padding: 0;
      background: #ccc;
    }

    .header {
      display: flex;
      justify-content: center;
      gap: 40px;
      padding: 30px 0;
      border-bottom: 2px solid #000;
      background-image: url('img/background.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      background-color: #7abdeaff;
    }

    .button {
      text-decoration: none;
      color: #000;
      background: #ffffff;
      border: 2px solid #000;
      padding: 15px 30px;
      border-radius: 10px;
      font-weight: bold;
      font-size: 16px;
      width: 350px;
      text-align: center;
      cursor: pointer;
    }

    .container {
      max-width: 95%;
      margin: 40px auto;
      padding: 20px;
      background: #fff;
      border: 2px solid #000;
    }

    .create-workspace {
      display: block;
      width: 100%;
      text-align: center;
      border: 2px solid #000;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 30px;
      font-weight: bold;
      background: #ffffff;
      cursor: pointer;
      font-size: 16px;
    }

    .workspace-list {
      list-style: none;
      padding: 0;
    }

    .workspace-card {
      background: #f5faff;
      border: 2px solid #007acc;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .workspace-title {
      font-size: 20px;
      font-weight: bold;
      color: #007acc;
      margin-bottom: 10px;
    }

    .workspace-actions {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    .workspace-actions a {
      background-color: #007acc;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
    }

    .workspace-actions a:hover {
      background-color: #005fa3;
    }

    .admin-controls {
      margin-top: 20px;
      display: flex;
      gap: 20px;
    }

    .logout-btn {
      background-color: #ddd;
      padding: 10px 20px;
      font-size: 14px;
      border: 2px solid #333;
      border-radius: 8px;
      cursor: pointer;
    }

    .toast {
      position: fixed;
      top: 30px;
      right: 30px;
      background-color: #007acc;
      color: #fff;
      padding: 12px 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      opacity: 0;
      transition: opacity 0.5s ease-in-out;
      z-index: 1000;
    }

    .toast.show {
      opacity: 1;
    }

    .delete-btn {
      background-color: red;
    }

    #scrollTopBtn {
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 56px;
      height: 56px;
      background: #ff8c00;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      cursor: pointer;
      border: none;
      clip-path: polygon(25% 5%, 75% 5%, 100% 50%, 75% 95%, 25% 95%, 0 50%);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
      z-index: 999;
      transition: transform 0.2s;
    }

    #scrollTopBtn:hover {
      transform: scale(1.05);
      background-color: #ffa64d;
    }
  </style>
</head>
<body>

  <header class="header">
    <a class="button">企業学生間共有フォーム</a>
    <a href="#" class="button" id="admin-btn">管理者として作業を開始する</a>
  </header>

  <main class="container">
    <a href="create-workspace.html"><button class="create-workspace">新規ワークスペース作成</button></a>

    <ul class="workspace-list" id="workspace-list">
      <?php foreach ($workspaces as $ws): ?>
        <li class="workspace-card">
          <div class="workspace-title"><?= htmlspecialchars($ws['name']) ?></div>
          <div class="workspace-actions">
            <a href="workspace.php?id=<?= $ws['workspaces_id'] ?>">開く</a>
            <a class="delete-btn" href="#" data-id="<?= $ws['workspaces_id'] ?>">削除</a>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <div id="admin-functions" class="admin-controls" style="display: none;">
      <button class="logout-btn" onclick="logoutAdmin()">管理者モード終了</button>
    </div>

    <button id="scrollTopBtn" aria-label="ページの先頭へ戻る">↑</button>

  </main>

  <script>
    const adminBtn = document.getElementById("admin-btn");
    const adminFunctions = document.getElementById("admin-functions");
    const ADMIN_PASSWORD = "admin123";

    function checkAdminMode() {
      const isAdmin = localStorage.getItem("admin") === "true";
      if (isAdmin) {
        adminFunctions.style.display = "flex";
      } else {
        document.querySelectorAll(".delete-btn").forEach(btn => btn.style.display = "none");
      }
    }

    function logoutAdmin() {
      if (confirm("本当に管理者モードを終了しますか？")) {
        localStorage.removeItem("admin");
        document.cookie = "admin=false; path=/";
        alert("管理者モードを終了しました。");
        location.reload();
      }
    }

    adminBtn.addEventListener("click", () => {
      const input = prompt("管理者パスワードを入力してください：");
      if (input === ADMIN_PASSWORD) {
        localStorage.setItem("admin", "true");
        document.cookie = "admin=true; path=/";
        alert("管理者モードが有効になりました。");
        location.reload();
      } else {
        alert("パスワードが違います。");
      }
    });

    window.addEventListener("DOMContentLoaded", () => {
      const message = localStorage.getItem("notification");
      if (message) {
        showToast(message);
        localStorage.removeItem("notification");
      }
      checkAdminMode();
    });

    function showToast(message) {
      const toast = document.createElement("div");
      toast.className = "toast";
      toast.textContent = message;
      document.body.appendChild(toast);

      setTimeout(() => {
        toast.classList.add("show");
      }, 100);

      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => {
          document.body.removeChild(toast);
        }, 500);
      }, 3000);
    }

    document.getElementById('scrollTopBtn')
      .addEventListener('click', () =>
        window.scrollTo({ top: 0, behavior: 'smooth' }));

    // 仮削除処理
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const id = this.getAttribute('data-id');
        if (confirm(`ワークスペース ID:${id} を削除しますか？（処理未実装）`)) {
          alert(`ID:${id} を削除しました（仮）`);
        }
      });
    });
  </script>

</body>
</html>

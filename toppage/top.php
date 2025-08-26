<?php
require_once("../localhost/db_open.php");

$stmt = $pdo->query("SELECT workspaces_id, name FROM workspaces ORDER BY created_at DESC");
$workspaces = $stmt->fetchAll();
$admin = ($_COOKIE['admin'] ?? '') === 'true';
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
      background: #f1f5fb;
    }

    .header {
      display: flex;
      justify-content: center;
      gap: 40px;
      padding: 30px 0;
      border-bottom: 2px solid #000;
      background-image: url('../img/background.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .button {
      text-decoration: none;
      color: #000000;
      background: #ffffff;
      border: 2px solid #000;
      padding: 15px 30px;
      border-radius: 10px;
      font-weight: bold;
      font-size: 16px;
      width: 350px;
      text-align: center;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .container {
      max-width: 95%;
      margin: 40px auto;
      padding: 30px;
      background: #ffffff;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .create-container {
      display: flex;
      justify-content: center;
      margin-bottom: 30px;
    }

    .create-workspace-link {
      text-align: center;
      padding: 20px 40px;
      background-color: #ffffff;
      color: #000;
      text-decoration: none;
      font-size: 18px;
      font-weight: bold;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border: none;
      transition: background-color 0.2s ease-in-out;
    }

    .create-workspace-link:hover {
      background-color: #f0f0f0;
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

    .delete-btn {
      background-color: red;
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

    #scrollTopBtn {
      display: none; /* ← 初期状態は非表示 */
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 56px;
      height: 56px;
      background: #ff8c00;
      color: #fff;
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
    <div class="create-container">
      <a href="create-workspace.html" class="create-workspace-link">新規ワークスペース作成</a>
    </div>
    <ul class="workspace-list" id="workspace-list">
      <?php foreach ($workspaces as $ws): ?>
        <li class="workspace-card">
          <div class="workspace-title"><?= htmlspecialchars($ws['name']) ?></div>
          <div class="workspace-actions">
            <a href="workspace.php?id=<?= $ws['workspaces_id'] ?>">開く</a>
            <?php if ($admin): ?>
              <a class="delete-btn" href="#" data-id="<?= $ws['workspaces_id'] ?>" data-name="<?= htmlspecialchars($ws['name']) ?>">削除</a>
            <?php endif; ?>
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
    const ADMIN_PASSWORD = "admin123";

    function checkAdminMode() {
      const isAdmin = localStorage.getItem("admin") === "true";
      const adminBtn = document.getElementById("admin-btn");
      const adminControls = document.getElementById("admin-functions");

      if (isAdmin) {
        adminControls.style.display = "flex";
        adminBtn.textContent = "管理者モードを終了する";
        adminBtn.addEventListener("click", logoutAdmin);
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

    document.getElementById("admin-btn").addEventListener("click", () => {
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

    function showToast(message) {
      const toast = document.createElement("div");
      toast.className = "toast";
      toast.textContent = message;
      document.body.appendChild(toast);

      setTimeout(() => toast.classList.add("show"), 100);
      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 500);
      }, 3000);
    }

    window.addEventListener("DOMContentLoaded", () => {
      if (localStorage.getItem("notification")) {
        showToast(localStorage.getItem("notification"));
        localStorage.removeItem("notification");
      }
      checkAdminMode();
    });

    document.getElementById("scrollTopBtn")
      .addEventListener("click", () =>
        window.scrollTo({ top: 0, behavior: "smooth" }));

    // スクロールして一定以上でボタン表示
    const scrollTopBtn = document.getElementById("scrollTopBtn");
    window.addEventListener("scroll", () => {
      if (window.scrollY > 300) {
        scrollTopBtn.style.display = "flex";
      } else {
        scrollTopBtn.style.display = "none";
      }
    });

    // 削除処理
    document.querySelectorAll(".delete-btn").forEach(btn => {
      btn.addEventListener("click", e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        if (confirm(`ワークスペース 「${name}」 を削除しますか？`)) {
          fetch("delete_workspace.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              localStorage.setItem("notification", `ワークスペース「${name}」を削除しました`);
              location.reload();
            } else {
              alert(data.error || "削除に失敗しました");
            }
          })
          .catch(() => alert("通信エラーが発生しました"));
        }
      });
    });
  </script>
</body>
</html>

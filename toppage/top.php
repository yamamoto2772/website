<?php
require_once("../localhost/db_open.php");
session_start();

/* 役割未選択なら選択ページへ差し戻し */
if (empty($_SESSION['role'])) {
  echo "<script>localStorage.setItem('selectionWarning','役割を選択してから進んでください');location.replace('sentaku.html');</script>";
  exit;
}

/* 並べ替えキー（ホワイトリスト） */
$sort = $_GET['sort'] ?? ($_SESSION['ws_sort'] ?? 'newest');
$sortMap = [
  'newest'    => 'created_at DESC',  // 新しい順（デフォルト）
  'oldest'    => 'created_at ASC',   // 古い順
  'name_asc'  => 'name ASC',         // 名前 A→Z
  'name_desc' => 'name DESC',        // 名前 Z→A
];
if (!isset($sortMap[$sort])) $sort = 'newest';
$_SESSION['ws_sort'] = $sort;

$orderBy = $sortMap[$sort];

/* データ取得（安全な ORDER BY のみ差し込み） */
$sql = "SELECT id, name, created_at FROM workspaces ORDER BY $orderBy";
$stmt = $pdo->query($sql);
$workspaces = $stmt->fetchAll();

/* 管理者・CSRF */
$is_admin   = !empty($_SESSION['is_admin']);
$csrf_token = $is_admin && !empty($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>トップページ</title>
  <link rel="icon" href="../img/favicon.png" type="image/png" sizes="32x32">
  <?php if ($csrf_token): ?>
    <meta name="csrf-token" content="<?= htmlspecialchars($csrf_token) ?>">
  <?php endif; ?>

  <style>
    body {
      font-family: sans-serif;
      margin: 0;
      padding: 0;
      background-image: url('../img/background.png');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 30px 40px;
      border-bottom: 2px solid #000;
      background-color: #3f51b5;
      color: #fff;
    }
    .header-left { display: flex; gap: 24px; align-items: baseline; }
    .role-badge { opacity: .85; font-size: 14px; }

    .button {
      text-decoration: none;
      color: #000000;
      background: #ffffff;
      border: 2px solid #000;
      padding: 12px 20px;
      border-radius: 10px;
      border-color: #3f51b5;
      font-weight: bold;
      font-size: 15px;
      text-align: center;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      white-space: nowrap;
      min-width: 140px;
      max-width: 200px;
    }
    .button:hover { background-color: #ddd; }

    .container {
      max-width: 70%;
      margin: 40px auto;
      padding: 30px;
      background: #ffffff;
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 20px;
    }
    .create-workspace-link {
      text-align: center;
      padding: 14px 24px;
      background-color: #ffffff;
      color: #000;
      text-decoration: none;
      font-size: 16px;
      font-weight: bold;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      border: 1px solid #eee;
      transition: background-color 0.2s;
    }
    .create-workspace-link:hover { background-color: #f0f0f0; }

    .sort-container select {
      padding: 8px 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .workspace-list { list-style: none; padding: 0; }
    .workspace-card {
      background: #f5faff;
      border: 2px solid #007acc;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }
    .workspace-title {
      font-size: 20px;
      font-weight: bold;
      color: #007acc;
      word-break: break-all;
      margin: 0;
    }
    .workspace-meta {
      color: #666;
      font-size: 12px;
      margin-top: 6px;
    }
    .workspace-actions { display: flex; gap: 10px; }
    .workspace-actions a {
      background-color: #007acc;
      color: white;
      padding: 8px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 14px;
    }
    .workspace-actions a:hover { background-color: #005fa3; }
    .delete-btn { background-color: #e53935; }
    .edit-btn { background-color: #4caf50; }
    .edit-btn:hover { background-color: #388e3c; }

    .toast {
      position: fixed; top: 30px; right: 30px; background-color: #007acc;
      color: #fff; padding: 12px 20px; border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2); opacity: 0; transition: opacity 0.5s; z-index: 1000;
    }
    .toast.show { opacity: 1; }

    #scrollTopBtn {
      display: none; position: fixed; bottom: 20px; right: 20px; width: 56px; height: 56px;
      background: #ff8c00; color: #fff; align-items: center; justify-content: center; font-size: 22px;
      cursor: pointer; border: none; clip-path: polygon(25% 5%, 75% 5%, 100% 50%, 75% 95%, 25% 95%, 0 50%);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25); z-index: 999; transition: transform 0.2s;
    }
    #scrollTopBtn:hover { transform: scale(1.05); background-color: #ffa64d; }
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
    <div class="header-right" style="display:flex; gap:16px;">
      <?php if (!$is_admin): ?>
        <a href="admin_login.php" class="button">管理者として作業を開始する</a>
      <?php else: ?>
        <a href="admin_logout.php" class="button">管理者モードを終了する</a>
      <?php endif; ?>
      <a href="return_to_selection.php" class="button return-btn">選択に戻る</a>
    </div>
  </header>

  <main class="container">
    <!-- ツールバー：作成ボタン + 並べ替え -->
    <div class="toolbar">
      <a href="create-workspace.html" class="create-workspace-link">新規ワークスペース作成</a>

      <div class="sort-container" style="margin-left:auto;">
        <form method="get" action="top.php">
          <label for="sort" style="margin-right:8px;">並べ替え:</label>
          <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="newest"    <?= $sort==='newest'?'selected':'' ?>>新しい順</option>
            <option value="oldest"    <?= $sort==='oldest'?'selected':'' ?>>古い順</option>
            <option value="name_asc"  <?= $sort==='name_asc'?'selected':'' ?>>名前順 (A→Z)</option>
            <option value="name_desc" <?= $sort==='name_desc'?'selected':'' ?>>名前順 (Z→A)</option>
          </select>
        </form>
      </div>
    </div>

    <ul class="workspace-list">
      <?php foreach ($workspaces as $ws): ?>
        <li class="workspace-card">
          <div>
            <div class="workspace-title"><?= htmlspecialchars($ws['name']) ?></div>
            <div class="workspace-meta">
              作成日時: <?= htmlspecialchars($ws['created_at']) ?>
            </div>
          </div>
          <div class="workspace-actions">
            <a href="workspace.php?id=<?= $ws['id'] ?>">開く</a>
            <?php if ($is_admin): ?>
              <a class="edit-btn" href="#" data-id="<?= $ws['id'] ?>" data-name="<?= htmlspecialchars($ws['name']) ?>">編集</a>
              <a class="delete-btn" href="#" data-id="<?= $ws['id'] ?>" data-name="<?= htmlspecialchars($ws['name']) ?>">削除</a>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <button id="scrollTopBtn" aria-label="ページの先頭へ戻る">↑</button>
  </main>

<script>
  function showToast(message, type = 'info') {
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.textContent = message;
    if (type === 'warn') toast.style.backgroundColor = '#e53935';
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add("show"), 100);
    setTimeout(() => { toast.classList.remove("show"); setTimeout(() => toast.remove(), 500); }, 3000);
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  document.addEventListener("DOMContentLoaded", () => {
    // 選択時や管理者ログイン/ログアウト時の通知
    const note = localStorage.getItem("notification");
    if (note) { showToast(note, 'info'); localStorage.removeItem("notification"); }

    // スクロールトップ
    document.getElementById("scrollTopBtn").addEventListener("click", () =>
      window.scrollTo({ top: 0, behavior: "smooth" })
    );
    window.addEventListener("scroll", () => {
      const btn = document.getElementById("scrollTopBtn");
      btn.style.display = window.scrollY > 300 ? "flex" : "none";
    });

    // 削除（管理者のみボタンが出る）
    document.querySelectorAll(".delete-btn").forEach(btn => {
      btn.addEventListener("click", e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        if (confirm(`ワークスペース 「${name}」 を削除しますか？`)) {
          fetch("delete_workspace.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-Token": getCsrfToken()
            },
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

    // 編集（名前変更）
    document.querySelectorAll(".edit-btn").forEach(btn => {
      btn.addEventListener("click", e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const oldName = btn.dataset.name;
        const newName = prompt(`「${oldName}」の新しい名前を入力してください：`, oldName);
        if (newName && newName !== oldName) {
          fetch("update_workspace.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-Token": getCsrfToken()
            },
            body: JSON.stringify({ id, name: newName })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              localStorage.setItem("notification", `ワークスペース名を「${oldName}」から「${newName}」に変更しました`);
              location.reload();
            } else {
              alert(data.error || "更新に失敗しました");
            }
          })
          .catch(() => alert("通信エラーが発生しました"));
        }
      });
    });
  });
</script>

</body>
</html>

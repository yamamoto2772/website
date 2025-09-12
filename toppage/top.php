<?php
require_once("../localhost/db_open.php");
session_start();

/* 役割未選択なら選択ページへ差し戻し */
if (empty($_SESSION['role'])) {
  echo "<script>localStorage.setItem('selectionWarning','役割を選択してから進んでください');location.replace('sentaku.html');</script>";
  exit;
}

/* 並べ替えキー */
$sort = $_GET['sort'] ?? ($_SESSION['ws_sort'] ?? 'newest');
$sortMap = [
  'newest'    => 'created_at DESC',
  'oldest'    => 'created_at ASC',
  'name_asc'  => 'name ASC',
  'name_desc' => 'name DESC',
];
if (!isset($sortMap[$sort])) $sort = 'newest';
$_SESSION['ws_sort'] = $sort;
$orderBy = $sortMap[$sort];

/* 検索キーワード（部分一致） */
$q = trim($_GET['q'] ?? '');
$searchSql = '';
$params = [];
if ($q !== '') {
  $searchSql = "WHERE name LIKE :q";
  $params[':q'] = "%{$q}%";
}

/* ページング設定 */
$perPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

/* 総件数取得（検索条件込み） */
$countSql = "SELECT COUNT(*) FROM workspaces $searchSql";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
if ($page > $totalPages) $page = $totalPages;

$offset = ($page - 1) * $perPage;

/* データ取得（検索 + 並べ替え + ページング） */
$sql = "SELECT id, name, created_at
        FROM workspaces
        $searchSql
        ORDER BY $orderBy
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$workspaces = $stmt->fetchAll();

/* 管理者 */
$is_admin   = !empty($_SESSION['is_admin']);
$csrf_token = $is_admin && !empty($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null;

/* ページリンク生成のためのクエリ組み立て */
function build_query(array $add = []) {
  $base = $_GET;
  unset($base['page']); 
  $params = array_merge($base, $add);
  return htmlspecialchars('?' . http_build_query($params), ENT_QUOTES, 'UTF-8');
}
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
        background: #f9f9f9;
        min-height: 100vh;
      }

      /* ヘッダー */
      .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 30px;
        background: #3f51b5;
        color: #fff;
      }
      .header-left { display: flex; gap: 20px; align-items: baseline; }
      .role-badge { opacity: .85; font-size: 14px; }

      /* ボタン */
      .button {
        text-decoration: none;
        color: #000;
        background: #fff;
        border: 2px solid #3f51b5;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 14px;
        box-shadow: 0 4px 8px rgba(0,0,0,.1);
      }
      .button:hover { background: #eee; }

      /* メインコンテナ */
      .container {
        max-width: 80%;
        margin: 30px auto;
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.1);
      }

      /* ツールバー */
      .toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        margin-bottom: 20px;
      }
      .create-workspace-link {
        padding: 10px 20px;
        background: #007acc;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
      }
      .create-workspace-link:hover { background: #005fa3; }
      .sort-container select, .search-container input {
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
      }

      /* ワークスペースカード */
      .workspace-list { list-style: none; padding: 0; margin: 0; }
      .workspace-card {
        background: #f5faff;
        border: 1px solid #007acc;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
      }
      .workspace-title { font-weight: bold; font-size: 18px; color: #007acc; }
      .workspace-meta { font-size: 12px; color: #666; margin-top: 4px; }
      .workspace-actions { display: flex; gap: 8px; flex-wrap: wrap; }
      .workspace-actions a {
        background: #007acc;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        text-decoration: none;
      }
      .workspace-actions a:hover { background: #005fa3; }
      .delete-btn { background: #e53935 !important; }
      .edit-btn { background: #4caf50 !important; }
      .edit-btn:hover { background: #388e3c !important; }

      /* 件数表示・ページネーション */
      .range-info { text-align: center; margin: 12px 0; font-size: 14px; color: #333; }
      .pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-top: 20px;
        flex-wrap: wrap;
      }
      .pagination a, .pagination span {
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        text-decoration: none;
        color: #333;
        background: #fafafa;
      }
      .pagination a:hover { background: #f0f0f0; }
      .pagination .current {
        background: #3f51b5;
        color: #fff;
        border-color: #3f51b5;
      }
      .pagination .disabled { opacity: .45; pointer-events: none; }

      /* トースト通知 */
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
        transition: opacity 0.5s;
        z-index: 1000;
      }
      .toast.show { opacity: 1; }

      /* スクロールトップ */
      #scrollTopBtn {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
        background: #007acc;
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
      #scrollTopBtn:hover { transform: scale(1.05); background-color: #007acc; }

      /* レスポンシブ */
      @media (max-width: 768px) {
        .header { padding: 16px 20px; flex-direction: column; align-items: flex-start; gap: 8px; }
        .header-right { width: 100%; display: flex; gap: 8px; }
        .header-right .button { flex: 1; padding: 10px; font-size: 13px; }

        .container { max-width: 94%; margin: 20px auto; padding: 20px; }
        .toolbar { flex-direction: column; align-items: stretch; gap: 12px; }
        .search-container { width: 100%; display: flex; gap: 8px; }
        .search-container input { flex: 1; }
        .workspace-card { flex-direction: column; align-items: stretch; }
        .workspace-actions { justify-content: flex-end; }
      }

      @media (max-width: 480px) {
        .workspace-title { font-size: 16px; }
        .workspace-meta { font-size: 11px; }
        #scrollTopBtn { width: 44px; height: 44px; font-size: 18px; }
      }

  </style>
</head>
<body>

  <header class="header">
    <div class="header-left">
      <h3 style="margin:0;">企業学生間共有フォーム</h3>
      <div class="role-badge">
        <?php
          if (!empty($_SESSION['role'])) {
            echo $_SESSION['role'] === 'student' ? '学生として作業中です'
                 : ($_SESSION['role'] === 'company' ? '企業として作業中です' : '役割未設定');
          } else {
            echo '役割未設定';
          }
        ?>
      </div>
    </div>
    <div class="header-right" style="display:flex; gap:16px;">
      <?php if (!$is_admin): ?>
        <a href="./admin/admin_login.php" class="button">管理者として作業を開始する</a>
      <?php else: ?>
        <a href="./admin/admin_logout.php" class="button">管理者モードを終了する</a>
      <?php endif; ?>
      <a href="return_to_selection.php" class="button return-btn">選択に戻る</a>
    </div>
  </header>

  <main class="container">
    <div class="toolbar">
      <a href="./workspace/create-workspace.html" class="create-workspace-link">＋ 新規ワークスペース</a>

      <!-- 検索フォーム -->
      <form method="get" action="top.php" class="search-container" style="display:flex; gap:8px;">
        <input type="text" name="q" placeholder="名前で検索" value="<?= htmlspecialchars($q) ?>">
        <button type="submit" class="button">検索</button>
        <?php if ($q !== ''): ?>
          <a href="top.php" class="button" style="background:#eee;">リセット</a>
        <?php endif; ?>
      </form>

      <!-- 並べ替え -->
      <div class="sort-container" style="margin-left:auto;">
        <form method="get" action="top.php">
          <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
          <label for="sort" style="margin-right:8px;">並べ替え:</label>
          <select name="sort" id="sort" onchange="this.form.submit()">
            <option value="newest"    <?= $sort==='newest'?'selected':'' ?>>新しい順</option>
            <option value="oldest"    <?= $sort==='oldest'?'selected':'' ?>>古い順</option>
            <option value="name_asc"  <?= $sort==='name_asc'?'selected':'' ?>>名前順 (A→Z)</option>
            <option value="name_desc" <?= $sort==='name_desc'?'selected':'' ?>>名前順 (Z→A)</option>
          </select>
          <input type="hidden" name="page" value="1">
        </form>
      </div>
    </div>

    <?php if ($total === 0): ?>
      <p>該当するワークスペースはありません。</p>
    <?php else: ?>
      <ul class="workspace-list">
        <?php foreach ($workspaces as $ws): ?>
          <li class="workspace-card">
            <div>
              <div class="workspace-title"><?= htmlspecialchars($ws['name']) ?></div>
              <div class="workspace-meta">作成日時: <?= htmlspecialchars($ws['created_at']) ?></div>
            </div>
            <!-- workspace.phpをframe.htmlに置き換える-->
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

      <!-- 件数表示 -->
      <div class="range-info">
        <?php
          $from = $offset + 1;
          $to   = min($offset + $perPage, $total);
          echo "全 {$total} 件中 {$from} ～ {$to} 件を表示";
        ?>
      </div>

      <!-- ページネーション -->
      <nav class="pagination" aria-label="ページナビゲーション">
        <?php
          // 前へ
          if ($page > 1) {
            echo '<a href="'. build_query(['page'=>$page-1]) .'">‹ 前へ</a>';
          } else {
            echo '<span class="disabled">‹ 前へ</span>';
          }

          // 全ページ番号
          for ($p = 1; $p <= $totalPages; $p++) {
            if ($p == $page) {
              echo '<span class="current">'. $p .'</span>';
            } else {
              echo '<a href="'. build_query(['page'=>$p]) .'">'. $p .'</a>';
            }
          }

          // 次へ
          if ($page < $totalPages) {
            echo '<a href="'. build_query(['page'=>$page+1]) .'">次へ ›</a>';
          } else {
            echo '<span class="disabled">次へ ›</span>';
          }
        ?>
      </nav>
    <?php endif; ?>

    <button id="scrollTopBtn" aria-label="ページの先頭へ戻る">↑</button>
  </main>

<script>
  // トースト（右上にポップ → 3秒で消える）
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

    // 削除（管理者のみ）
    document.querySelectorAll(".delete-btn").forEach(btn => {
      btn.addEventListener("click", e => {
        e.preventDefault();
        const id = btn.dataset.id;
        const name = btn.dataset.name;
        if (confirm(`ワークスペース 「${name}」 を削除しますか？`)) {
          fetch("./workspace/delete_workspace.php", {
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
              // 現在の検索・並べ替え・ページ状態を維持したまま再読込
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
          fetch("./workspace/update_workspace.php", {
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

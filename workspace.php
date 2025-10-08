<?php
session_start();
// 役割が未設定なら選択画面へ（最初の1回だけ）
if (!isset($_SESSION['role'])) {
  header('Location: sentaku.html');
  exit;
}

// === PHPロジック ===
$wid = filter_input(INPUT_GET, 'workspace_id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
if (!$wid) { $wid = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]); }
$workspaceId = $wid ?: 1; // 開発用デフォルト

require_once __DIR__ . '/./localhost/db_open.php';
require_once __DIR__ . '/api/auth.php';  
$role = $ROLE ?? 'guest';

// セッションにワークスペースIDを保持（API からも参照可）
$_SESSION['workspace_id'] = $workspaceId;

// ワークスペース名取得
$stmt = $pdo->prepare('SELECT name FROM workspaces WHERE id = ?');
$stmt->execute([$workspaceId]);
$workspaceName = $stmt->fetchColumn();

// 見つからない場合
if ($workspaceName === false) {
  http_response_code(404);
  $workspaceName = '未設定ワークスペース';
}

// ログ（必要なら）
error_log('WSID=' . $workspaceId);
error_log('DB=' . $pdo->query('SELECT DATABASE()')->fetchColumn());
error_log('ROW=' . json_encode(['id' => $workspaceId, 'name' => $workspaceName], JSON_UNESCAPED_UNICODE));
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>企業学生間共有フォーム</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
  <!-- ワークスペース情報埋め込み -->
  <div id="app"
       data-workspace-id="<?= htmlspecialchars($workspaceId, ENT_QUOTES, 'UTF-8') ?>"
       data-role="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>"></div>

  <header style="display: flex; justify-content: space-between; align-items: center; background: #3f51b5; color: #fff; padding: 10px 20px; border-bottom: 2px solid #2c387e;">
  <div>
    <h1 style="margin: 0; font-size: 22px;">企業学生間共有フォーム</h1>
    <p style="margin: 4px 0 0; font-size: 14px;"><?= htmlspecialchars($workspaceName) ?>のワークスペース</p>
  </div>

  <div style="display: flex; align-items: center; gap: 16px; font-weight: 600; font-size: 14px;">
    <!-- ✅ トップへ戻るボタン -->
    <a href="/team4/website/toppage/top.php" style="background:#fff; color:#3f51b5; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:600; transition:background .2s;">
      ← トップへ戻る
    </a>

    <!-- ✅ 現在の役割表示 -->
    <?php
      if (!empty($_SESSION['role'])) {
        echo $_SESSION['role'] === 'student'
          ? '<span style="color:#90caf9;">学生として作業中です</span>'
          : ($_SESSION['role'] === 'company'
              ? '<span style="color:#a5d6a7;">企業として作業中です</span>'
              : '役割未設定');
      } else {
        echo '役割未設定';
      }
    ?>
  </div>
</header>



  <nav>
    <button data-page="home.html">ワークスペーストップ</button>
    <button data-page="question.php">質問フォーム</button>
    <button data-page="chat.html">チャットフォーム</button>
    <button data-page="task.html">課題提示フォーム</button>
    <button data-page="result.html">成果物提出フォーム</button>
    <button data-page="request.html">管理者への要望</button>
    <button data-page="announcement.html">お知らせ</button>
  </nav>

  <main id="main-content">
    <h2>ようこそ</h2>
    <p>メニューからページを選択してください。</p>
  </main>

  <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>

  <!-- JS読み込み -->
  <script src="js/frame.js"></script>
  <script src="js/chat.js"></script>
  <script src="js/room.js"></script>
  <script src="js/announcement_form.js"></script>
  <script src="js/announcement.js"></script>

  <!-- グローバル埋め込み -->
  <script>
    window.__WORKSPACE__ = {
      id: <?= (int)$workspaceId ?>,
      name: "<?= addslashes($workspaceName) ?>"
    };
    window.__USER__ = {
      role: document.getElementById('app')?.dataset?.role || 'guest'
    };
    localStorage.setItem("workspace_id", "<?= (int)$workspaceId ?>");
  </script>
</body>
</html>

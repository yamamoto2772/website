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
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { margin:0; font-family:sans-serif; display:grid; grid-template-areas:"header header" "sidebar main"; grid-template-columns:220px 1fr; grid-template-rows:50px 1fr; height:100vh; }
header { grid-area: header; background-color:#3f51b5; color:white; display:flex; justify-content:space-between; align-items:center; font-size:0.9em; height:50px; padding:0 1em; box-sizing:border-box; }
header h1 { margin:0; font-size:1.1em; }
header p { margin:0; font-size:0.8em; opacity:0.9; }
header .buttons a { margin-left:8px; padding:4px 8px; border-radius:4px; background:#ffffff33; border:1px solid #fff; color:white; text-decoration:none; font-size:0.8em; }
nav { grid-area: sidebar; background-color:#f0f0f0; padding:1em; border-right:1px solid #ccc; }
nav button { display:flex; align-items:center; width:100%; margin-bottom:10px; padding:10px; font-size:0.95em; background-color:#ddd; border:none; cursor:pointer; border-radius:4px; }
nav button i { margin-right:8px; font-size:1.1em; }
main { grid-area: main; overflow:hidden; }
iframe#main-frame { width:100%; height:100%; border:none; }
</style>
</head>
<body>

<header>
<div>
<h1>企業学生間共有フォーム</h1>
<p><?= htmlspecialchars($workspace['name']) ?> のワークスペース</p>
</div>
<div class="buttons">
<a href="top.php">トップに戻る</a>
<a href="workspace.php?id=<?= $workspace_id ?>">更新</a>
</div>
</header>

<nav>
<button onclick="loadPage('calendar.php?id=<?= $workspace_id ?>')"><i class="fa-solid fa-calendar"></i> マイルストーンカレンダー</button>
<button onclick="loadPage('question.php?id=<?= $workspace_id ?>')"><i class="fa-solid fa-question-circle"></i> 質問フォーム</button>
<button onclick="loadPage('chat.php?id=<?= $workspace_id ?>')"><i class="fa-solid fa-comments"></i> チャットフォーム</button>
<button onclick="loadPage('task.html')"><i class="fa-solid fa-tasks"></i> 課題提示フォーム</button>
<button onclick="loadPage('result.html')"><i class="fa-solid fa-upload"></i> 成果物提出フォーム</button>
<button onclick="loadPage('request.html')"><i class="fa-solid fa-envelope"></i> 管理者への要望</button>
</nav>

<main>
<iframe id="main-frame" src="calendar.php?id=<?= $workspace_id ?>"></iframe>
</main>

<script>
function loadPage(url){
    document.getElementById('main-frame').src = url;
}
</script>

</body>
</html>

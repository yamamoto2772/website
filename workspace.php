<?php
require_once 'config.php';

// ワークスペース名取得
$workspaceName = '未指定のワークスペース';
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT name FROM workspaces WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $workspaceName = htmlspecialchars($result['name'], ENT_QUOTES, 'UTF-8');
    } else {
        $workspaceName = '（存在しないワークスペース）';
    }
}

// マイルストーン全件取得
$milestoneStmt = $pdo->query("SELECT * FROM milestones ORDER BY 日付 ASC");
$allMilestones = $milestoneStmt->fetchAll(PDO::FETCH_ASSOC);

$year = date('Y');
$month = date('n');
$weekdays = ['日', '月', '火', '水', '木', '金', '土'];

function getMilestonesByDate($milestones, $date) {
    $list = [];
    foreach ($milestones as $m) {
        if ($m['日付'] === $date) {
            $list[] = $m;
        }
    }
    return $list;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8" />
  <title>企業学生間共有フォーム</title>
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
  padding: 0.5em 1em; /* 上下のpaddingを減らす */
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9em; /* フォントサイズを少し小さく */
}

    nav {
      grid-area: sidebar;
      background-color: #f8f9ff;
      padding: 1.5em 1em;
      border-right: 2px solid #3f51b5;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .button{
      background-color: #3f51b5;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 12px 20px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.25s ease;
      text-align: left;
    }
    nav button {
      background-color: #3f51b5;
      color: white;
      border: none;
      border-radius: 6px;
      padding: 12px 20px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.25s ease;
      text-align: left;
    }
    nav button:hover,
    nav button:focus {
      background-color: #2c387e;
      outline: none;
      box-shadow: 0 0 8px rgba(63, 81, 181, 0.7);
    }
    main {
      grid-area: main;
      padding: 1em;
      overflow-y: auto;
    }
    .view { display: none; }
    .view.active { display: block; }

    .month-calendar {
      border: 1px solid #ccc;
      border-radius: 8px;
      max-width: 720px;
      padding: 8px;
      background: #f9f9f9;
      margin: 0 auto;
    }
    .month-title {
      text-align: center;
      font-weight: bold;
      margin-bottom: 6px;
      font-size: 1.2em;
    }
    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 2px;
    }
    .calendar-header, .calendar-day {
      text-align: center;
      padding: 4px;
      border: 1px solid #ccc;
    }
    .calendar-day {
      min-height: 80px;
      font-size: 0.75em;
      position: relative;
    }
    .calendar-day.outside-month { background-color: #f0f0f0; color: #999; }
    .calendar-day strong { display: block; margin-bottom: 4px; }
    .milestone-item { font-size: 0.7em; margin: 2px; background: #e0e0ff; padding: 2px 4px; border-radius: 4px; }

    .milestone-view {
      border-top: 4px solid black;
      height: 200px;
      margin: 40px auto;
      position: relative;
      max-width: 720px;
    }
    .milestone-point {
      position: absolute;
      width: 120px;
      text-align: center;
    }
    .milestone-point .box {
      width: 100px;
      height: 40px;
      background: #e0f7fa;
      border: 1px solid #333;
      margin-bottom: 10px;
    }
    .milestone-point .line {
      width: 2px;
      height: 40px;
      background: #000;
      margin: 0 auto;
    }
    .milestone-table {
      margin: 30px auto;
      width: 90%;
      border-collapse: collapse;
    }
    .milestone-table th, .milestone-table td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
    }
    .toggle-buttons {
      text-align: center;
      margin-bottom: 20px;
    }
    .toggle-buttons button {
      margin: 0 10px;
      padding: 10px 20px;
    }
  </style>
  <script>
    function toggleView(viewId) {
      document.querySelectorAll('.view').forEach(view => view.classList.remove('active'));
      document.getElementById(viewId).classList.add('active');
    }
  </script>
</head>
<body>

<header>
  <div>
    <h1>企業学生間共有フォーム</h1>
    <p><?= $workspaceName ?> のワークスペース</p>
  </div>
</header>

<nav>
  <button onclick="location.href='workspace.php?id=<?= htmlspecialchars($_GET['id'] ?? 0) ?>'">ワークスペーストップ</button>
  <button onclick="alert('質問フォームへ遷移')">質問フォーム</button>
  <button onclick="alert('チャットフォームへ遷移')">チャットフォーム</button>
  <button onclick="alert('課題提示フォームへ遷移')">課題提示フォーム</button>
  <button onclick="alert('成果物提出フォームへ遷移')">成果物提出フォーム</button>
  <button onclick="alert('管理者への要望へ遷移')">管理者への要望</button>
  <a class="button" href="index.php">戻る</a>
</nav>

<main>
  <div class="toggle-buttons">
    <button onclick="toggleView('calendar')">カレンダー表示</button>
    <button onclick="toggleView('milestones')">マイルストーン表示</button>
  </div>

  <section id="calendar" class="view active">
    <div class='month-calendar'>
      <div class='month-title'><?= $year ?>年<?= $month ?>月</div>
      <div class='calendar-grid'>
        <?php foreach ($weekdays as $wd): ?>
          <div class='calendar-header'><?= $wd ?></div>
        <?php endforeach; ?>
        <?php
        $firstDay = strtotime("$year-$month-01");
        $startWeekday = (int)date('w', $firstDay);
        $daysInMonth = date('t', $firstDay);
        $totalCells = ceil(($startWeekday + $daysInMonth) / 7) * 7;
        $calendarStart = strtotime("-$startWeekday days", $firstDay);

        for ($i = 0; $i < $totalCells; $i++) {
          $current = strtotime("+{$i} days", $calendarStart);
          $dateStr = date('Y-m-d', $current);
          $dayNum = (int)date('j', $current);
          $isCurrentMonth = (date('Y-m', $current) === sprintf('%04d-%02d', $year, $month));
          $classes = 'calendar-day' . (!$isCurrentMonth ? ' outside-month' : '');
          echo "<div class='{$classes}'><strong>{$dayNum}</strong>";
          $miles = getMilestonesByDate($allMilestones, $dateStr);
          foreach ($miles as $mile) {
            $title = htmlspecialchars($mile['タイトル']);
            echo "<div class='milestone-item'>{$title}</div>";
          }
          echo "</div>";
        }
        ?>
      </div>
    </div>
  </section>

  <section id="milestones" class="view">
    <div class="milestone-view">
      <div class="milestone-point" style="left: 10%">
        <div class="box">マイルストーン1</div>
        <div class="line"></div>
      </div>
      <div class="milestone-point" style="left: 40%">
        <div class="box">マイルストーン2</div>
        <div class="line"></div>
      </div>
      <div class="milestone-point" style="left: 70%">
        <div class="box">マイルストーン3</div>
        <div class="line"></div>
      </div>
    </div>
    <table class="milestone-table">
      <tr><th>日付</th><th>マイルストーン</th><th>高さと方向</th><th>担当者</th><th>ステータス</th></tr>
      <tr><td>1/26</td><td>マイルストーン1</td><td>+5</td><td>山田太郎</td><td>準備中</td></tr>
    </table>
  </section>
</main>

</body>
</html>
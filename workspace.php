<?php
require_once 'config.php';

// ワークスペース名取得
$workspaceName = '未指定のワークスペース';
$workspaceId = 0;
if (isset($_GET['id'])) {
    $workspaceId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT name FROM workspaces WHERE id = :id");
    $stmt->bindParam(':id', $workspaceId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $workspaceName = $result['name'];
    }
}

// 今の年月
$year = date('Y');
$month = date('n');
$weekdays = ['日', '月', '火', '水', '木', '金', '土'];

// milestones 全件取得（workspace_id はないので全件取得）
$milestoneStmt = $pdo->query("SELECT * FROM milestones ORDER BY 日付");
$allMilestones = $milestoneStmt->fetchAll(PDO::FETCH_ASSOC);

// 日付で絞り込む関数
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
  <title>企業学生間共有フォーム - <?= htmlspecialchars($workspaceName) ?></title>
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
      padding: 0.5em 1em;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.9em;
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
    nav button, .button {
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
    nav button:focus,
    .button:hover,
    .button:focus {
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
      cursor: pointer;
      background: white;
    }
    .calendar-day.outside-month { background-color: #f0f0f0; color: #999; }
    .calendar-day strong { display: block; margin-bottom: 4px; }
    .milestone-item {
      font-size: 0.7em;
      margin: 2px;
      background: #e0e0ff;
      padding: 2px 4px;
      border-radius: 4px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      cursor: default;
    }

    .milestone-table {
      margin: 30px auto;
      width: 90%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
    }
    .milestone-table th, .milestone-table td {
      border: 1px solid #ccc;
      padding: 8px;
      text-align: center;
      font-size: 0.9em;
    }
    .toggle-buttons {
      text-align: center;
      margin-bottom: 20px;
    }
    .toggle-buttons button {
      margin: 0 10px;
      padding: 10px 20px;
      cursor: pointer;
    }
  </style>
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

    //スクロール関連
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
    function toggleView(viewId) {
      document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
      document.getElementById(viewId).classList.add('active');
    }

    function onDateClick(date) {
      toggleView('milestones');

      const rows = document.querySelectorAll('.milestone-row');
      rows.forEach(row => {
        row.style.display = row.dataset.date === date ? '' : 'none';
      });

      document.getElementById('milestone-title').textContent = date + ' のマイルストーン';
    }

    function showAllMilestones() {
      toggleView('milestones');
      const rows = document.querySelectorAll('.milestone-row');
      rows.forEach(row => row.style.display = '');
      document.getElementById('milestone-title').textContent = 'すべてのマイルストーン';
    }
  </script>
</head>
<body>

<header>
  <div>
    <h1>企業学生間共有フォーム</h1>
    <p><?= htmlspecialchars($workspaceName) ?> のワークスペース</p>
  </div>
</header>

<nav>
  <button onclick="location.href='workspace.php?id=<?= htmlspecialchars($workspaceId) ?>'">ワークスペーストップ</button>
  <button onclick="loadPage('question.php')">質問フォーム</button>
  <button onclick="alert('チャットフォームへ遷移')">チャットフォーム</button>
  <button onclick="alert('課題提示フォームへ遷移')">課題提示フォーム</button>
  <button onclick="alert('成果物提出フォームへ遷移')">成果物提出フォーム</button>
  <button onclick="alert('管理者への要望へ遷移')">管理者への要望</button>
  <a class="button" href="index.php">戻る</a>
</nav>

<main>
  <div class="toggle-buttons">
    <button onclick="toggleView('calendar')">カレンダー表示</button>
    <button onclick="showAllMilestones()">マイルストーン表示</button>
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
          echo "<div class='{$classes}' onclick=\"onDateClick('{$dateStr}')\"><strong>{$dayNum}</strong>";

          $miles = getMilestonesByDate($allMilestones, $dateStr);
          foreach ($miles as $mile) {
            $title = htmlspecialchars($mile['タイトル']);
            echo "<div class='milestone-item' title='{$title}'>{$title}</div>";
          }
          echo "</div>";
        }
        ?>
      </div>
    </div>
  </section>
  <main id="main-content">
    <h2></h2>
    <p></p>
  </main>
  <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>


  <section id="milestones" class="view">
    <h2 id="milestone-title">すべてのマイルストーン</h2>
    <table class="milestone-table">
      <thead>
        <tr>
          <th>日付</th>
          <th>タイトル</th>
          <th>詳細</th>
          <th>マイルストーンID</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allMilestones as $ms): ?>
          <tr class="milestone-row" data-date="<?= htmlspecialchars($ms['日付']) ?>">
            <td><?= htmlspecialchars($ms['日付']) ?></td>
            <td><?= htmlspecialchars($ms['タイトル']) ?></td>
            <td><?= htmlspecialchars($ms['詳細']) ?></td>
            <td><?= htmlspecialchars($ms['マイルストーンID']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>

</body>
</html>
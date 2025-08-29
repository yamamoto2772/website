<?php
// localhost/db_open.php を読み込む
// データベース接続を行うためのファイル
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

// 今の年月
$year = date('Y');
$month = date('n');
$weekdays = ['日', '月', '火', '水', '木', '金', '土'];

// milestones 全件取得
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

    /* カレンダーとマイルストーンのスタイル */
    .view { display: none; }
    .view.active { display: block; }
    .month-calendar {
      border: 1px solid #ccc;
      border-radius: 8px;
      max-width: 720px;
      padding: 8px;
      background: #f9f9f9;
      margin: 0 auto 20px;
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
    <button onclick="toggleView('calendar')">マイルストーンカレンダー</button>
    <button onclick="loadPage('question.php?id=<?= htmlspecialchars($workspace_id) ?>')">質問フォーム</button>
    <button onclick="loadPage('chat.html')">チャットフォーム</button>
    <button onclick="loadPage('task.html')">課題提示フォーム</button>
    <button onclick="loadPage('result.html')">成果物提出フォーム</button>
    <button onclick="loadPage('request.html')">管理者への要望</button>
  </nav>

  <main id="main-content">
    <p>読み込みを行うにはサイドバーのボタンを押してください。</p>

    <section id="calendar" class="view active">
        <div class='month-calendar'>
            <div class='month-title'><?= htmlspecialchars($year) ?>年<?= htmlspecialchars($month) ?>月</div>
            <div class='calendar-grid'>
                <?php
                $firstDay = strtotime("$year-$month-01");
                $startWeekday = (int)date('w', $firstDay);
                $daysInMonth = date('t', $firstDay);
                $totalCells = ceil(($startWeekday + $daysInMonth) / 7) * 7;
                $calendarStart = strtotime("-$startWeekday days", $firstDay);

                foreach ($weekdays as $wd): ?>
                    <div class='calendar-header'><?= htmlspecialchars($wd) ?></div>
                <?php endforeach;

                for ($i = 0; $i < $totalCells; $i++) {
                    $current = strtotime("+{$i} days", $calendarStart);
                    $dateStr = date('Y-m-d', $current);
                    $dayNum = (int)date('j', $current);
                    $isCurrentMonth = (date('Y-m', $current) === sprintf('%04d-%02d', $year, $month));
                    $classes = 'calendar-day' . (!$isCurrentMonth ? ' outside-month' : '');

                    $sanitizedDateStr = htmlspecialchars($dateStr, ENT_QUOTES, 'UTF-8');
                    echo "<div class='{$classes}' onclick=\"onDateClick('{$sanitizedDateStr}')\"><strong>{$dayNum}</strong>";

                    $miles = getMilestonesByDate($allMilestones, $dateStr);
                    foreach ($miles as $mile) {
                        $title = htmlspecialchars($mile['タイトル'], ENT_QUOTES, 'UTF-8');
                        echo "<div class='milestone-item' title='{$title}'>{$title}</div>";
                    }
                    echo "</div>";
                }
                ?>
            </div>
        </div>
        <button onclick="showAllMilestones()">すべてのマイルストーンを表示</button>
    </section>

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

  <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>

  <script>
    function toggleView(viewId) {
        document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
        document.getElementById(viewId).classList.add('active');
    }

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

    // 初期表示をカレンダーにする
    document.addEventListener('DOMContentLoaded', () => {
        toggleView('calendar');
    });
  </script>
</body>
</html>
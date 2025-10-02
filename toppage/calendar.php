<?php
require_once("../localhost/db_open.php");

$workspaceId = $_GET['id'] ?? '';
if (!$workspaceId || !ctype_digit($workspaceId)) {
    exit("不正なワークスペースIDです");
}

// ワークスペース情報取得
$stmt = $pdo->prepare("SELECT * FROM workspaces WHERE workspaces_id=?");
$stmt->execute([$workspaceId]);
$workspace = $stmt->fetch();
if (!$workspace) exit("ワークスペースが見つかりません。");

// カレンダー用の日付
$year = date('Y');
$month = date('n');
$weekdays = ['日','月','火','水','木','金','土'];

// カレンダー予定取得（ワークスペースごとに紐づく対象が workspaceId のもの）
$milestoneStmt = $pdo->prepare("SELECT * FROM calendar WHERE 紐づく対象=? ORDER BY 実施日_締切日");
$milestoneStmt->execute([$workspaceId]);
$allMilestones = $milestoneStmt->fetchAll(PDO::FETCH_ASSOC);

// 日付ごとに予定をまとめる
function getMilestonesByDate($milestones, $date) {
    $list = [];
    foreach ($milestones as $m) {
        if ($m['実施日_締切日'] === $date) $list[] = $m;
    }
    return $list;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($workspace['name']) ?> | マイルストーンカレンダー</title>
<style>
body { font-family:sans-serif; margin:0; padding:0; }
.container { max-width:800px; margin:0 auto; padding:20px; }
.month-calendar { border:1px solid #ccc; border-radius:8px; padding:8px; background:#f9f9f9; }
.month-title { text-align:center; font-weight:bold; margin-bottom:6px; font-size:1.2em; }
.calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.calendar-header, .calendar-day { text-align:center; padding:4px; border:1px solid #ccc; }
.calendar-day { min-height:80px; font-size:0.75em; position:relative; cursor:pointer; background:white; }
.calendar-day.outside-month { background:#f0f0f0; color:#999; }
.calendar-day strong { display:block; margin-bottom:4px; }
.milestone-item { font-size:0.7em; margin:2px; background:#e0e0ff; padding:2px 4px; border-radius:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; cursor:default; }
</style>
</head>
<body>
<div class="container">
    

    <div class="month-calendar">
        <div class="month-title"><?= $year ?>年<?= $month ?>月</div>
        <div class="calendar-grid">
            <?php foreach($weekdays as $wd) echo "<div class='calendar-header'>{$wd}</div>"; ?>

            <?php
            $firstDay = strtotime("$year-$month-01");
            $startWeekday = (int)date('w', $firstDay);
            $daysInMonth = date('t', $firstDay);
            $totalCells = ceil(($startWeekday + $daysInMonth)/7)*7;
            $calendarStart = strtotime("-$startWeekday days", $firstDay);

            for($i=0;$i<$totalCells;$i++){
                $current = strtotime("+{$i} days", $calendarStart);
                $dateStr = date('Y-m-d', $current);
                $dayNum = (int)date('j', $current);
                $isCurrentMonth = (date('Y-m',$current) === sprintf('%04d-%02d',$year,$month));
                $classes = 'calendar-day'.(!$isCurrentMonth?' outside-month':'');
                echo "<div class='{$classes}'><strong>{$dayNum}</strong>";
                $miles = getMilestonesByDate($allMilestones,$dateStr);
                foreach($miles as $mile){
                    $title = htmlspecialchars($mile['予定タイトル'],ENT_QUOTES,'UTF-8');
                    echo "<div class='milestone-item' title='{$title}'>{$title}</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>

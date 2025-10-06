<?php
require_once("../localhost/db_open.php");

// POSTデータの受け取り
$title       = $_POST['title'] ?? '';
$content     = $_POST['content'] ?? '';
$type        = $_POST['type'] ?? '';
$date        = $_POST['date'] ?? '';
$workspaceId = $_POST['workspaceId'] ?? '';

if (!$title || !$date || !$workspaceId) {
    exit("入力が不足しています。");
}

// 登録日時を現在時刻に設定
$now = date("Y-m-d H:i:s");

// 一意な予定IDを生成
$yoteiId = uniqid("yotei-");

// INSERT文
$sql = "INSERT INTO calendar 
(`予定ID`, `予定タイトル`, `内容`, `種別`, `紐づく対象`, `紐づくレコードID`, `実施日_締切日`, `登録日時`)
VALUES (?, ?, ?, ?, ?, NULL, ?, ?)";

$stmt = $pdo->prepare($sql);
$result = $stmt->execute([
    $yoteiId,
    $title,
    $content,
    $type,
    $workspaceId,
    $date,
    $now
]);

if ($result) {
    // 成功したら元のカレンダーページに戻す
    header("Location: calendar.php?id=" . urlencode($workspaceId));
    exit;
} else {
    exit("登録に失敗しました。");
}

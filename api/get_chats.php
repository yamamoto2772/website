<?php
require_once("../../localhost/db_open.php");

$questionId = $_GET['質問ID'] ?? '';
if(!$questionId){ echo json_encode([]); exit; }

$stmt = $pdo->prepare("SELECT * FROM message WHERE チャットルームID=? ORDER BY 作成日時 ASC");
$stmt->execute([$questionId]);
$chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($chats, JSON_UNESCAPED_UNICODE);

<?php
require_once("../../localhost/db_open.php");

$questionId = $_POST['質問ID'] ?? '';
$user = $_POST['user'] ?? '匿名';
$message = $_POST['message'] ?? '';

if(!$questionId || !$message){
    echo json_encode(['success'=>false,'message'=>'パラメータ不足']);
    exit;
}

$chatId = uniqid(); // チャットID自動生成

$stmt = $pdo->prepare("INSERT INTO message (チャットID, チャットルームID, 送信者識別, メッセージ, 作成日時) VALUES (?, ?, ?, ?, NOW())");
$res = $stmt->execute([$chatId, $questionId, $user, $message]);

echo json_encode(['success'=>$res]);

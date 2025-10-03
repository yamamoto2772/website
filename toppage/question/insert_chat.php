<?php
// データベース接続情報を読み込み
require_once("../localhost/db_open.php");

// POSTリクエストかどうかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTされたデータを取得
    $question_id = $_POST['question_id'];
    $sender_id = $_POST['sender_id'];
    $message = $_POST['message'];

    // 一意のチャットIDを生成（`chat_` + 8桁のランダムな英数字）
    $chat_id = 'chat_' . substr(md5(uniqid(rand(), true)), 0, 8);
    $image_path = ''; // 画像は現在未実装のため空文字列

    try {
        // SQLクエリを準備
        $sql = "INSERT INTO `message` (`チャットID`, `チャットルームID`, `送信者識別`, `メッセージ`, `画像`, `作成日時`) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        
        // データをバインドして実行
        $stmt->execute([$chat_id, $question_id, $sender_id, $message, $image_path]);

        // 成功した場合はJSONで応答
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        // 失敗した場合はエラーメッセージをJSONで応答
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    // POST以外のリクエストの場合はエラーを返す
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => '無効なリクエストです。']);
}
?>

<?php
// データベース接続ファイルのパスを調整
require_once('../../localhost/db_open.php');

// JSON形式でレスポンスを返すことを指定
header('Content-Type: application/json');

// POSTリクエストかどうかを確認
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['質問ID'])) {
    $questionId = $_POST['質問ID'];

    try {
        // プリペアドステートメントを使用してSQLインジェクションを防ぐ
        $stmt = $pdo->prepare("DELETE FROM questions WHERE `質問ID` = ?");
        $stmt->execute([$questionId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => '質問が正常に削除されました。']);
        } else {
            echo json_encode(['success' => false, 'message' => '指定された質問が見つかりませんでした。']);
        }
    } catch (PDOException $e) {
        http_response_code(500); // サーバーエラー
        echo json_encode(['success' => false, 'message' => 'データベースエラー: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => '無効なリクエストです。']);
}
?>
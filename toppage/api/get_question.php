<?php
require_once("../../localhost/db_open.php");

// workspace_id と question_id を取得
$workspaceId = $_GET['id'] ?? null;
$questionId = $_GET['qid'] ?? null;

if (!$workspaceId || !$questionId) {
    http_response_code(400);
    echo json_encode(['error' => 'IDが指定されていません']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE 質問ID=? AND workspaces_id=?");
    $stmt->execute([$questionId, $workspaceId]);
    $question = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$question) {
        echo json_encode(['error' => '質問が見つかりません']);
        exit();
    }

    echo json_encode([
        'タイトル' => $question['タイトル'],
        '内容' => $question['内容'],
        '画像' => $question['画像'],
        '質問者' => $question['質問者識別'],
        '作成日時' => $question['作成日時']
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'データベースエラー: '.$e->getMessage()]);
}
?>

<?php
// PHP_EOL の代わりに "\n" を使用
require_once("../localhost/db_open.php");

// ワークスペースIDの取得とチェック
$workspaceId = $_GET['id'] ?? '';
if(!$workspaceId){
    echo "<p>ワークスペースIDが指定されていません。</p>";
    return;
}

// 投稿後のステータスとメッセージを GET パラメータから取得
$status = $_GET['status'] ?? '';
$msg = $_GET['msg'] ?? '';

// 質問一覧取得
// 【修正完了】ワークスペースIDが 'id' カラムに格納されていると確定
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id=? ORDER BY 作成日時 DESC");
$stmt->execute([$workspaceId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
/* CSSは変更ありませんが、見やすいように一部省略 */
.container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; }
/* メッセージ表示用のスタイル */
.message { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: 600; border: 1px solid;}
.message.success-message { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
.message.error-message { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb;}

.post-button {
    display: inline-block; margin-bottom: 20px; padding: 10px 16px;
    background-color: #007bff; color: white; border-radius: 6px; border: none;
    cursor: pointer; text-decoration: none; font-weight: bold;
}
.post-button:hover { background-color: #0056b3; }
.question-list { padding: 0; }
.question-item { padding: 15px; margin-bottom: 20px; border-left: 5px solid #007bff; background:#e9e9e9; border-radius:8px; overflow:hidden; position:relative; }
.chat-container { display:none; margin-top:10px; background:#fff; border-radius:8px; padding:15px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
.chat-messages { max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom:10px; border-radius:5px; background:#f9f9f9; }
.question-image { max-width: 100%; height: auto; margin-top: 10px; border: 1px solid #ddd; }
.delete-button { 
    float: right; 
    background-color: #dc3545; 
    color: white; 
    border: none; 
    padding: 5px 10px; 
    border-radius: 4px; 
    cursor: pointer; 
    margin-left: 10px; 
}
.toggle-detail {
    float: right;
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<div class="container" data-workspace-id="<?= htmlspecialchars($workspaceId) ?>">
    
    <?php if ($status === 'success' && $msg): ?>
        <div class="message success-message"><?= htmlspecialchars(urldecode($msg)) ?></div>
    <?php elseif ($status === 'error' && $msg): ?>
        <div class="message error-message"><?= htmlspecialchars(urldecode($msg)) ?></div>
    <?php endif; ?>
    <button class="post-button" onclick="loadPage('insert_question.php?id=<?= htmlspecialchars($workspaceId) ?>')">質問を投稿する</button>

    <div class="question-list" id="question-list">
        <?php if(count($questions) === 0): ?>
            <p>まだ質問はありません。</p>
        <?php else: ?>
            <?php foreach($questions as $q): ?>
                <div class="question-item" data-id="<?= htmlspecialchars($q['質問ID']) ?>">
                    <button class="delete-button" onclick="deleteQuestion(<?= htmlspecialchars($q['質問ID']) ?>)">削除</button>
                    <button class="toggle-detail" onclick="toggleDetail(<?= htmlspecialchars($q['質問ID']) ?>)">詳細/チャットを表示</button>
                    
                    <h2><?= htmlspecialchars($q['タイトル']) ?></h2>
                    <p><strong>質問者:</strong> <?= htmlspecialchars($q['質問者識別']) ?> | <strong>作成日時:</strong> <?= htmlspecialchars($q['作成日時']) ?></p>
                    
                    <div class="question-detail chat-container" id="detail-<?= htmlspecialchars($q['質問ID']) ?>">
                        <p><strong>内容:</strong> <?= nl2br(htmlspecialchars($q['内容'])) ?></p>
                        <?php if($q['画像']): ?>
                            <img src="<?= htmlspecialchars($q['画像']) ?>" alt="質問画像" class="question-image">
                        <?php endif; ?>

                        <div class="chat-messages" id="chat-messages-<?= htmlspecialchars($q['質問ID']) ?>">チャットを読み込み中...</div>
                        <textarea class="chat-text" id="chat-text-<?= htmlspecialchars($q['質問ID']) ?>" placeholder="メッセージを入力"></textarea>
                        <button class="chat-send" onclick="sendChat(<?= htmlspecialchars($q['質問ID']) ?>)">送信</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
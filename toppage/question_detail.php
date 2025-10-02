<?php
require_once("../localhost/db_open.php");

if (!isset($_GET['qid']) || !isset($_GET['id'])) {
    echo "<p>質問IDまたはワークスペースIDが指定されていません。</p>";
    exit;
}

$question_id = $_GET['qid'];
$workspace_id = $_GET['id'];
?>

<style>
/* 質問詳細ページのスタイル */
.question_detail_container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.chat-container {
    border-top: 1px solid #ddd;
    margin-top: 20px;
    padding-top: 20px;
}
.chat-messages {
    height: 300px;
    overflow-y: auto;
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    flex-direction: column; /* メッセージを上から下へ並べる */
}
.chat-message {
    margin-bottom: 10px;
}
.chat-input {
    display: flex;
}
.chat-input input[type="text"] {
    flex-grow: 1;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
.chat-input button {
    margin-left: 10px;
    padding: 8px 15px;
    background-color: #4a68d1;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<div class="question_detail_container">
    <button onclick="loadPage('question.php?id=<?= htmlspecialchars($workspace_id) ?>')">質問一覧に戻る</button>
    <hr>
    <?php
    // 質問詳細の取得
    try {
        // 修正箇所: SQLクエリにworkspaces_idを追加
        $sql = "SELECT * FROM questions WHERE 質問ID = ? AND workspaces_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$question_id, $workspace_id]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($question) {
            echo "<h2>" . htmlspecialchars($question['タイトル']) . "</h2>";
            echo "<p>" . nl2br(htmlspecialchars($question['内容'])) . "</p>";
            echo "<small>作成日時: " . htmlspecialchars($question['作成日時']) . "</small>";
        } else {
            echo "<p>質問が見つかりませんでした。</p>";
        }
    } catch (PDOException $e) {
        echo "取得エラー: " . htmlspecialchars($e->getMessage());
    }
    ?>

    <div class="chat-container">
        <h3>チャット</h3>
        <div id="chat-messages" class="chat-messages">
            </div>
        <div class="chat-input">
            <input type="text" id="chat-message-input" placeholder="メッセージを入力...">
            <button id="chat-send-button">送信</button>
        </div>
    </div>
</div>

<script>
    const questionId = <?= json_encode($question_id) ?>;
    const chatMessagesDiv = document.getElementById('chat-messages');
    const chatMessageInput = document.getElementById('chat-message-input');
    const chatSendButton = document.getElementById('chat-send-button');

    // ユーザーIDは仮で 'user1' とします。
    const userId = 'user1';

    // メッセージ取得関数
    const fetchMessages = async () => {
        try {
            const response = await fetch(`fetch_messages.php?qid=${encodeURIComponent(questionId)}`);
            const messages = await response.json();
            
            // チャットエリアをクリア
            chatMessagesDiv.innerHTML = '';
            
            // 新しいメッセージを追加
            messages.forEach(msg => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('chat-message');
                messageDiv.innerHTML = `<strong>${escapeHtml(msg.sender_id)}:</strong> ${escapeHtml(msg.message)}`;
                chatMessagesDiv.appendChild(messageDiv);
            });

            // チャットエリアを一番下までスクロール
            chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight;
        } catch (error) {
            console.error('メッセージの取得に失敗しました:', error);
        }
    };
    
    // HTMLエスケープ関数
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // メッセージ送信関数
    const sendMessage = async () => {
        const message = chatMessageInput.value.trim();
        if (message === '') return;

        const formData = new FormData();
        formData.append('question_id', questionId);
        formData.append('sender_id', userId);
        formData.append('message', message);

        try {
            const response = await fetch('insert_chat.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                chatMessageInput.value = '';
                fetchMessages(); // メッセージ送信後に再取得
            } else {
                console.error('メッセージの送信に失敗しました: ' + result.error);
            }
        } catch (error) {
            console.error('Fetch Error:', error);
        }
    };

    chatSendButton.addEventListener('click', sendMessage);
    chatMessageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // 1秒ごとにメッセージを自動で取得
    setInterval(fetchMessages, 1000);

    // ページロード時に初回メッセージを取得
    fetchMessages();
</script>
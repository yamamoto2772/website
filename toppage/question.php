<?php
// PHP_EOL の代わりに "\n" を使用
require_once("../localhost/db_open.php");

// ワークスペースIDの取得とチェック
$workspaceId = $_GET['id'] ?? '';
if(!$workspaceId){
    // 親ページ（workspace.php）の #main-content に読み込まれることを想定
    echo "<p>ワークスペースIDが指定されていません。</p>";
    return;
}

// 質問一覧取得
// SQLインジェクション対策済み (PDO プリペアドステートメント)
// ワークスペース識別子を id に統一
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id=? ORDER BY 作成日時 DESC");
$stmt->execute([$workspaceId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
/* CSSは変更ありませんが、見やすいように一部省略 */
.container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; }
.post-button {
    display: inline-block; margin-bottom: 20px; padding: 10px 16px;
    background-color: #007bff; color: white; border-radius: 6px; border: none;
    cursor: pointer; text-decoration: none; font-weight: bold;
}
.post-button:hover { background-color: #0056b3; }
.question-list { padding: 0; }
.question-item { padding: 15px; margin-bottom: 20px; border-left: 5px solid #007bff; background:#e9e9e9; border-radius:8px; overflow:hidden; position:relative; }
/* ... その他のスタイル定義 ... */
.chat-container { display:none; margin-top:10px; background:#fff; border-radius:8px; padding:15px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
.chat-messages { max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom:10px; border-radius:5px; background:#f9f9f9; }
</style>

<div class="container">
    <button class="post-button" onclick="loadPage('insert_question.php?id=<?= htmlspecialchars($workspaceId) ?>')">質問を投稿する</button>

    <div class="question-list" id="question-list">
        <?php if(count($questions) === 0): ?>
            <p>まだ質問はありません。</p>
        <?php else: ?>
            <?php foreach($questions as $q): ?>
                <div class="question-item" data-id="<?= htmlspecialchars($q['質問ID']) ?>">
                    <h2><?= htmlspecialchars($q['タイトル']) ?></h2>
                    <p><strong>質問者:</strong> <?= htmlspecialchars($q['質問者識別']) ?> | <strong>作成日時:</strong> <?= htmlspecialchars($q['作成日時']) ?></p>
                    
                    <button class="delete-button" onclick="deleteQuestion(<?= htmlspecialchars($q['質問ID']) ?>)">削除</button>
                    <button class="toggle-detail" onclick="toggleDetail(<?= htmlspecialchars($q['質問ID']) ?>)">詳細/チャットを表示</button>

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

<script>
// PHP変数をJavaScript変数として安全に渡す（addslashesでエスケープ）
const workspaceId = '<?= addslashes($workspaceId) ?>';
let chatIntervals = {};

// 質問削除
function deleteQuestion(id){
    if(!confirm('本当にこの質問を削除しますか？')) return;
    
    // 【重要】api/delete_question.php で、このユーザーに削除権限があるかチェックしてください
    fetch(`api/delete_question.php`,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        // ワークスペース識別子を id に統一
        body:`質問ID=${id}&id=${workspaceId}`
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            // 画面全体を再読み込みせず、該当の質問要素を削除
            const item = document.querySelector(`.question-item[data-id="${id}"]`);
            if(item) item.remove();
        } else {
            alert('削除に失敗しました: '+(data.message??''));
        }
    })
    .catch(err=>{ console.error(err); alert('削除中にエラーが発生しました'); });
}

// 質問詳細＋チャットの表示切替
function toggleDetail(qid){
    const container = document.getElementById('detail-'+qid);
    if(container.style.display==='block'){
        container.style.display='none';
        clearInterval(chatIntervals[qid]);
    } else {
        container.style.display='block';
        loadChat(qid);
        chatIntervals[qid] = setInterval(()=>loadChat(qid),5000);
    }
}

// チャット読み込み
function loadChat(qid){
    fetch(`api/get_chats.php?質問ID=${qid}`)
        .then(res=>res.json())
        .then(data=>{
            const chatDiv = document.getElementById('chat-messages-'+qid);
            chatDiv.innerHTML='';
            if(data.length===0){
                chatDiv.innerHTML='<p>まだメッセージはありません。</p>';
                return;
            }
            data.forEach(c=>{
                const p=document.createElement('p');
                const sender = document.createElement('strong');
                
                // XSS対策 - メッセージを textContent で安全に挿入
                sender.textContent = c.送信者識別 + ': ';
                p.textContent = c.メッセージ;
                
                p.prepend(sender);
                chatDiv.appendChild(p);
            });
            chatDiv.scrollTop = chatDiv.scrollHeight;
        })
        .catch(err=>{
            console.error(err);
            document.getElementById('chat-messages-'+qid).innerHTML='<p>チャットの取得に失敗しました</p>';
        });
}

// チャット送信
function sendChat(qid){
    const textEl = document.getElementById('chat-text-'+qid);
    const msg = textEl.value.trim();
    if(!msg) return alert('メッセージを入力してください');

    const formData = new URLSearchParams();
    formData.append('質問ID', qid);
    formData.append('message', msg);
    // 【重要】api/send_chat.php で、セッションから送信者を特定し、formData に依存しないようにしてください

    fetch('api/send_chat.php',{
        method:'POST',
        body: formData
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            textEl.value='';
            loadChat(qid);
        } else {
            alert('送信失敗: '+(data.message??''));
        }
    })
    .catch(err=>{
        console.error(err);
        alert('送信中にエラーが発生しました');
    });
}

// 質問一覧更新用（今回は削除時の最適化を行ったため、この関数は事実上不要だが残しておく）
function loadQuestions(){
    // 親ページで定義された loadPage() 関数を呼び出す
    if(typeof loadPage === 'function'){
        loadPage(`question.php?id=${workspaceId}`);
    } else {
        // loadPage が未定義の場合は、DOM操作での更新を試みるか、または何もしない
        console.warn("loadPage 関数が定義されていません。手動で画面を更新してください。");
    }
}
</script>
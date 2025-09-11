<?php
require_once("../localhost/db_open.php");

$workspaceId = $_GET['id'] ?? '';
if(!$workspaceId){
    echo "<p>ワークスペースIDが指定されていません。</p>";
    return;
}

// 質問一覧取得
$stmt = $pdo->prepare("SELECT * FROM questions WHERE workspaces_id=? ORDER BY 作成日時 DESC");
$stmt->execute([$workspaceId]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
.container { max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif; }
.post-button {
    display: inline-block; margin-bottom: 20px; padding: 10px 16px;
    background-color: #007bff; color: white; border-radius: 6px; border: none;
    cursor: pointer; text-decoration: none; font-weight: bold;
}
.post-button:hover { background-color: #0056b3; }
.question-list { padding: 0; }
.question-item { padding: 15px; margin-bottom: 20px; border-left: 5px solid #007bff; background:#e9e9e9; border-radius:8px; overflow:hidden; position:relative; }
.question-item h2 { margin-top:0; font-size:1.2em; }
.question-item p { margin:5px 0; }
.delete-button { background-color:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; float:right; margin-top:5px; font-size:0.9em; }
.delete-button:hover { background-color:#c82333; }
.toggle-detail { background:#17a2b8; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:0.9em; margin-left:10px; }
.toggle-detail:hover { background:#138496; }
.question-image { max-width: 100%; margin-top: 10px; border-radius: 5px; }

/* チャット用 */
.chat-container { display:none; margin-top:10px; background:#fff; border-radius:8px; padding:15px; box-shadow:0 0 10px rgba(0,0,0,0.1); }
.chat-messages { max-height:200px; overflow-y:auto; border:1px solid #ccc; padding:10px; margin-bottom:10px; border-radius:5px; background:#f9f9f9; }
.chat-messages p { margin:5px 0; }
.chat-text { width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; box-sizing:border-box; resize: vertical; min-height:60px; }
.chat-send { margin-top:10px; padding:10px 15px; background:#28a745; color:white; border:none; border-radius:5px; cursor:pointer; }
.chat-send:hover { background:#218838; }
</style>

<div class="container">
    <a href="insert_question.php?id=<?= htmlspecialchars($workspaceId) ?>" class="post-button">質問を投稿する</a>

    <div class="question-list" id="question-list">
        <?php if(count($questions) === 0): ?>
            <p>まだ質問はありません。</p>
        <?php else: ?>
            <?php foreach($questions as $q): ?>
                <div class="question-item" data-id="<?= $q['質問ID'] ?>">
                    <h2><?= htmlspecialchars($q['タイトル']) ?></h2>
                    <p><strong>質問者:</strong> <?= htmlspecialchars($q['質問者識別']) ?> | <strong>作成日時:</strong> <?= htmlspecialchars($q['作成日時']) ?></p>
                    <button class="delete-button" onclick="deleteQuestion(<?= $q['質問ID'] ?>)">削除</button>
                    <button class="toggle-detail" onclick="toggleDetail(<?= $q['質問ID'] ?>)">詳細/チャットを表示</button>

                    <div class="question-detail chat-container" id="detail-<?= $q['質問ID'] ?>">
                        <p><strong>内容:</strong> <?= nl2br(htmlspecialchars($q['内容'])) ?></p>
                        <?php if($q['画像']): ?>
                            <img src="<?= htmlspecialchars($q['画像']) ?>" alt="質問画像" class="question-image">
                        <?php endif; ?>

                        <!-- チャット -->
                        <div class="chat-messages" id="chat-messages-<?= $q['質問ID'] ?>">チャットを読み込み中...</div>
                        <textarea class="chat-text" id="chat-text-<?= $q['質問ID'] ?>" placeholder="メッセージを入力"></textarea>
                        <button class="chat-send" onclick="sendChat(<?= $q['質問ID'] ?>)">送信</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const workspaceId = '<?= addslashes($workspaceId) ?>';
let chatIntervals = {};

// 質問削除
function deleteQuestion(id){
    if(!confirm('本当にこの質問を削除しますか？')) return;
    fetch(`api/delete_question.php`,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`質問ID=${id}&workspaces_id=${workspaceId}`
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            loadQuestions();
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
                p.innerHTML=`<strong>${c.送信者識別}:</strong> ${c.メッセージ}`;
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

// SPA 内で質問一覧更新用
function loadQuestions(){
    fetch(`question.php?id=${workspaceId}`)
    .then(res=>res.text())
    .then(html=>{
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const newList = tempDiv.querySelector('#question-list');
        if(newList){
            document.getElementById('question-list').innerHTML = newList.innerHTML;
        }
    });
}
</script>

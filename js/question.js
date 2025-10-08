
let chatIntervals = {}; // グローバルスコープに定義

// ワークスペースIDを取得するヘルパー関数
function getWorkspaceId() {
    // DOMから data-workspace-id 属性の値を取得
    const container = document.querySelector('.container');
    return container ? container.dataset.workspaceId : null;
}

// 質問削除
function deleteQuestion(id){
    const workspaceId = getWorkspaceId();
    if (!workspaceId) return alert('ワークスペースIDが取得できませんでした。');

    if(!confirm('本当にこの質問を削除しますか？')) return;
    
    fetch(`../api/delete_question.php`,{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`質問ID=${id}&id=${workspaceId}`
    })
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            const item = document.querySelector(`.question-item[data-id="${id}"]`);
            if(item) item.remove();
        } else {
            alert('削除に失敗しました。' + (data.message ? '理由: ' + data.message : 'サーバーから詳細なエラーが返されませんでした。'));
        }
    })
    .catch(err=>{ console.error(err); alert('削除中に通信エラーが発生しました'); });
}

// 質問詳細＋チャットの表示切替
function toggleDetail(qid){
    const container = document.getElementById('detail-'+qid);
    if(container.style.display==='block'){
        container.style.display='none';
        if(chatIntervals[qid]) clearInterval(chatIntervals[qid]); 
    } else {
        container.style.display='block';
        loadChat(qid);
        chatIntervals[qid] = setInterval(()=>loadChat(qid),5000);
    }
}

// チャット読み込み (loadChat, sendChat, loadQuestions は元のまま移動)
function loadChat(qid){
    fetch(`../api/get_chats.php?質問ID=${qid}`)
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

function sendChat(qid){
    const textEl = document.getElementById('chat-text-'+qid);
    const msg = textEl.value.trim();
    if(!msg) return alert('メッセージを入力してください');

    const formData = new URLSearchParams();
    formData.append('質問ID', qid);
    formData.append('message', msg);
    
    fetch('../api/send_chat.php',{
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
        alert('送信中に通信エラーが発生しました');
    });
}
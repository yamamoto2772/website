<?php
require_once("../localhost/db_open.php");

$workspace_id = $_GET['id'] ?? '';
if (!ctype_digit($workspace_id)) {
  exit("不正なIDです");
}

$stmt = $pdo->prepare("SELECT * FROM workspaces WHERE id = ?");
$stmt->execute([$workspace_id]);
$workspace = $stmt->fetch();

if (!$workspace) {
  exit("ワークスペースが見つかりません。");
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($workspace['name']) ?> | 企業学生間共有フォーム</title>
  <link rel="icon" href="../img/favicon.png" type="image/png" sizes="32x32">

  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      display: grid;
      grid-template-areas:
        "header header"
        "sidebar main";
      grid-template-columns: 220px 1fr;
      grid-template-rows: auto 1fr;
      height: 100vh;
    }

    header {
      grid-area: header;
      background-color: #3f51b5;
      color: white;
      padding: 1em;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    header h1 {
      margin: 0;
      font-size: 1.4em;
    }

    header p {
      margin: 0;
      font-size: 1em;
      font-weight: normal;
      opacity: 0.9;
    }

    nav {
      grid-area: sidebar;
      background-color: #f0f0f0;
      padding: 1em;
      border-right: 1px solid #ccc;
    }

    nav button {
      display: block;
      width: 100%;
      margin-bottom: 10px;
      padding: 10px;
      font-size: 0.95em;
      background-color: #ddd;
      border: none;
      cursor: pointer;
    }

    main {
      grid-area: main;
      padding: 1em;
      overflow-y: auto;
    }

    #scrollTopBtn {
      position: fixed;
      bottom: 30px;
      right: 50px;
      display: none;
      padding: 20px;
      font-size: 20px;
      background-color: #4a68d1;
      color: white;
      border: none;
      border-radius: 50%;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0,0,0,0.3);
      width: 50px;
      height: 50px;
    }
  </style>
</head>
<body>

  <header>
    <div>
      <h1>企業学生間共有フォーム</h1>
      <p><?= htmlspecialchars($workspace['name']) ?> のワークスペース</p>
    </div>
     <div>
    <a href="top.php" style="color: white; text-decoration: none; background: #ffffff33; padding: 8px 16px; border-radius: 8px; border: 1px solid #fff;">← トップに戻る</a>
  </div>
  </header>

  <nav>
    <button onclick="loadPage('calendar.php?id=<?= $workspace_id ?>')">ワークスペーストップ</button>
    <button onclick="loadPage('question.php?id=<?= $workspace_id ?>')">質問フォーム</button>
    <button onclick="loadPage('chat.html')">チャットフォーム</button>
    <button onclick="loadPage('task.html')">課題提示フォーム</button>
    <button onclick="loadPage('result.html')">成果物提出フォーム</button>
    <button onclick="loadPage('request.html')">管理者への要望</button>
  </nav>

  <main id="main-content">
    <p>読み込みを行うにはサイドバーのボタンを押してください。</p>
  </main>

  <button id="scrollTopBtn" onclick="scrollToTop()">↑</button>

  <script>
    function loadPage(page) {
      fetch(page)
        .then(response => {
          if (!response.ok) throw new Error("読み込み失敗");
          return response.text();
        })
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
        })
        .catch(error => {
          document.getElementById('main-content').innerHTML =
            `<p style="color:red;">${page} の読み込みに失敗しました。</p>`;
        });
    }

    const mainContent = document.getElementById('main-content');
    const btn = document.getElementById('scrollTopBtn');

    mainContent.addEventListener('scroll', function () {
      if (mainContent.scrollTop > 200) {
        btn.style.display = 'block';
      } else {
        btn.style.display = 'none';
      }
    });

    function scrollToTop() {
      mainContent.scrollTo({ top: 0, behavior: 'smooth' });
    }
    // 【重要】これは question.php ではなく、グローバルなスクリプトに配置してください

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

function loadQuestions(){
    const workspaceId = getWorkspaceId();
    if(typeof loadPage === 'function' && workspaceId){
        loadPage(`question.php?id=${workspaceId}`);
    } else {
        console.warn("loadPage 関数が定義されていないか、ワークスペースIDが不明です。");
    }
}
  </script>

</body>
</html>

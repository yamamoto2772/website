window.initRoomPage = function (roomIdFromFrame) {
  const roomId = roomIdFromFrame || '';
  const roomIdLabel = document.getElementById('roomIdLabel');
  const currentUserTypeLabel = document.getElementById('currentUserType');
  const btnStudent = document.getElementById('btnStudent');
  const btnCompany = document.getElementById('btnCompany');
  const chatBox = document.getElementById('chatBox');
  const messageInput = document.getElementById('messageInput');
  const sendBtn = document.getElementById('sendBtn');

  roomIdLabel && (roomIdLabel.textContent = roomId || '(未指定)');

  const userKey = `userType:room:${roomId}`;
  function getUserType() { return sessionStorage.getItem(userKey); }
  function setUserType(type) {
    if (!type) sessionStorage.removeItem(userKey);
    else sessionStorage.setItem(userKey, type);
    updateUserTypeLabel();
  }
  function updateUserTypeLabel() {
    const t = getUserType();
    currentUserTypeLabel && (currentUserTypeLabel.textContent = t ? `現在: ${t}` : '未選択');
  }

  btnStudent?.addEventListener('click', () => setUserType('student'));
  btnCompany?.addEventListener('click', () => setUserType('company'));
  updateUserTypeLabel();

  function renderMessages(list) {
    chatBox.innerHTML = '';
    list.forEach(msg => {
      const div = document.createElement('div');
      const sender = msg.sender_type || 'unknown';
      div.className = `message ${sender}`;
      const time = msg.created_at ? ` ${msg.created_at}` : '';
      div.textContent = `[${sender}] ${msg.content || ''}${time ? ' (' + time + ')' : ''}`;
      chatBox.appendChild(div);
    });
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  async function fetchMessages() {
    if (!roomId) {
      chatBox.innerHTML = `<div style="color:red;">roomId が指定されていません。</div>`;
      return;
    }
    try {
      const res = await fetch(`http://localhost/team4/website/api/chat.php?action=list&room_id=${encodeURIComponent(roomId)}`, { cache: 'no-store' });
      if (!res.ok) throw new Error('list fetch failed');
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'list api error');
      renderMessages(data.messages || []);
    } catch (e) {
      console.error(e);
      chatBox.innerHTML = `<div style="color:red;">メッセージの取得に失敗しました。</div>`;
    }
  }

  async function postMessage(content, senderType) {
    if (!roomId || !senderType || !content) throw new Error('roomId, senderType, content が必要です');

    const body = new FormData();
    body.append('action', 'post');
    body.append('room_id', roomId);
    body.append('sender_type', senderType);
    body.append('content', content);

    try {
      const res = await fetch('http://localhost/team4/website/api/chat.php', { method: 'POST', body });
      if (!res.ok) throw new Error('post fetch failed');
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'post api error');
      return true;
    } catch (e) {
      console.error(e);
      throw e;
    }
  }

  sendBtn?.addEventListener('click', async () => {
    const text = (messageInput.value || '').trim();
    const userType = getUserType();
    if (!userType) { alert('ユーザーを選択してください（生徒/企業）'); return; }
    if (!text) return;

    sendBtn.disabled = true;
    try {
      await postMessage(text, userType);
      messageInput.value = '';
      await fetchMessages();
    } catch (e) {
      alert('メッセージ送信に失敗しました。');
    } finally {
      sendBtn.disabled = false;
    }
  });

  messageInput?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') { e.preventDefault(); sendBtn?.click(); }
  });

  fetchMessages();
};

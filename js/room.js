// js/room.js
window.initRoomPage = function (roomIdFromFrame) {

  document.getElementById('userSwitch')?.remove();
document.getElementById('btnStudent')?.remove();
document.getElementById('btnCompany')?.remove();
document.getElementById('roomIdLabel')?.closest('p')?.remove();

  // ===== 設定 =====
  const API_BASE = '/team4/website/api'; // ルートからの絶対パスに合わせて変更OK
  const POLL_MS = 5000;                  // 自動更新間隔（ms） 0 ならポーリング無効
  const USE_CREDENTIALS = false;         // 別オリジンでセッションを使うなら true に

  // ===== 取得 =====
  const roomId =
    roomIdFromFrame ||
    new URLSearchParams(location.search).get('roomId') ||
    new URLSearchParams(location.search).get('id') ||
    '';

  const roomIdLabel = document.getElementById('roomIdLabel');
  const chatBox     = document.getElementById('chatBox');
  const messageInput= document.getElementById('messageInput');
  const sendBtn     = document.getElementById('sendBtn');
  const roleBadge   = document.getElementById('roleBadge'); // 任意: ロール表示

  if (roomIdLabel) roomIdLabel.textContent = roomId || '(未指定)';

  // workspace_id（保険で取得して同送）
  const ws =
    Number(document.getElementById('app')?.dataset?.workspaceId) ||
    Number(new URLSearchParams(location.search).get('workspace_id')) ||
    Number(localStorage.getItem('workspace_id')) || 0;

  // ===== ロール表示（任意）=====
  if (roleBadge) {
    fetch(`${API_BASE}/check_role.php`, {
      ...(USE_CREDENTIALS ? { credentials: 'include' } : {})
    })
      .then(r => r.ok ? r.json() : Promise.reject())
      .then(d => roleBadge.textContent = d?.role ? `ログイン種別: ${d.role}` : 'ログイン情報が未設定です')
      .catch(() => roleBadge.textContent = '種別の確認に失敗しました');
  }

  // ===== 描画 =====
  function render(list) {
  chatBox.innerHTML = '';
  (list || []).forEach(m => {
    const wrapper = document.createElement('div');
    const senderType = m.sender_type ?? m.sender ?? 'unknown';
    const sender = (() => {
      switch (senderType) {
        case 'student': return '生徒';
        case 'company': return '企業';
        case 'admin': return '管理者';
        default: return '不明';
      }
    })();

    const text = m.content ?? m.message ?? '';
    const time = m.created_at ? ` (${m.created_at})` : '';

    // 吹き出し本体
    const bubble = document.createElement('div');
    bubble.textContent = `${text}`;
    bubble.style.padding = '8px 12px';
    bubble.style.borderRadius = '12px';
    bubble.style.display = 'inline-block';
    bubble.style.maxWidth = '70%';
    bubble.style.margin = '4px 0';
    bubble.style.wordBreak = 'break-word';

    const meta = document.createElement('div');
    meta.textContent = `${sender}${time}`;
    meta.style.fontSize = '0.8em';
    meta.style.color = '#555';

    // 各タイプの見た目
    if (senderType === 'student') {
      wrapper.style.textAlign = 'right';
      bubble.style.background = '#e3f2fd';
      bubble.style.color = '#000';
      bubble.style.border = '1px solid #90caf9';
    } else if (senderType === 'company') {
      wrapper.style.textAlign = 'left';
      bubble.style.background = '#e8f5e9';
      bubble.style.border = '1px solid #a5d6a7';
    } else {
      wrapper.style.textAlign = 'center';
      bubble.style.background = '#f3e5f5';
      bubble.style.border = '1px solid #ce93d8';
    }

    wrapper.appendChild(bubble);
    wrapper.appendChild(meta);
    chatBox.appendChild(wrapper);
  });

  chatBox.scrollTop = chatBox.scrollHeight;
}



  // ===== 取得 =====
  async function fetchMessages() {
    if (!roomId) {
      chatBox.innerHTML = '<span style="color:red;">roomId が指定されていません。</span>';
      return;
    }
    const res = await fetch(
      `${API_BASE}/message.php?action=list&room_id=${encodeURIComponent(roomId)}`,
      { cache: 'no-store', ...(USE_CREDENTIALS ? { credentials: 'include' } : {}) }
    );
    if (!res.ok) throw new Error('list fetch failed');
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'list api error');
    render(data.messages || []);
  }

  // ===== 送信 =====
  async function send() {
    if (!messageInput) return;
    const text = (messageInput.value || '').trim();
    if (!text) return;
    if (!roomId) { alert('roomId が不明です'); return; }

    sendBtn && (sendBtn.disabled = true);
    messageInput.disabled = true;

    try {
      const body = new FormData();
      body.append('action', 'create');
      body.append('room_id', roomId);
      body.append('message', text);
      if (ws > 0) body.append('workspace_id', String(ws));

      const res = await fetch(`${API_BASE}/message.php?action=create`, {
        method: 'POST',
        body,
        ...(USE_CREDENTIALS ? { credentials: 'include' } : {})
      });
      if (!res.ok) throw new Error('create fetch failed');
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'create api error');

      messageInput.value = '';
      await fetchMessages();
    } finally {
      sendBtn && (sendBtn.disabled = false);
      messageInput.disabled = false;
      messageInput.focus();
    }
  }

  // ===== イベント =====
  sendBtn?.addEventListener('click', send);

  messageInput?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      send();
    }
  });

  // Ctrl+Enter でも送信
  messageInput?.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      send();
    }
  });

  // ===== 初期ロード & ポーリング =====
  let pollTimer = null;
  function startPolling() {
    if (!POLL_MS) return;
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = setInterval(() => {
      fetchMessages().catch(()=>{ /* 失敗は無視して次回 */ });
    }, POLL_MS);
  }
  window.addEventListener('beforeunload', () => pollTimer && clearInterval(pollTimer));

  fetchMessages()
    .then(startPolling)
    .catch(() => {
      chatBox.innerHTML = '<span style="color:red;">メッセージの取得に失敗しました。</span>';
    });
};

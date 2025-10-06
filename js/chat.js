// js/chat.js
window.initChatPage = function () {
  const roomList = document.getElementById('roomList');
  const showFormBtn = document.getElementById('showFormBtn');
  const roomForm = document.getElementById('roomForm');
  const createBtn = document.getElementById('createBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const newRoomTitle = document.getElementById('newRoomTitle');

  // --- workspace_id を取得（優先度：__WORKSPACE__ → data-* → ?workspace_id → ?id → localStorage）
  const wsRaw =
    (window.__WORKSPACE__ && window.__WORKSPACE__.id) ??
    document.getElementById('app')?.dataset?.workspaceId ??
    new URLSearchParams(location.search).get('workspace_id') ??
    new URLSearchParams(location.search).get('id') ??
    localStorage.getItem('workspace_id');

  const ws = Number(wsRaw);
  console.log('workspace_id being sent:', ws);

  if (!Number.isFinite(ws) || ws <= 0) {
    console.error('workspace_id not found', { wsRaw });
    alert('ワークスペースが特定できません。URLや埋め込みを確認してください。');
    return; // 以降の処理はしない
  }
  // 保険で保存
  localStorage.setItem('workspace_id', String(ws));

  // ルーム一覧を取得
  async function fetchRooms() {
    try {
      // ※ サーバ側が workspace_id を受け付けなくても無害。対応していれば絞り込みに使われます。
      const res = await fetch(`http://localhost/team4/website/api/room.php?action=list&workspace_id=${ws}`, { cache: 'no-store' });
      if (!res.ok) throw new Error('list fetch failed');
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'list api error');

      // クライアント側でも最終フィルタ（サーバが全件返しても現在WSのみ表示）
      const rooms = Array.isArray(data.rooms) ? data.rooms.filter(r => {
        const rid = Number(r.workspace_id ?? r.workspaceId ?? r.ws_id);
        return !Number.isFinite(rid) || rid === ws; // カラムが無ければ全表示、あれば一致のみ
      }) : [];

      renderRooms(rooms);
    } catch (e) {
      console.error(e);
      roomList.innerHTML = `<li style="color:red;">ルーム一覧の取得に失敗しました。</li>`;
    }
  }

  function renderRooms(rooms) {
    roomList.innerHTML = '';
    if (!rooms.length) {
      const li = document.createElement('li');
      li.textContent = 'ルームはまだありません。';
      roomList.appendChild(li);
      return;
    }
    rooms.forEach(room => {
      const li = document.createElement('li');
      const link = document.createElement('a');
      link.href = '#';
      link.textContent = room.title || room.id;
      link.style.display = 'block';
      link.style.padding = '0.5em 0';
      link.style.textDecoration = 'none';
      link.style.color = '#3f51b5';
      link.addEventListener('click', (e) => {
        e.preventDefault();
        if (window.loadPage) {
          // 保険で workspace_id も渡す
          window.loadPage(`room.html?roomId=${encodeURIComponent(room.id)}&workspace_id=${ws}`);
        }
      });
      li.appendChild(link);
      roomList.appendChild(li);
    });
  }

  async function createRoom(title) {
    console.log('will send workspace_id:', ws);
    const body = new FormData();
    body.append('action', 'create');
    body.append('title', title);
    body.append('workspace_id', ws); // ★ 必ず送る

    const res = await fetch('http://localhost/team4/website/api/room.php?action=create', {
      method: 'POST',
      body
    });
    if (!res.ok) throw new Error('create fetch failed');
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'create api error');
    return data.room;
  }

  showFormBtn?.addEventListener('click', () => {
    roomForm.style.display = 'block';
    showFormBtn.style.display = 'none';
    newRoomTitle.focus();
  });

  cancelBtn?.addEventListener('click', () => {
    roomForm.style.display = 'none';
    showFormBtn.style.display = 'inline-block';
    newRoomTitle.value = '';
  });

  createBtn?.addEventListener('click', async () => {
    const title = (newRoomTitle.value || '').trim();
    if (!title) { alert('タイトルを入力してください'); return; }
    createBtn.disabled = true;
    try {
      await createRoom(title);
      newRoomTitle.value = '';
      roomForm.style.display = 'none';
      showFormBtn.style.display = 'inline-block';
      await fetchRooms();
    } catch (e) {
      console.error(e);
      alert('ルーム作成に失敗しました。');
    } finally {
      createBtn.disabled = false;
    }
  });

  fetchRooms();
};

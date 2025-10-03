// js/chat.js
window.initChatPage = function () {
  const roomList = document.getElementById('roomList');
  const showFormBtn = document.getElementById('showFormBtn');
  const roomForm = document.getElementById('roomForm');
  const createBtn = document.getElementById('createBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const newRoomTitle = document.getElementById('newRoomTitle');

  // ルーム一覧を取得
  async function fetchRooms() {
    try {
      const res = await fetch('http://localhost/team4/website/api/room.php?action=list', { cache: 'no-store' });
      if (!res.ok) throw new Error('list fetch failed');
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'list api error');
      renderRooms(data.rooms || []);
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
          window.loadPage(`room.html?roomId=${encodeURIComponent(room.id)}`);
        }
      });
      li.appendChild(link);
      roomList.appendChild(li);
    });
  }

  async function createRoom(title) {
    const body = new FormData();
    body.append('action', 'create');
    body.append('title', title);

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

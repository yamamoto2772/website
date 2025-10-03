// js/frame.js
document.addEventListener('DOMContentLoaded', () => {
  const mainContent = document.getElementById('main-content');
  const scrollBtn = document.getElementById('scrollTopBtn');

  document.querySelectorAll('nav button').forEach(btn => {
    btn.addEventListener('click', () => {
      const page = btn.getAttribute('data-page');
      if (!page) return;
      loadPage(page);
    });
  });

  async function loadPage(page) {
    const hasQuery = page.includes('?');
    const file = hasQuery ? page.split('?')[0] : page;
    const qs = hasQuery ? page.split('?')[1] : '';

    try {
      const res = await fetch(`pages/${file}`);
      if (!res.ok) throw new Error('読み込み失敗');
      const html = await res.text();
      mainContent.innerHTML = html;

      // ページごとの初期化処理
      if (file === 'chat.html' && window.initChatPage) {
        window.initChatPage();
      } else if (file === 'room.html' && window.initRoomPage) {
        const params = new URLSearchParams(qs);
        const roomId = params.get('roomId') || '';
        window.initRoomPage(roomId);
      } else if (file === 'announcement_form.html' && window.initAnnouncementForm) {
        // お知らせフォーム初期化
        window.initAnnouncementForm();
      } else if (file === 'announcement.html' && window.initAnnouncementPage) {
        // お知らせ一覧初期化
        window.initAnnouncementPage();
      }

    } catch (e) {
      mainContent.innerHTML = `<p style="color:red;">${page} の読み込みに失敗しました。</p>`;
      console.error(e);
    }
  }

  mainContent.addEventListener('scroll', () => {
    scrollBtn.style.display = mainContent.scrollTop > 200 ? 'block' : 'none';
  });

  window.scrollToTop = function () {
    mainContent.scrollTo({ top: 0, behavior: 'smooth' });
  };

  window.loadPage = loadPage;
});

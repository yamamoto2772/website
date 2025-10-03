window.initAnnouncementPage = async function () {
  try {
    const res = await fetch("http://localhost/team4/website/api/announcement.php?action=list");
    const text = await res.text();

    console.log("📩 APIレスポンス(生):", text);

    let data;
    try {
      data = JSON.parse(text);
    } catch (err) {
      console.error("❌ JSONパース失敗:", err);
      return;
    }

    const list = document.getElementById("announcement-list");
    list.innerHTML = "";

    (data.announcements || []).forEach(item => {
      console.log("➡️ 追加する要素:", item);

      const li = document.createElement("li");
      li.innerHTML = `
        <a href="#" onclick="loadPage('announcement_detail.html?id=${item.id}')" 
           class="block p-2 border rounded hover:bg-gray-50">
          <div class="font-bold">${item.title}</div>
          <div class="text-sm text-gray-500">${item.created_at}</div>
        </a>`;
      list.appendChild(li);
    });
  } catch (err) {
    console.error("❌ initAnnouncementPage エラー:", err);
  }
};

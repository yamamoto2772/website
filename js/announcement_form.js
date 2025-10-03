function initAnnouncementForm() {
  const form = document.getElementById("announcement-form");
  if (!form) {
    console.warn("announcement-form が見つかりません");
    return;
  }

  // 既に登録されている submit イベントを削除して二重登録を防ぐ
  form.replaceWith(form.cloneNode(true));
  const newForm = document.getElementById("announcement-form");

  newForm.addEventListener("submit", async (e) => {
    e.preventDefault(); // ページ遷移を止める
    console.log("submit イベント発火");

    const data = {
      title: newForm.title.value,
      content: newForm.content.value,
      image: newForm.image.value,
      user_type: "学生" // 仮固定
    };
    console.log("送信データ:", data);

    try {
      const res = await fetch("http://localhost/team4/website/api/announcement.php?action=create", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      const result = await res.json();
      console.log("APIレスポンス:", result);

      if (result.success) {
        alert("✅ 投稿に成功しました！");
        // 成功したら一覧ページに戻る
        if (window.loadPage) {
          window.loadPage("announcement.html").then(() => {
            // 一覧ページ読み込み後に必要なら追加処理
          });
        } else {
          window.location.href = "announcement.html";
        }
      } else {
        alert("❌ 投稿失敗: " + (result.error || "不明なエラー"));
      }
    } catch (err) {
      alert("❌ 通信エラー: " + err.message);
      console.error(err);
    }
  });
}

// frame.html などでページを fetch したあとに必ず呼す
// 例: loadPage('announcement_form.html').then(initAnnouncementForm);

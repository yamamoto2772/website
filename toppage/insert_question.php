<?php
// データベース接続ファイルの読み込み
// ※ 注意: このファイルのパスが正しいことを再確認してください。
require_once('../localhost/db_open.php');

// ワークスペースIDを取得
$id = $_GET['id'] ?? 1;

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ajaxレスポンスとして処理
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];

    // POSTデータの取得とサニタイズ（ここでは簡易的な処理）
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $questioner = $_POST['questioner'] ?? '';
    // フォームから送信されたID（ワークスペースID）
    $id_post = $_POST['id'] ?? 1; 
    $image = '';
    $current_time = date('Y-m-d H:i:s');

    // 必須フィールドの確認
    if (empty($title) || empty($content) || empty($questioner)) {
        $missing = [];
        if (empty($title)) $missing[] = 'タイトル';
        if (empty($content)) $missing[] = '内容';
        if (empty($questioner)) $missing[] = '質問者名';
        $response['message'] = 'エラー: 必要な情報が不足しています。不足項目: ' . implode(', ', $missing);
        echo json_encode($response);
        exit();
    } 

    // 画像アップロード処理
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'uploads/';
        // ディレクトリが存在しない場合は作成を試みる
        if (!is_dir($upload_dir)) {
            // trueで再帰的にディレクトリを作成
            if (!mkdir($upload_dir, 0777, true)) {
                 $response['message'] = 'アップロードディレクトリの作成に失敗しました。パーミッションを確認してください。';
                 echo json_encode($response);
                 exit();
            }
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $upload_file = $upload_dir . $file_name;

        // ファイルを移動
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
            $image = $upload_file;
        } else {
            // エラーが発生した場合
            $response['message'] = '画像のアップロードに失敗しました。ファイルエラーコード: ' . $_FILES['image']['error'];
            echo json_encode($response);
            exit();
        }
    }
    
    // データベースへの挿入処理
    try {
        // SQLインジェクション防止のためプリペアドステートメントを使用
        // 'id'カラムがワークスペースIDを指すという前提で処理
        $sql = "INSERT INTO questions (タイトル, 内容, 質問者識別, 画像, 作成日時, id) VALUES (?, ?, ?, ?, ?, ?)";
        // $pdo が db_open.php で正しく定義されていることが前提
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([
            $title, 
            $content, 
            $questioner, 
            $image, 
            $current_time, 
            $id_post // ワークスペースID
        ]);

        if ($result) {
            $response['success'] = true;
            $response['message'] = '質問が正常に投稿されました。';
        } else {
            $response['message'] = '質問の投稿処理中に不明なエラーが発生しました。';
        }

    } catch (PDOException $e) {
        // データベース接続またはSQL実行エラー
        $response['message'] = 'データベースエラー: ' . $e->getMessage() . ' (SQLSTATE: ' . $e->getCode() . ')';
    }
    
    echo json_encode($response);
    exit(); // POST処理後はここで終了
}
?>

<style>
/* CSSは省略 */
.container { max-width: 100%; margin: 0; padding: 0 30px; }
.content-header { padding: 15px 0 0 0; margin-bottom: 20px;}
.back-button { padding: 8px 15px; background-color: #6c757d; color: white; border-radius: 6px; transition: background-color 0.2s, transform 0.1s; border: none; cursor: pointer; font-weight: 600; display: inline-block;}
.back-button:hover { background-color: #5a6268; transform: translateY(-1px);}
.content-main { padding: 0 30px 30px 30px; display: flex; justify-content: center; }
.form-card {max-width: 600px; width: 100%; padding: 30px; background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); }
.form-card h2 {margin-top: 0; color: #3f51b5; border-bottom: 2px solid #3f51b533; padding-bottom: 10px; margin-bottom: 20px; font-size: 1.5em; }
.post-form label { display: block; margin-top: 20px; margin-bottom: 5px; font-weight: 600; color: #3f51b5; font-size: 0.95em;}
.post-form input[type="text"], .post-form textarea, .post-form input[type="file"] {width: 100%; padding: 10px 12px; margin-top: 5px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s;}
.post-form input[type="text"]:focus, .post-form textarea:focus {border-color: #3f51b5; box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.2); outline: none;}
.post-form textarea { height: 120px; resize: vertical;}
.post-form button[type="submit"] { display: block; width: 100%; padding: 12px; margin-top: 30px; background-color: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1em; font-weight: bold; transition: background-color 0.2s, transform 0.1s; }
.post-form button[type="submit"]:hover { background-color: #218838; transform: translateY(-1px);}
.message { padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: 600;} 
/* 成功メッセージの色を定義 */
.message.success-message { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
/* エラーメッセージの色を修正（元のコードではクラス名が重複していたため） */
.message { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
</style>


<div class="content-header">
    <div class="container">
        <button class="back-button" onclick="window.parent.loadPage('question.php?id=<?= htmlspecialchars($id) ?>')">← 質問一覧に戻る</button>
    </div>
</div>

<div class="content-main">
    <div class="form-card">
        <h2>新しい質問を投稿</h2>

        <p id="response-message" class="message" style="display:none;"></p>
        
        <form id="question-form" action="" method="post" enctype="multipart/form-data" class="post-form">
           <label for="title">タイトル:</label>
            <input type="text" id="title" name="title" required placeholder="質問の要点を簡潔に入力してください">

            <label for="content">内容:</label>
            <textarea id="content" name="content" required placeholder="質問の詳細を具体的に記述してください"></textarea>

            <label for="questioner">質問者名:</label>
            <input type="text" id="questioner" name="questioner" required placeholder="あなたの識別名を入力">

            <label for="image">画像（任意）:</label>
            <input type="file" id="image" name="image" accept="image/*">
            
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>"> 
            
            <button type="button" id="submit-btn">質問を投稿する</button>
        </form>
    </div>
</div>

<script>
    // PHPからワークスペースIDを取得
    const ID = '<?= addslashes($id) ?>';
    
    // FETCH_URLを現在のURLに設定（最も安全な方法）
    // 自身に対してPOSTリクエストを送る
    const FETCH_URL = window.location.href; 

    (function initForm() {
        const form = document.getElementById('question-form');
        const messageEl = document.getElementById('response-message');
        const submitButton = document.getElementById('submit-btn');

        if (!form || !submitButton) {
            console.error("致命的なエラー: フォーム要素が見つかりませんでした。");
            return;
        }

        // ボタンのクリックイベントに非同期送信処理を登録
        submitButton.addEventListener('click', handlePost);

        function handlePost() {
            // 必須入力項目の簡易チェック
            if (!form.title.value.trim() || !form.content.value.trim() || !form.questioner.value.trim()) {
                 messageEl.style.display = 'block';
                 messageEl.className = 'message';
                 messageEl.textContent = 'エラー: タイトル、内容、質問者名は必須入力です。';
                 return;
            }

            submitButton.disabled = true;
            submitButton.textContent = '投稿中...';
            messageEl.style.display = 'none'; 

            const formData = new FormData(form);

            fetch(FETCH_URL, {
                method: 'POST', 
                body: formData
            })
            .then(response => {
                // HTTPステータスが200番台でなければエラーをスロー
                if (!response.ok) {
                    throw new Error(`HTTPエラーが発生しました！ Status: ${response.status} (${response.statusText})`);
                }
                return response.json(); // JSONレスポンスをパース
            })
            .then(data => {
                messageEl.style.display = 'block';
                messageEl.textContent = data.message;
                
                if (data.success) {
                    messageEl.className = 'message success-message';
                    
                    // 成功後、1秒待って質問一覧ページへ遷移
                    setTimeout(() => {
                        if (window.parent.loadPage) {
                            window.parent.loadPage(`question.php?id=${ID}`);
                        } else {
                            // 親ウィンドウの関数がない場合の代替手段 (デバッグ用)
                            window.location.href = `question.php?id=${ID}`; 
                        }
                    }, 1000); 
                    
                } else {
                    // サーバー側で処理エラー（例：DBエラー、必須項目不足）
                    messageEl.className = 'message';
                }
            })
            .catch(error => {
                // 通信エラー、JSONパースエラー、HTTPエラーなど
                console.error('致命的な通信エラー:', error);
                
                messageEl.style.display = 'block';
                messageEl.className = 'message';
                messageEl.textContent = `致命的な通信エラーが発生しました。 (${error.message})`; 
            })
            .finally(() => {
                // 成功時以外はボタンを再有効化
                if (!messageEl.classList.contains('success-message')) {
                    submitButton.disabled = false;
                    submitButton.textContent = '質問を投稿する';
                }
            });
        }
    })();
</script>
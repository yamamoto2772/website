<?php
// データベース接続ファイルの読み込み
// ※ パスが正しいことを必ず再確認してください。
$db_path = '../localhost/db_open.php';

if (!file_exists($db_path)) {
    exit('【PHPエラー】データベース接続ファイルが見つかりません。パス: ' . htmlspecialchars($db_path));
}
require_once($db_path);

// ワークスペースIDを取得
$id = $_GET['id'] ?? 1;

// フォームが送信された場合の処理 (POSTリクエスト)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $questioner = $_POST['questioner'] ?? '';
    $id_post = $_POST['id'] ?? 1; 
    $image = '';
    $current_time = date('Y-m-d H:i:s');

    $status_type = 'error';
    $status_message = ''; // エラーメッセージを初期化

    // ---------------------------------------------------------------------
    // 1. 必須フィールドの確認
    // ---------------------------------------------------------------------
    if (empty($title) || empty($content) || empty($questioner)) {
        $status_message = 'タイトル、内容、質問者名は必須入力です。';
        goto redirect_to_question_list; // エラー処理へジャンプ
    } 

    // ---------------------------------------------------------------------
    // 2. 画像アップロード処理
    // ---------------------------------------------------------------------
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = __DIR__ . '/uploads/'; 
        $db_image_path_prefix = 'uploads/';
        
        if (!is_dir($upload_dir)) {
             if (!@mkdir($upload_dir, 0777, true)) {
                 $status_message = 'アップロードディレクトリの作成に失敗しました。パーミッションを確認してください。';
                 goto redirect_to_question_list; // エラー処理へジャンプ
             }
        }
        
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $upload_file = $upload_dir . $file_name;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_file)) {
            $status_message = '画像のアップロードに失敗しました。';
            goto redirect_to_question_list; // エラー処理へジャンプ
        }
        $image = $db_image_path_prefix . $file_name; 
    }
    
    // ---------------------------------------------------------------------
    // 3. データベースへの挿入処理
    // ---------------------------------------------------------------------
    try {
        $sql = "INSERT INTO questions (タイトル, 内容, 質問者識別, 画像, 作成日時, id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $result = $stmt->execute([ $title, $content, $questioner, $image, $current_time, $id_post ]);

        if ($result) {
            $status_type = 'success';
            $status_message = '質問が正常に投稿されました。';
        } else {
            $status_message = '質問の投稿処理中に不明なデータベースエラーが発生しました。';
        }

    } catch (PDOException $e) {
        $status_message = 'データベースエラー: ' . $e->getMessage();
    }
    
    // ---------------------------------------------------------------------
    // 4. 【統合されたリダイレクト処理】
    // ---------------------------------------------------------------------
    redirect_to_question_list:
    
    // 親のURLが workspace.php であることを想定
    $parent_url = 'workspace.php?id=' . urlencode($id_post);
    
    // question.php への切り替え指示とステータスメッセージをパラメータに追加
    $js_redirect_url = $parent_url . '&content=question' . '&status=' . $status_type . '&msg=' . urlencode($status_message);
    
    // JavaScriptを出力し、親ウィンドウをリダイレクトさせる
    echo "<script type='text/javascript'>";
    
    // 親ウィンドウのURLを強制的に書き換えることで、ワークスペース画面全体をリロードし、質問一覧を表示させる
    echo "  window.parent.location.href = '{$js_redirect_url}';";
    
    echo "</script>";
    exit(); // PHPの出力を終了
}
// ---------------------------------------------------------------------
// HTML表示部分 (GETリクエストのみ)
// ---------------------------------------------------------------------
?>

<style>
/* CSSはデザイン版を使用 */
.container { max-width: 100%; margin: 0; padding: 0 30px; }
.content-header { padding: 15px 0 0 0; margin-bottom: 20px;}
.back-button { 
    padding: 8px 15px; 
    background-color: #6c757d; 
    color: white; 
    border-radius: 6px; 
    transition: background-color 0.2s, transform 0.1s; 
    border: none; 
    cursor: pointer; 
    font-weight: 600; 
    display: inline-block;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.back-button:hover { background-color: #5a6268; transform: translateY(-1px);}
.content-main { padding: 0 30px 30px 30px; display: flex; justify-content: center; }
.form-card {
    max-width: 650px; 
    width: 100%; 
    padding: 40px; 
    background: #ffffff; 
    border: 1px solid #e0e0e0; 
    border-radius: 12px; 
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); 
}
.form-card h2 {
    margin-top: 0; 
    color: #3f51b5; 
    border-bottom: 3px solid #3f51b5; 
    padding-bottom: 15px; 
    margin-bottom: 30px; 
    font-size: 1.8em; 
    font-weight: 700;
}
.post-form label { 
    display: block; 
    margin-top: 25px; 
    margin-bottom: 8px; 
    font-weight: 700; 
    color: #333333; 
    font-size: 1em;
}
.post-form input[type="text"], 
.post-form textarea, 
.post-form input[type="file"] {
    width: 100%; 
    padding: 12px; 
    margin-top: 5px; 
    border: 1px solid #cccccc; 
    border-radius: 8px; 
    box-sizing: border-box; 
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 1em;
    background-color: #f9f9f9;
}
.post-form textarea { 
    height: 150px; 
    resize: vertical;
}
.post-form input[type="text"]:focus, 
.post-form textarea:focus {
    border-color: #3f51b5; 
    box-shadow: 0 0 0 4px rgba(63, 81, 181, 0.2); 
    background-color: #fff;
    outline: none;
}
.post-form input[type="file"] {
    padding: 12px 10px;
    background-color: #eeeeee;
    cursor: pointer;
}
.post-form button[type="submit"] { 
    display: block; 
    width: 100%; 
    padding: 15px; 
    margin-top: 40px; 
    background-color: #28a745; 
    color: white; 
    border: none; 
    border-radius: 8px; 
    cursor: pointer; 
    font-size: 1.2em; 
    font-weight: bold; 
    transition: background-color 0.2s, transform 0.1s;
    box-shadow: 0 3px 6px rgba(40, 167, 69, 0.4);
}
.post-form button[type="submit"]:hover { 
    background-color: #218838; 
    transform: translateY(-2px); 
}
.message { 
    padding: 15px; 
    margin-bottom: 20px; 
    border-radius: 8px; 
    text-align: center; 
    font-weight: 600;
    border: 1px solid;
    font-size: 1.1em;
} 
.message.success-message { 
    background-color: #d4edda; 
    color: #155724; 
    border-color: #c3e6cb; 
}
.message.error-message { 
    background-color: #f8d7da; 
    color: #721c24; 
    border-color: #f5c6cb;
}
</style>


<div class="content-header">
    <div class="container">
        <button class="back-button" onclick="window.parent.location.href='workspace.php?id=<?= htmlspecialchars($id) ?>&content=question'">← 質問一覧に戻る</button>
    </div>
</div>

<div class="content-main">
    <div class="form-card">
        <h2>新しい質問を投稿</h2>

        <p id="response-message" class="message" style="display:none;"></p>
        
        <form id="question-form" action="insert_question.php?id=<?= htmlspecialchars($id) ?>" method="post" enctype="multipart/form-data" class="post-form">
           <label for="title">タイトル:</label>
           <input type="text" id="title" name="title" required placeholder="質問の要点を簡潔に入力してください">

           <label for="content">内容:</label>
           <textarea id="content" name="content" required placeholder="質問の詳細を具体的に記述してください"></textarea>

           <label for="questioner">質問者名:</label>
           <input type="text" id="questioner" name="questioner" required placeholder="あなたの識別名を入力">

           <label for="image">画像（任意）:</label>
           <input type="file" id="image" name="image" accept="image/*">
           
           <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>"> 
           
           <button type="submit" id="submit-btn">質問を投稿する</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 質問一覧に戻るボタンの動作（親のコンテンツ切り替え）
        const backButton = document.querySelector('.back-button');
        const workspaceId = '<?= htmlspecialchars($id) ?>';
        
        if (backButton) {
            backButton.onclick = function() {
                // 親の loadPage 関数があればそれを使う
                if (window.parent.loadPage && typeof window.parent.loadPage === 'function') {
                    window.parent.loadPage(`question.php?id=${workspaceId}`);
                } else {
                    // なければ親ウィンドウのURL全体を書き換えて強制的に遷移
                    window.parent.location.href = `question.php?id=${workspaceId}`;
                }
            };
        }
    });
</script>
<?php
// config.php ファイルを読み込み、データベースに接続
require 'config.php';

// questions テーブルから作成日時が新しい順にすべての質問を取得
$sql = "select * from questions order by 作成日時 DESC";

try {
    // クエリを実行し、結果を $stmt に格納
    $stmt = $pdo->query($sql);
} catch (PDOException $e) {
    // データベースエラーが発生した場合、エラーメッセージを表示して終了
    echo "取得エラー: " . $e->getMessage();
    exit;
}
?>

<style>
/* question.php専用のスタイル */
.question {
    border: 1px solid #333;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 16px;
    background-color: #f9f9f9;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
}

.question h3 {
    margin: 0 0 8px;
}

.question small {
    display: block;
    color: #555;
    margin-bottom: 8px;
}

.new_question_button {
    text-align: center;
    margin-bottom: 20px;
}

.new_question_button button {
    padding: 12px 24px;
    font-size: 16px;
    background-color: #4a68d1;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    transition: 0.3s;
}

.new_question_button button:hover {
    background-color: #3a56b1;
}

.link_button {
    background: none;
    border: none;
    color: blue;
    padding: 0;
    font: inherit;
    cursor: pointer;
    text-decoration: underline;
}

.link_button:hover {
    opacity: 0.7;
}

</style>

<header><h2>質問フォーム</h2></header>
<br>

<div class="new_question_button">
    <button onclick="loadPage('new_question.html')">新しい質問を作成する</button>
</div>

<?php
// データベースから取得した質問データをループで表示
foreach ($stmt as $row):
?>
    <div class="question">
        <br>
        <h3><?php echo htmlspecialchars($row['タイトル']); ?></h3>
        <small><?php echo htmlspecialchars($row['作成日時']); ?></small>
        <button class="link_button" onclick="loadPage('question_detail.php?id=<?php echo urlencode($row['質問ID']); ?>')">詳細を見る</button>
    </div>
    <br>
<?php endforeach; ?>
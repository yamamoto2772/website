<?php
// db_open.phpへの正しい相対パスを指定
require_once("../localhost/db_open.php");
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
// 質問IDが指定されている場合、質問詳細を表示
if (isset($_GET['id'])) {
    // URLのパラメータからIDを取得
    $questionId = $_GET['id'];

    // 質問IDが存在するかどうかを検証
    if (!ctype_digit($questionId)) {
        echo "無効な質問IDです。";
        exit;
    }

    try {
        // プリペアドステートメントを使用して質問の詳細を取得
        $stmt = $pdo->prepare("SELECT * FROM questions WHERE 質問ID = ?");
        $stmt->execute([$questionId]);
        $question = $stmt->fetch();

        // 質問が見つからない場合
        if (!$question) {
            echo "質問が見つかりません。";
            exit;
        }

        // 質問の詳細を表示
        echo "<h2>" . htmlspecialchars($question['タイトル']) . "</h2>";
        echo "<p>" . nl2br(htmlspecialchars($question['内容'])) . "</p>";
        echo "<small>作成日時: " . htmlspecialchars($question['作成日時']) . "</small>";
        echo '<br><br><button onclick="loadPage(\'question.php\')">質問一覧に戻る</button>';

    } catch (PDOException $e) {
        echo "取得エラー: " . htmlspecialchars($e->getMessage());
    }

} else { // 質問一覧を表示
    try {
        // 全質問を取得
        $sql = "select * from questions order by 作成日時 DESC";
        $stmt = $pdo->query($sql);
    } catch (PDOException $e) {
        echo "取得エラー: " . htmlspecialchars($e->getMessage());
        exit;
    }

    // データベースから取得した質問データをループで表示
    foreach ($stmt as $row):
?>
    <div class="question">
        <br>
        <h3><?php echo htmlspecialchars($row['タイトル']); ?></h3>
        <small><?php echo htmlspecialchars($row['作成日時']); ?></small>
        <button class="link_button" onclick="loadPage('question.php?id=' + encodeURIComponent(<?php echo json_encode($row['質問ID']); ?>))">詳細を見る</button>
    </div>
    <br>
<?php endforeach; ?>

<?php } // elseブロックの閉じタグ ?>
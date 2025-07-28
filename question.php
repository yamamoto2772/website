    <?php
    require 'localhost/db_open.php';

    $sql = "select * from questions order by 作成日時 DESC";

    try {
        $stmt = $pdo->query($sql); 
    } catch (PDOException $e) {
      echo "取得エラー: " . $e->getMessage();
      exit;
    }
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>質問フォーム</title>
</head>
<body>
    <style>
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

  .question button {
    color: #2a5db0;
    text-decoration: none;
    font-weight: bold;
  }

  .question a:hover {
    text-decoration: underline;
  }

  .new_question_button {
    margin-bottom: 20px;
  }

  .link_button{
      background: none;
      border: none;
      color: blue;
      padding: 0;
      font: inherit;
      cursor: pointer;
      text-decoration: underline;
  }

  .link_button:hover{
    opacity:0.7;
  }
  
  .new_question_button {
    text-align: center;      
            
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

 


</style>

    <header><h2>質問フォーム</h2></header>
    
    <br>
    <div class="new_question_button">
        <button onclick="loadPage('new_question.html')">新しい質問を作成する</button>    
    </div>
    <?php foreach ($stmt as $row): ?>
    <div class="question">
      <br>
      <h3><?php echo htmlspecialchars($row['タイトル']); ?></h3>
      <small><?php echo htmlspecialchars($row['作成日時']); ?></small>
      <button class="link_button"onclick="loadPage('question_detail.php?id=<?php echo urlencode($row['質問ID']); ?>')">詳細を見る</button>
    </div>
    <br>
  <?php endforeach; ?>

    
</body>
</html>
<?php
require_once("../../localhost/db_open.php");

$name = $_POST['name'] ?? '';

if (!empty($name)) {
    $stmt = $pdo->prepare("INSERT INTO workspaces (name, created_at) VALUES (:name, NOW())");
    $stmt->execute([':name' => $name]);

    // 通知メッセージをlocalStorageにセットし、top.phpへリダイレクト
    echo "<script>
        localStorage.setItem('notification', 'ワークスペース「{$name}」を作成しました。');
        window.location.href = '../top.php';
    </script>";
    exit;

} else {
    echo "<script>
        alert('ワークスペース名が空です。');
        window.location.href = '../top.php';
    </script>";
    exit;
}
?>

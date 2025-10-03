<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = ["http://localhost:3000", "http://127.0.0.1:3000"];
if (in_array($origin, $allowed_origins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../localhost/db_open.php';
header("Content-Type: application/json; charset=UTF-8");

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT `お知らせID` AS id, `タイトル` AS title, `内容` AS content, `投稿日時` AS created_at FROM announcements ORDER BY `投稿日時` DESC");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "announcements" => $rows]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "SQLエラー: " . $e->getMessage()]);
    }
    exit;
}

if ($action === 'detail' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT `お知らせID` AS id, `タイトル` AS title, `内容` AS content, `画像` AS image, `投稿者種別` AS user_type, `投稿日時` AS created_at FROM announcements WHERE `お知らせID`=?");
        $stmt->execute([$_GET['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode(["success" => true, "announcement" => $row]);
        } else {
            echo json_encode(["success" => false, "error" => "データが見つかりません"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "SQLエラー: " . $e->getMessage()]);
    }
    exit;
}

if ($action === 'create' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['title']) || empty($data['content'])) {
        echo json_encode(["success" => false, "error" => "タイトルまたは内容が空です"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (`お知らせID`, `タイトル`, `内容`, `画像`, `投稿者種別`, `投稿日時`) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            uniqid(),
            $data['title'],
            $data['content'],
            $data['image'] ?? '',
            $data['user_type'] ?? '未指定',
            date("Y-m-d H:i:s")
        ]);

        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "SQLエラー: " . $e->getMessage()]);
    }
    exit;
}

echo json_encode(["success" => false, "error" => "無効なアクション"]);

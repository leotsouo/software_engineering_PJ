<?php
// edit_announcement.php
require_once 'db_connection.php';

// 檢查是否有傳入有效的 AnnouncementID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("未提供有效的公告 ID");
}
$announcement_id = (int)$_GET['id'];
// 接收更新成功提示
$success = isset($_GET['success']) && $_GET['success'] === '1';

try {
    // 處理表單提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $errors  = [];
        if ($title === '') {
            $errors[] = '請輸入標題。';
        }
        if ($content === '') {
            $errors[] = '請輸入內容。';
        }
        if (empty($errors)) {
            $sql_update = "UPDATE announcement
                           SET Title = :title, Content = :content, PublishDate = NOW()
                           WHERE AnnouncementID = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                ':title'   => $title,
                ':content' => $content,
                ':id'      => $announcement_id
            ]);
            // 更新完成，重新導向並帶上成功參數
            header('Location: edit_announcement.php?id=' . $announcement_id . '&success=1');
            exit;
        }
    }

    // 讀取原始資料以預填表單
    $sql = "SELECT Title, Content, PublishDate FROM announcement WHERE AnnouncementID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $announcement_id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$announcement) {
        die("找不到該公告");
    }

} catch (PDOException $e) {
    die("資料庫錯誤: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯公告</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #eef3fa; color: #333; }
        .container {
            max-width: 800px;
            margin: 60px auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        h1 { color: #0057b8; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc;
            border-radius: 5px; font-size: 16px; resize: vertical;
        }
        .form-group textarea { height: 200px; }
        .errors {
            background: #fdecea; border: 1px solid #f5c2c0;
            color: #b33a3a; padding: 15px; border-radius: 5px; margin-bottom: 20px;
        }
        .success {
            background: #e6ffed; border: 1px solid #b7eb8f;
            color: #389e0d; padding: 15px; border-radius: 5px; margin-bottom: 20px;
        }
        .btn-submit {
            background-color: #0073e6; color: #fff; border: none;
            padding: 12px 25px; font-size: 16px; border-radius: 5px;
            cursor: pointer; transition: background-color 0.3s;
        }
        .btn-submit:hover { background-color: #005bb5; }
        .btn-back {
            display: inline-block; margin-left: 15px; margin-top: 0;
            color: #0073e6; text-decoration: none; font-size: 16px;
        }
        .btn-back:hover { text-decoration: underline; }
        /* 新增：回上一頁按鈕，絕對定位右下 */
        .btn-prev {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background-color: #0057b8;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-prev:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>編輯公告</h1>

        <!-- 成功提示 -->
        <?php if ($success): ?>
            <div class="success">✅ 公告已更新成功！</div>
        <?php endif; ?>

        <!-- 錯誤訊息 -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- 編輯表單 -->
        <form action="edit_announcement.php?id=<?= $announcement_id ?>" method="post">
            <div class="form-group">
                <label for="title">標題</label>
                <input type="text" id="title" name="title"
                       value="<?= htmlspecialchars($announcement['Title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="content">內容</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($announcement['Content']) ?></textarea>
            </div>

            <button type="submit" class="btn-submit">更新</button>
            <a href="admin_dashboard.php?id=<?= $announcement_id ?>" class="btn-back">取消</a>
        </form>

        <!-- 新增：回上一頁按鈕 -->
        <a href="admin_dashboard.php?id=<?= $announcement_id ?>" class="btn-prev">⏎ 回上一頁</a>
    </div>
</body>
</html>
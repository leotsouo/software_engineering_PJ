<?php
require_once 'db_connection.php'; // 引入 PDO 連線

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 取得並過濾輸入
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // 檢查必填
    $errors = [];
    if ($title === '') {
        $errors[] = '請輸入標題。';
    }
    if ($content === '') {
        $errors[] = '請輸入內容。';
    }

    if (empty($errors)) {
        try {
            // 插入公告
            $sql = "INSERT INTO announcement (Title, Content, PublishDate)
                    VALUES (:title, :content, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title'   => $title,
                ':content' => $content
            ]);
            // 插入成功後轉回公告列表
            header('Location: admin_dashboard.php'); 
            exit;
        } catch (PDOException $e) {
            $errors[] = '資料庫錯誤：' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增公告</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #eef3fa; color: #333; }
        .container { max-width: 800px; margin: 60px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h1 { color: #0057b8; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;
            font-size: 16px; resize: vertical;
        }
        .form-group textarea { height: 200px; }
        .errors { background: #fdecea; border: 1px solid #f5c2c0; color: #b33a3a; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .btn-submit {
            background-color: #0073e6; color: #fff; border: none; padding: 12px 25px;
            font-size: 16px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s;
        }
        .btn-submit:hover { background-color: #005bb5; }
        .btn-back {
            display: inline-block; margin-top: 15px; color: #0073e6; text-decoration: none;
        }
        .btn-back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>新增公告</h1>

        <!-- 錯誤訊息顯示 -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- 新增公告表單 -->
        <form action="add_announcement.php" method="post">
            <div class="form-group">
                <label for="title">標題</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="content">內容</label>
                <textarea id="content" name="content" required><?= htmlspecialchars($content ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit">送出</button>
        </form>

        <a href="admin_dashboard.php" class="btn-back">« 回到首頁</a>
    </div>
</body>
</html>
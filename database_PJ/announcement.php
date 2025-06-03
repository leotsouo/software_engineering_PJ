<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

// 確保 `AnnouncementID` 在 URL 中提供
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("未提供有效的公告 ID");
}

$announcement_id = $_GET['id'];

try {
    // 獲取公告詳細資料
    $sql = "SELECT Title, Content, PublishDate 
            FROM announcement 
            WHERE AnnouncementID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $announcement_id, PDO::PARAM_INT);
    $stmt->execute();
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        die("找不到該公告或公告已被刪除");
    }

    // 獲取附件資料
    $sql_attachments = "SELECT AttachmentName, AttachmentLink 
                        FROM attachment 
                        WHERE AnnouncementID = :id";
    $stmt_attachments = $pdo->prepare($sql_attachments);
    $stmt_attachments->bindParam(':id', $announcement_id, PDO::PARAM_INT);
    $stmt_attachments->execute();
    $attachments = $stmt_attachments->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("資料獲取失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>公告詳細內容</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #0057b8;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .announcement-title {
            font-size: 28px;
            font-weight: bold;
            color: #0057b8;
            margin-bottom: 20px;
        }
        .announcement-date {
            font-size: 14px;
            color: #777;
            margin-bottom: 20px;
        }
        .announcement-content {
            font-size: 18px;
            line-height: 1.6;
        }
        .attachments h2 {
            font-size: 22px;
            margin-top: 30px;
            color: #0057b8;
        }
        .attachments ul {
            list-style: none;
            padding: 0;
        }
        .attachments li {
            margin-bottom: 10px;
        }
        .attachments a {
            color: #0073e6;
            text-decoration: none;
        }
        .attachments a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0073e6;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <header>
        <h1>公告詳細內容</h1>
    </header>
    <div class="container">
        <div class="announcement-title"><?= htmlspecialchars($announcement['Title']) ?></div>
        <div class="announcement-date">發布日期：<?= htmlspecialchars($announcement['PublishDate']) ?></div>
        <div class="announcement-content"><?= nl2br(htmlspecialchars($announcement['Content'])) ?></div>

        <!-- 附件區域 -->
        <div class="attachments">
            <h2>附件</h2>
            <?php if (empty($attachments)): ?>
                <p>沒有附件。</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($attachments as $attachment): ?>
                        <li>
                            <a href="<?= htmlspecialchars($attachment['AttachmentLink']) ?>" target="_blank">
                                <?= htmlspecialchars($attachment['AttachmentName']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <a href="index.php" class="btn">回首頁</a>
    </div>
</body>
</html>

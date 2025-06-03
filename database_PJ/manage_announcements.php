//公告管理處理腳本
<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['announcementTitle'];
    $content = $_POST['announcementContent'];
    $attachmentPath = '';

    if (!empty($_FILES['announcementFile']['name'])) {
        $attachmentPath = 'uploads/' . basename($_FILES['announcementFile']['name']);
        move_uploaded_file($_FILES['announcementFile']['tmp_name'], $attachmentPath);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Announcement (Title, Content, PublishDate) VALUES (?, ?, NOW())");
        $stmt->execute([$title, $content]);
        $announcementID = $pdo->lastInsertId();

        if ($attachmentPath) {
            $stmt = $pdo->prepare("INSERT INTO Attachment (AnnouncementID, AttachmentName, AttachmentLink) VALUES (?, ?, ?)");
            $stmt->execute([$announcementID, basename($attachmentPath), $attachmentPath]);
        }

        echo "公告新增成功！";
    } catch (PDOException $e) {
        echo "新增公告失敗：" . $e->getMessage();
    }
}
?>

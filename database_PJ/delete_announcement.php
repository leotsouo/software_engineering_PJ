<?php
require_once 'db_connection.php';

// 確認傳入的 AnnouncementID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("未提供有效的公告 ID");
}
$announcement_id = (int)$_GET['id'];

try {
    // 刪除公告主資料
    $sql = "DELETE FROM announcement WHERE AnnouncementID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $announcement_id]);

    // 如有附件，則一併刪除
    $sql_att = "DELETE FROM attachment WHERE AnnouncementID = :id";
    $stmt_att = $pdo->prepare($sql_att);
    $stmt_att->execute([':id' => $announcement_id]);

    // 刪除完成後，顯示提示並導回首頁
    echo '<!DOCTYPE html>';
    echo '<html lang="zh-TW">';
    echo '<head><meta charset="UTF-8"><title>刪除公告</title></head>';
    echo '<body><script>';
    echo 'alert("✅ 公告已成功刪除！");';
    echo 'window.location.href = "admin_dashboard.php";';
    echo '</script></body></html>';
    exit;

} catch (PDOException $e) {
    die("刪除失敗: " . $e->getMessage());
}
?>
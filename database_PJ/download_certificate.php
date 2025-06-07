<?php
require_once 'db_connection.php';
session_start();

// 檢查是否已登入學生
if (!isset($_SESSION['student_id'])) {
    echo "請先登入學生帳號再下載證書。";
    exit;
}

// 拿到來自表單的隊伍名稱
$team_name = $_POST['team_name'] ?? '';

if (!$team_name) {
    echo "❌ 未提供隊伍名稱。";
    exit;
}

try {
    // 查詢該學生是否真的屬於這個隊伍
    $sql = "SELECT tm.TeamID FROM teammember tm
            JOIN team t ON tm.TeamID = t.TeamID
            WHERE tm.StudentID = :student_id AND t.TeamName = :team_name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':student_id' => $_SESSION['student_id'],
        ':team_name' => $team_name
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $file = __DIR__ . '/uploads/certificates/certificate_template.pdf';
        if (file_exists($file)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="certificate_' . urlencode($team_name) . '.pdf"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            echo "❌ 證書檔案不存在，請聯絡主辦單位。";
        }
    } else {
        echo "⚠️ 您無法下載此隊伍的證書。";
    }
} catch (PDOException $e) {
    echo "資料庫錯誤：" . $e->getMessage();
}

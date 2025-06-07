<?php
session_start();
require_once 'db_connection.php';

// 檢查是否登入
if (!isset($_SESSION['judge_id'])) {
    header("Location: login_judge.php");
    exit();
}

// 取得評審 ID 與傳入的 score_id
$judge_id = $_SESSION['judge_id'];
$score_id = $_POST['score_id'] ?? '';

if (empty($score_id)) {
    die("⚠️ 請提供要刪除的評分 ID");
}

try {
    // 確認該筆分數是這位評審的
    $stmt_check = $pdo->prepare("SELECT * FROM score WHERE ScoreID = :score_id AND JudgeID = :judge_id");
    $stmt_check->execute([
        'score_id' => $score_id,
        'judge_id' => $judge_id
    ]);
    $score = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$score) {
        die("❌ 無權限刪除此評分或評分不存在！");
    }

    // 刪除資料
    $stmt_delete = $pdo->prepare("DELETE FROM score WHERE ScoreID = :score_id");
    $stmt_delete->execute(['score_id' => $score_id]);

    // 回主頁
    header("Location: judge_dashboard.php");
    exit();
} catch (PDOException $e) {
    die("❌ 刪除過程發生錯誤: " . $e->getMessage());
}

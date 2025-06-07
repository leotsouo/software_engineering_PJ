<?php
session_start();
require_once 'db_connection.php';

// 檢查是否登入
if (!isset($_SESSION['judge_id'])) {
    header("Location: login_judge.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judge_id = $_SESSION['judge_id'];
    $team_id = $_POST['team_id'] ?? '';
    $scores = $_POST['scores'] ?? [];
    $comment = trim($_POST['comment'] ?? '');

    // 基本驗證
    if (empty($team_id) || empty($scores) || empty($comment)) {
        die("資料不完整，請回上一頁檢查。");
    }

    // 計算平均分數
    $score_values = array_map('intval', $scores);
    $average = round(array_sum($score_values) / count($score_values));

    try {
        $stmt = $pdo->prepare("
            INSERT INTO score (JudgeID, TeamID, ScoreValue, Comment, Timestamp)
            VALUES (:judge_id, :team_id, :score_value, :comment, NOW())
        ");
        $stmt->execute([
            'judge_id' => $judge_id,
            'team_id' => $team_id,
            'score_value' => $average,
            'comment' => $comment
        ]);

        // 回到評審主頁
        header("Location: judge_dashboard.php");
        exit;
    } catch (PDOException $e) {
        die("評分寫入失敗: " . $e->getMessage());
    }
} else {
    die("無效請求方式。");
}

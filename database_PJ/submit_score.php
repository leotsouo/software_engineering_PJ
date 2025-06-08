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

    // ✅ 後端鎖定檢查：確認是否已評過
    $check_stmt = $pdo->prepare("SELECT 1 FROM score WHERE JudgeID = :judge AND TeamID = :team");
    $check_stmt->execute(['judge' => $judge_id, 'team' => $team_id]);
    if ($check_stmt->fetch()) {
        // 若已存在紀錄，導回並提示錯誤
        header("Location: judge_dashboard.php?error=already_scored");
        exit();
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

        header("Location: judge_dashboard.php?success=1");
        exit;
    } catch (PDOException $e) {
        die("評分寫入失敗: " . $e->getMessage());
    }
} else {
    die("無效請求方式。");
}

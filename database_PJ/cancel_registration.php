<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];

try {
    // 找出隊伍 ID
    $sql_team = "SELECT TeamID FROM teammember WHERE StudentID = :student_id";
    $stmt_team = $pdo->prepare($sql_team);
    $stmt_team->bindParam(':student_id', $student_id);
    $stmt_team->execute();
    $team = $stmt_team->fetch(PDO::FETCH_ASSOC);

    if ($team) {
        $team_id = $team['TeamID'];

        // 刪除 submission（如果有）
        $stmt_delete_submission = $pdo->prepare("DELETE FROM submission WHERE TeamID = :team_id");
        $stmt_delete_submission->execute([':team_id' => $team_id]);

        // 刪除 teammember
        $stmt_delete_member = $pdo->prepare("DELETE FROM teammember WHERE TeamID = :team_id");
        $stmt_delete_member->execute([':team_id' => $team_id]);

        // 刪除 team
        $stmt_delete_team = $pdo->prepare("DELETE FROM team WHERE TeamID = :team_id");
        $stmt_delete_team->execute([':team_id' => $team_id]);
    }

    // ✅ 清除 session 並導回首頁
    unset($_SESSION['student_id']);
    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    echo "取消報名錯誤：" . $e->getMessage();
}

<?php
// delete_judge.php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

$judge_id = $_GET['id'] ?? '';
if ($judge_id) {
    $stmt = $pdo->prepare("DELETE FROM judge WHERE JudgeID = :id");
    $stmt->execute([':id' => $judge_id]);
}

header('Location: admin_dashboard.php');
exit;
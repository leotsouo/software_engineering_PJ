<?php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("請提供隊伍 ID");
}
$teamID = (int)$_GET['id'];

try {
    // 如果有外鍵關聯，請先刪除相關 teammember... etc
    $pdo->beginTransaction();

    // 先刪隊員
    $pdo->prepare("DELETE FROM teammember WHERE TeamID=:id")
        ->execute([':id'=>$teamID]);

    // 再刪隊伍
    $pdo->prepare("DELETE FROM team WHERE TeamID=:id")
        ->execute([':id'=>$teamID]);

    $pdo->commit();
    header('Location: admin_dashboard.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    die("刪除失敗: ".$e->getMessage());
}
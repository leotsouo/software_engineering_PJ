<?php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

$teacher_id = $_GET['id'] ?? '';
if ($teacher_id) {
    $sql = "DELETE FROM teacher WHERE TeacherID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $teacher_id]);
}

header('Location: admin_dashboard.php');
exit;
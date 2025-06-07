<?php
// delete_student.php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

$student_id = $_GET['id'] ?? '';
if ($student_id) {
    $stmt = $pdo->prepare("DELETE FROM teammember WHERE StudentID = :id");
    $stmt->execute([':id' => $student_id]);
}

header('Location: admin_dashboard.php');
exit;
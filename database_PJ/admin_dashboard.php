<?php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
$message = '';

// ------------------ 評分標準管理 ------------------
try {
    $stmt_criteria_all = $pdo->query("SELECT * FROM criteria ORDER BY id ASC");
    $criteria_all = $stmt_criteria_all->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criteria_form'])) {
        $new_criteria = trim($_POST['criteria_name']);
        if (!empty($new_criteria)) {
            $stmt = $pdo->prepare("INSERT INTO criteria (name) VALUES (:name)");
            $stmt->bindParam(':name', $new_criteria);
            $stmt->execute();
            $message = "✅ 評分標準已成功新增！";
        } else {
            $message = "⚠️ 請輸入評分標準名稱。";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_criteria_form'])) {
        $delete_id = $_POST['criteria_id'] ?? '';
        if (!empty($delete_id)) {
            $stmt = $pdo->prepare("DELETE FROM criteria WHERE id = :id");
            $stmt->bindParam(':id', $delete_id);
            $stmt->execute();
            $message = "🗑️ 評分標準已成功刪除！";
        } else {
            $message = "⚠️ 請選擇要刪除的標準。";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} catch (PDOException $e) {
    die("評分標準管理失敗: " . $e->getMessage());
}
?>
<!-- 後續 HTML 與管理介面請保留你原本的整合樣式 -->
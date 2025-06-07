<?php
// edit_judge.php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// 1. 檢查 JudgeID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("未提供有效的評審 ID");
}
$judge_id = $_GET['id'];
$success  = isset($_GET['success']) && $_GET['success'] === '1';
$errors   = [];

// 2. 處理表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '') {
        $errors[] = '請輸入評審姓名。';
    }
    if ($phone === '') {
        $errors[] = '請輸入聯絡電話。';
    }

    if (empty($errors)) {
        $sql = "UPDATE judge
                SET Name = :name, Phone = :phone
                WHERE JudgeID = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name'  => $name,
            ':phone' => $phone,
            ':id'    => $judge_id
        ]);
        header('Location: edit_judge.php?id=' . urlencode($judge_id) . '&success=1');
        exit;
    }
}

// 3. 讀取原始
$sql = "SELECT Name, Phone FROM judge WHERE JudgeID = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $judge_id]);
$judge = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$judge) {
    die("找不到該評審");
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>編輯評審 <?= htmlspecialchars($judge_id) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    /* 同 edit_student.php 的樣式 */
    body { font-family: Arial, sans-serif; background: #eef3fa; color: #333; }
    .container { max-width: 600px; margin: 60px auto; background: #fff; padding: 30px;
                 border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position:relative; }
    h1 { color: #0057b8; margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { font-weight: bold; display: block; margin-bottom: 5px; }
    .form-group input { width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; }
    .errors { background:#fdecea; border:1px solid #f5c2c0; color:#b33a3a;
              padding:10px; border-radius:4px; margin-bottom:15px; }
    .success { background:#e6ffed; border:1px solid #b7eb8f; color:#389e0d;
               padding:10px; border-radius:4px; margin-bottom:15px; }
    .btn-submit { background:#0073e6; color:#fff; border:none; padding:10px 20px;
                  border-radius:4px; cursor:pointer; }
    .btn-submit:hover { background:#005bb5; }
    .btn-back, .btn-prev { text-decoration:none; color:#0073e6; }
    .btn-prev { position:absolute; bottom:20px; right:20px; background:#0057b8;
                color:#fff; padding:6px 12px; border-radius:4px; }
    .btn-prev:hover { background:#004080; }
  </style>
</head>
<body>
  <div class="container">
    <h1>編輯評審 <?= htmlspecialchars($judge_id) ?></h1>

    <?php if ($success): ?>
      <div class="success">✅ 資料更新成功！</div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="edit_judge.php?id=<?= urlencode($judge_id) ?>">
      <div class="form-group">
        <label for="name">姓名</label>
        <input type="text" id="name" name="name"
               value="<?= htmlspecialchars($judge['Name']) ?>" required>
      </div>
      <div class="form-group">
        <label for="phone">電話</label>
        <input type="text" id="phone" name="phone"
               value="<?= htmlspecialchars($judge['Phone']) ?>" required>
      </div>
      <button type="submit" class="btn-submit">更新</button>
      <a href="admin_dashboard.php" class="btn-back">取消</a>
    </form>

    <a href="admin_dashboard.php" class="btn-prev">⏎ 回上一頁</a>
  </div>
</body>
</html>
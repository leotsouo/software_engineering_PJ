<?php
// add_user.php
require_once 'db_connection.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// 欄位設定
$roles = [
    'teacher' => [
        'table'   => 'teacher',
        'fields'  => ['TeacherID','Name','Phone','Email','Password'],
        'labels'  => ['TeacherID'=>'教師帳號','Name'=>'姓名','Phone'=>'電話','Email'=>'Email','Password'=>'密碼']
    ],
    'student' => [
        'table'   => 'teammember',
        'fields'  => ['MemberID','TeamID','Name','StudentID','Gender','Phone','Email'],
        'labels'  => ['MemberID'=>'成員ID','TeamID'=>'隊伍ID','Name'=>'姓名','StudentID'=>'學號','Gender'=>'性別','Phone'=>'電話','Email'=>'Email']
    ],
    'judge' => [
        'table'   => 'judge',
        'fields'  => ['JudgeID','Name','Title','Phone','Email'],
        'labels'  => ['JudgeID'=>'評審帳號','Name'=>'姓名','Title'=>'職稱','Phone'=>'電話','Email'=>'Email']
    ],
];

$errors = [];
$role   = $_POST['role'] ?? '';
$data   = $_POST;  // POST 資料暫存

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 驗證身份
    if (!isset($roles[$role])) {
        $errors[] = '請選擇有效的身份。';
    } else {
        // 驗證必填欄位
        foreach ($roles[$role]['fields'] as $field) {
            if (empty(trim($data[$field] ?? ''))) {
                $errors[] = "請輸入「" . $roles[$role]['labels'][$field] . "」。";
            }
        }
    }

    // 無誤時寫入
    if (empty($errors)) {
        $cfg     = $roles[$role];
        $fields  = $cfg['fields'];
        $cols    = implode(',', $fields);
        $phs     = implode(',', array_map(fn($f)=>":$f",$fields));
        $sql     = "INSERT INTO {$cfg['table']} ($cols) VALUES ($phs)";
        $stmt    = $pdo->prepare($sql);
        $params  = [];
        foreach ($fields as $f) {
            $params[":$f"] = $data[$f];
        }
        try {
            $stmt->execute($params);
            header('Location: admin_dashboard.php');
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $errors[] = '此帳號已存在。';
            } else {
                $errors[] = '資料庫錯誤：' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>新增帳號</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: Arial, sans-serif; background: #eef3fa; color: #333; }
    .container { max-width: 600px; margin: 60px auto; background: #fff; padding: 30px;
                 border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    h1 { color: #0057b8; margin-bottom: 20px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display:block; font-weight:bold; margin-bottom:5px; }
    .form-group input, .form-group select {
      width:100%; padding:8px; border:1px solid #ccc; border-radius:4px;
    }
    .errors { background:#fdecea; border:1px solid #f5c2c0; color:#b33a3a;
              padding:10px; border-radius:4px; margin-bottom:15px; }
    .btn-submit { background:#0073e6; color:#fff; border:none; padding:10px 20px;
                  border-radius:4px; cursor:pointer; }
    .btn-submit:hover { background:#005bb5; }
    .btn-back { display:inline-block; margin-top:15px; color:#0073e6; text-decoration:none; }
    .btn-back:hover { text-decoration:underline; }
  </style>
</head>
<body>
  <div class="container">
    <h1>新增帳號</h1>

    <?php if ($errors): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="add_user.php">
      <div class="form-group">
        <label for="role">身份</label>
        <select id="role" name="role" required onchange="this.form.submit()">
          <option value="">請選擇身份</option>
          <?php foreach ($roles as $key => $cfg): ?>
            <option value="<?= $key ?>" <?= $role === $key ? 'selected' : '' ?>>
              <?= $cfg['table'] === 'teacher' ? '教師'
                   : ($cfg['table'] === 'teammember' ? '學生' : '評審') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (isset($roles[$role])): ?>
        <?php foreach ($roles[$role]['fields'] as $field): ?>
          <div class="form-group">
            <label for="<?= $field ?>"><?= $roles[$role]['labels'][$field] ?></label>

            <?php if ($field === 'Gender'): ?>
              <select id="Gender" name="Gender" required>
                <option value="">請選擇性別</option>
                <option value="Male"   <?= (isset($data['Gender']) && $data['Gender']==='Male')   ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= (isset($data['Gender']) && $data['Gender']==='Female') ? 'selected' : '' ?>>Female</option>
                <option value="Other"  <?= (isset($data['Gender']) && $data['Gender']==='Other')  ? 'selected' : '' ?>>Other</option>
              </select>
            <?php else: ?>
              <input type="text"
                     id="<?= $field ?>"
                     name="<?= $field ?>"
                     value="<?= htmlspecialchars($data[$field] ?? '') ?>"
                     required>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <button type="submit" class="btn-submit">送出</button>
      <a href="admin_dashboard.php" class="btn-back">« 回到後台首頁</a>
    </form>
  </div>
</body>
</html>
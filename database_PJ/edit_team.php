<?php
// edit_team.php
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

// 讀老師清單
try {
    $teachers = $pdo
        ->query("SELECT TeacherID, Name FROM teacher ORDER BY Name")
        ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("無法讀取老師：".$e->getMessage());
}

// 取得該隊伍原始資料
$stmt = $pdo->prepare("SELECT TeamID, competition_category, TeamName, RegistrationDate, TeacherID FROM team WHERE TeamID = :id");
$stmt->execute([':id' => $teamID]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$team) {
    die("找不到該隊伍");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['competition_category'] ?? '');
    $teamName = trim($_POST['team_name'] ?? '');
    $regDate  = trim($_POST['registration_date'] ?? '');
    $teacher  = trim($_POST['teacher_id'] ?? '');

    // 驗證
    if ($category === '') {
        $errors[] = '請選擇競賽組別。';
    }
    if ($teamName === '') {
        $errors[] = '請輸入隊伍名稱。';
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $regDate)) {
        $errors[] = '請輸入有效的報名日期 (YYYY-MM-DD)。';
    }
    if ($teacher === '') {
        $errors[] = '請選擇指導老師。';
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE team
                    SET competition_category = :cat,
                        TeamName            = :tname,
                        RegistrationDate    = :rdate,
                        TeacherID           = :tid
                    WHERE TeamID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cat'   => $category,
                ':tname' => $teamName,
                ':rdate' => $regDate,
                ':tid'   => $teacher,
                ':id'    => $teamID
            ]);
            header('Location: admin_dashboard.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = '更新失敗：' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯隊伍 #<?= htmlspecialchars($teamID) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #eef3fa; color: #333; }
        .container { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        h1 { color: #0057b8; margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-top: 15px; }
        input, select { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px; }
        .errors { background: #fdecea; border: 1px solid #f5c2c0; color: #b33a3a; padding: 12px; border-radius: 4px; }
        .btn { margin-top: 20px; padding: 10px 20px; background: #0073e6; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #005bb5; }
        .btn-back { margin-left: 10px; background: #ccc; color: #333; }
        .btn-back:hover { background: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>編輯隊伍 #<?= htmlspecialchars($teamID) ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post">
            <label for="competition_category">競賽組別</label>
            <select id="competition_category" name="competition_category" required>
                <option value="">請選擇</option>
                <?php foreach (['創意發想組','創業實作組'] as $opt): ?>
                    <option value="<?= $opt ?>"
                        <?= $opt === $team['competition_category'] ? 'selected' : '' ?>>
                        <?= $opt ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="team_name">隊伍名稱</label>
            <input id="team_name" type="text" name="team_name"
                   value="<?= htmlspecialchars($team['TeamName']) ?>" required>

            <label for="registration_date">報名日期</label>
            <input id="registration_date" type="date" name="registration_date"
                   value="<?= htmlspecialchars($team['RegistrationDate']) ?>" required>

            <label for="teacher_id">指導老師</label>
            <select id="teacher_id" name="teacher_id" required>
                <option value="">請選擇</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?= $t['TeacherID'] ?>"
                        <?= $t['TeacherID'] == $team['TeacherID'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn">更新</button>
            <a href="admin_dashboard.php" class="btn btn-back">取消</a>
        </form>
    </div>
</body>
</html>
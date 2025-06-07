<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['judge_id'])) {
    header("Location: login_judge.php");
    exit();
}

$judge_name = $_SESSION['judge_name'];

// 撈隊伍
$team_stmt = $pdo->query("SELECT TeamID, TeamName, competition_category FROM team");
$teams = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

// 撈評分標準
$criteria_stmt = $pdo->query("SELECT * FROM criteria ORDER BY id ASC");
$criteria_list = $criteria_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>評審主頁面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
            min-height: 100vh;
            margin: 0;
        }
        .dashboard-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            width: 900px;
        }
        h2 {
            color: #0057b8;
            text-align: center;
            margin-bottom: 10px;
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-group {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-group button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 15px;
            border: none;
            border-radius: 8px;
            background-color: #0073e6;
            color: white;
            cursor: pointer;
        }
        .button-group button:hover {
            background-color: #004a99;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #dceeff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        textarea {
            resize: vertical;
        }
        input[type="submit"] {
            padding: 12px 20px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #004a99;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h2>評審主頁面</h2>
    <div class="welcome">歡迎，<?= htmlspecialchars($judge_name) ?>！</div>

    <div class="button-group">
        <button onclick="showSection('teams')">隊伍列表</button>
        <button onclick="showSection('addScore')">新增評分</button>
    </div>

    <!-- 隊伍列表 -->
    <div id="section-teams" class="section active">
        <h4>隊伍列表</h4>
        <table>
            <thead>
                <tr>
                    <th>隊伍 ID</th>
                    <th>隊伍名稱</th>
                    <th>競賽組別</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teams as $row): ?>
                    <tr>
                        <td><?= $row['TeamID'] ?></td>
                        <td><?= htmlspecialchars($row['TeamName']) ?></td>
                        <td><?= htmlspecialchars($row['competition_category']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 新增評分 -->
    <div id="section-addScore" class="section">
        <form method="POST" action="submit_score.php">
            <div class="form-group">
                <label for="team_id">選擇要評分的隊伍：</label>
                <select name="team_id" id="team_id" required>
                    <option value="" disabled selected>請選擇隊伍</option>
                    <?php foreach ($teams as $row): ?>
                        <option value="<?= $row['TeamID'] ?>"><?= htmlspecialchars($row['TeamName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (empty($criteria_list)): ?>
                <p style="color:red;">⚠️ 尚未設定評分標準，請先至 criteria_manage.php 新增</p>
            <?php else: ?>
                <?php foreach ($criteria_list as $crit): ?>
                    <div class="form-group">
                        <label><?= htmlspecialchars($crit['name']) ?>：</label>
                        <input type="number" name="scores[<?= $crit['id'] ?>]" min="0" max="100" required>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="form-group">
                <label for="comment">總評論：</label>
                <textarea name="comment" id="comment" rows="4" required></textarea>
            </div>

            <input type="submit" value="提交評分">
        </form>
    </div>
</div>

<script>
function showSection(section) {
    document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
    document.getElementById('section-' + section).classList.add('active');
}
</script>
</body>
</html>

<?php
session_start();

// 確保有統整資料
if (!isset($_SESSION['confirmation_data'])) {
    header('Location: index.php');
    exit;
}

// 獲取資料
$data = $_SESSION['confirmation_data'];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>報名確認</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }
        h1 {
            color: #0057b8;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #0057b8;
            color: white;
        }
        .btn {
            display: inline-block;
            background-color: #0073e6;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            text-align: center;
            display: block;
            max-width: 200px;
            margin: 20px auto;
        }
        .btn:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>報名成功</h1>
        <h2>隊伍資訊</h2>
        <table>
            <tr>
                <th>隊伍名稱</th>
                <td><?= htmlspecialchars($data['team_name']) ?></td>
            </tr>
            <tr>
                <th>報名日期</th>
                <td><?= htmlspecialchars($data['registration_date']) ?></td>
            </tr>
            <tr>
                <th>競賽組別</th>
                <td><?= htmlspecialchars($data['competition_category']) ?></td>
            </tr>
            <tr>
                <th>專案名稱</th>
                <td><?= htmlspecialchars($data['project_name']) ?></td>
            </tr>
            <tr>
                <th>專案摘要</th>
                <td><?= nl2br(htmlspecialchars($data['project_summary'])) ?></td>
            </tr>
        </table>

        <h2>隊員資訊</h2>
        <table>
            <thead>
                <tr>
                    <th>姓名</th>
                    <th>學號</th>
                    <th>性別</th>
                    <th>電話</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['members'] as $member): ?>
                    <tr>
                        <td><?= htmlspecialchars($member['name']) ?></td>
                        <td><?= htmlspecialchars($member['student_id']) ?></td>
                        <td><?= htmlspecialchars($member['gender']) ?></td>
                        <td><?= htmlspecialchars($member['phone']) ?></td>
                        <td><?= htmlspecialchars($member['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="admin_dashboard.php" class="btn">回後台管理</a>
    </div>
</body>
</html>

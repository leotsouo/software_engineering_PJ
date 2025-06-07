<?php
session_start();
require_once 'db_connection.php';

// 確保學生登入（team 身份）
if (!isset($_SESSION['team_id'])) {
    header("Location: login_student.php");
    exit();
}

$team_id = $_SESSION['team_id'];

// 撈出該隊伍的所有評語與總分
$stmt = $pdo->prepare("
    SELECT s.ScoreValue, s.Comment, j.Name AS JudgeName, s.Timestamp
    FROM score s
    JOIN judge j ON s.JudgeID = j.JudgeID
    WHERE s.TeamID = :team_id
    ORDER BY s.Timestamp DESC
");
$stmt->execute(['team_id' => $team_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 計算平均分
$total = 0;
$count = count($feedbacks);
foreach ($feedbacks as $f) {
    $total += intval($f['ScoreValue']);
}
$average = $count > 0 ? round($total / $count, 2) : 0;
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>評語與總分查詢</title>
    <style>
        body {
            background-color: #f4f8fc;
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .container {
            background: white;
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        h2 {
            color: #0057b8;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #dceeff;
        }
        .avg {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
        }
        a {
            display: inline-block;
            margin-top: 25px;
            text-decoration: none;
            background-color: #0073e6;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
        }
        a:hover {
            background-color: #004a99;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>評審給您的回饋</h2>

        <?php if (empty($feedbacks)): ?>
            <p>目前尚無評語與分數，請稍後再試。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>評審</th>
                        <th>分數</th>
                        <th>評論</th>
                        <th>時間</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['JudgeName']) ?></td>
                            <td><?= $f['ScoreValue'] ?> 分</td>
                            <td><?= nl2br(htmlspecialchars($f['Comment'])) ?></td>
                            <td><?= $f['Timestamp'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="avg">⭐ 總平均：<?= $average ?> 分</div>
        <?php endif; ?>

        <a href="index.php">回首頁</a>
    </div>
</body>
</html>

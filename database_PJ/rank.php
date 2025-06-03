<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

try {
    // 查詢創意發想組的成績
    $sql_creative = "
        SELECT 
            t.TeamID,
            t.TeamName,
            t.competition_category,
            IFNULL(SUM(s.ScoreValue) / COUNT(DISTINCT s.JudgeID), 0) AS AverageScore
        FROM 
            team t
        LEFT JOIN 
            score s ON t.TeamID = s.TeamID
        WHERE 
            t.competition_category = '創意發想組'
        GROUP BY 
            t.TeamID
        ORDER BY 
            AverageScore DESC";
    $stmt_creative = $pdo->prepare($sql_creative);
    $stmt_creative->execute();
    $creative_scores = $stmt_creative->fetchAll(PDO::FETCH_ASSOC);

    // 更新創意發想組的組別排名
    $rank = 1;
    foreach ($creative_scores as $team) {
        $sql_update_rank = "UPDATE team SET Rank = :rank WHERE TeamID = :team_id";
        $stmt_update = $pdo->prepare($sql_update_rank);
        $stmt_update->bindParam(':rank', $rank, PDO::PARAM_INT);
        $stmt_update->bindParam(':team_id', $team['TeamID'], PDO::PARAM_INT);
        $stmt_update->execute();
        $rank++;
    }

    // 查詢創業實作組的成績
    $sql_entrepreneur = "
        SELECT 
            t.TeamID,
            t.TeamName,
            t.competition_category,
            IFNULL(SUM(s.ScoreValue) / COUNT(DISTINCT s.JudgeID), 0) AS AverageScore
        FROM 
            team t
        LEFT JOIN 
            score s ON t.TeamID = s.TeamID
        WHERE 
            t.competition_category = '創業實作組'
        GROUP BY 
            t.TeamID
        ORDER BY 
            AverageScore DESC";
    $stmt_entrepreneur = $pdo->prepare($sql_entrepreneur);
    $stmt_entrepreneur->execute();
    $entrepreneur_scores = $stmt_entrepreneur->fetchAll(PDO::FETCH_ASSOC);

    // 更新創業實作組的組別排名
    $rank = 1;
    foreach ($entrepreneur_scores as $team) {
        $sql_update_rank = "UPDATE team SET Rank = :rank WHERE TeamID = :team_id";
        $stmt_update = $pdo->prepare($sql_update_rank);
        $stmt_update->bindParam(':rank', $rank, PDO::PARAM_INT);
        $stmt_update->bindParam(':team_id', $team['TeamID'], PDO::PARAM_INT);
        $stmt_update->execute();
        $rank++;
    }
} catch (PDOException $e) {
    die("資料獲取失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>成績與排名</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        h2 {
            color: #0073e6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background-color: #0057b8;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .rank {
            font-weight: bold;
            color: #0057b8;
        }
        .btn {
            background-color: #0073e6;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
            position: fixed;
            right: 20px;
            bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>成績與排名</h1>

        <!-- 創意發想組 -->
        <h2>創意發想組</h2>
        <table>
            <thead>
                <tr>
                    <th>排名</th>
                    <th>隊伍名稱</th>
                    <th>平均分數</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($creative_scores)): ?>
                    <tr>
                        <td colspan="3">目前無隊伍資料。</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($creative_scores as $index => $team): ?>
                        <tr>
                            <td class="rank"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($team['TeamName']) ?></td>
                            <td><?= number_format($team['AverageScore'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 創業實作組 -->
        <h2>創業實作組</h2>
        <table>
            <thead>
                <tr>
                    <th>排名</th>
                    <th>隊伍名稱</th>
                    <th>平均分數</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($entrepreneur_scores)): ?>
                    <tr>
                        <td colspan="3">目前無隊伍資料。</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($entrepreneur_scores as $index => $team): ?>
                        <tr>
                            <td class="rank"><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($team['TeamName']) ?></td>
                            <td><?= number_format($team['AverageScore'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <!-- 回首頁按鈕 -->
        <a href="index.php" class="btn">回首頁</a>
    </div>
</body>
</html>

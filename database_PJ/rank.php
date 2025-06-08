<?php
require_once 'db_connection.php';

$keyword = $_GET['keyword'] ?? '';
$category = $_GET['category'] ?? '';

try {
    $where = "WHERE 1=1";
    $params = [];

    if ($category !== '') {
        $where .= " AND t.competition_category = ?";
        $params[] = $category;
    }

    if ($keyword !== '') {
        $where .= " AND t.TeamName LIKE ?";
        $params[] = "%$keyword%";
    }

    $sql = "
        SELECT 
            t.TeamID,
            t.TeamName,
            t.competition_category,
            IFNULL(SUM(s.ScoreValue) / COUNT(DISTINCT s.JudgeID), 0) AS AverageScore
        FROM 
            team t
        LEFT JOIN 
            score s ON t.TeamID = s.TeamID
        $where
        GROUP BY 
            t.TeamID, t.competition_category
        ORDER BY 
            t.competition_category, AverageScore DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grouped = ['創意發想組' => [], '創業實作組' => []];
    foreach ($all_scores as $row) {
        $grouped[$row['competition_category']][] = $row;
    }
} catch (PDOException $e) {
    die("資料獲取失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>成績與排名</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #0057b8;
            text-align: center;
        }

        h2 {
            color: #0073e6;
            margin-top: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
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
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #005bb5;
        }

        .filter {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter input,
        .filter select {
            padding: 6px 10px;
            margin-right: 10px;
        }

        .no-result {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin: 40px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>成績與排名</h1>

        <div class="filter">
            <form method="get">
                <select name="category">
                    <option value="">全部組別</option>
                    <option value="創意發想組" <?= $category === '創意發想組' ? 'selected' : '' ?>>創意發想組</option>
                    <option value="創業實作組" <?= $category === '創業實作組' ? 'selected' : '' ?>>創業實作組</option>
                </select>
                <input type="text" name="keyword" placeholder="搜尋隊伍名稱" value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">搜尋</button>
            </form>
        </div>

        <?php if (empty($grouped['創意發想組']) && empty($grouped['創業實作組'])): ?>
            <div class="no-result">🔍 查無符合條件的隊伍資料</div>
        <?php else: ?>
            <?php foreach ($grouped as $group_name => $teams): ?>
                <?php if (!empty($teams)): ?>
                    <h2><?= htmlspecialchars($group_name) ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>排名</th>
                                <th>隊伍名稱</th>
                                <th>平均分數</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teams as $index => $team): ?>
                                <tr>
                                    <td class="rank"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($team['TeamName']) ?></td>
                                    <td><?= number_format($team['AverageScore'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align:center;">
            <a href="index.php" class="btn">回首頁</a>
        </div>
    </div>
</body>

</html>
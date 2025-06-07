<?php
require_once 'db_connection.php';
try {
    $sql = "SELECT PastTeamID, ProjectName, PostYear FROM past_projects ORDER BY PostYear DESC";
    $stmt = $pdo->query($sql);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("讀取資料錯誤：" . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>歷屆作品展示區</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #0057b8;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #0057b8;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0073e6;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #005bb5;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>歷屆作品展示區</h1>
        <?php if (empty($projects)): ?>
            <p>目前尚無歷屆作品資料。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>隊伍 ID</th>
                        <th>作品名稱</th>
                        <th>發表年份</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['PastTeamID']) ?></td>
                            <td><?= htmlspecialchars($p['ProjectName']) ?></td>
                            <td><?= htmlspecialchars($p['PostYear']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="index.php" class="btn-back">回首頁</a>
    </div>
</body>

</html>
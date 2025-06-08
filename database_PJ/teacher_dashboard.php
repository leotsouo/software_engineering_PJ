<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

session_start(); // 啟用 session

// 確保教師已登入
if (!isset($_SESSION['teacher_id'])) {
    header('Location: login_teacher.php');
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // 獲取教師帶領的隊伍資料
    $sql = "SELECT TeamID, TeamName, competition_category 
            FROM team 
            WHERE TeacherID = :teacher_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $stmt->execute();
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC); // 獲取隊伍資料

    // 獲取歷屆作品資料
    $view_past_projects = isset($_GET['view_past_projects']) && $_GET['view_past_projects'] == '1';
    $past_projects = [];
    if ($view_past_projects) {
        $sql_past_projects = "SELECT PastTeamID, ProjectName, PostYear 
                              FROM past_projects";
        $stmt_past_projects = $pdo->query($sql_past_projects);
        $past_projects = $stmt_past_projects->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------ 歷屆作品查詢 ------------------
    $view_past_projects = isset($_GET['view_past_projects']) && $_GET['view_past_projects'] === '1';
    $past_projects = [];
    if ($view_past_projects) {
        $sql_pp = "SELECT PastTeamID, ProjectName, PostYear FROM past_projects";
        $stmt_pp = $pdo->query($sql_pp);
        $past_projects = $stmt_pp->fetchAll(PDO::FETCH_ASSOC);
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
    <title>教師主頁面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #0057b8;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
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
        .message {
            text-align: center;
            font-size: 16px;
            color: #333;
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
            margin-bottom: 20px;
        }
        .btn:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>
    <header>
        <h1>教師主頁面</h1>
    </header>
    <div class="container">
        <h2>目前帶領的隊伍</h2>
        <?php if (empty($teams)): ?>
            <p class="message">尚未有隊伍。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>隊伍 ID</th>
                        <th>隊伍名稱</th>
                        <th>競賽組別</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td><?= htmlspecialchars($team['TeamID']) ?></td>
                            <td><?= htmlspecialchars($team['TeamName']) ?></td>
                            <td><?= htmlspecialchars($team['competition_category']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- 查詢歷屆作品按鈕 -->
        <form method="GET" action="">
            <input type="hidden" name="view_past_projects" value="<?= $view_past_projects ? '0' : '1' ?>">
            <button type="submit" class="btn">
                <?= $view_past_projects ? '隱藏歷屆作品' : '查詢歷屆作品' ?>
            </button>
        </form>

        <!-- 歷屆作品表格 -->
        <?php if ($view_past_projects && !empty($past_projects)): ?>
            <h2>歷屆作品</h2>
            <table>
                <thead>
                    <tr>
                        <th>PastTeamID</th>
                        <th>作品名稱</th>
                        <th>發表年份</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($past_projects as $project): ?>
                        <tr>
                            <td><?= htmlspecialchars($project['PastTeamID']) ?></td>
                            <td><?= htmlspecialchars($project['ProjectName']) ?></td>
                            <td><?= htmlspecialchars($project['PostYear']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- 回首頁按鈕 -->
        <a href="logout.php" class="btn">登出回首頁</a>
    </div>
</body>
</html>

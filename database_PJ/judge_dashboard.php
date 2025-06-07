<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

session_start(); // 啟用 session

// 確保評審已登入
if (!isset($_SESSION['judge_id'])) {
    header('Location: login_judge.php');
    exit;
}

$judge_id = $_SESSION['judge_id'];
$judge_name = $_SESSION['judge_name'];

$message = ''; // 儲存提示訊息

try {
    // 獲取所有隊伍列表
    $sql_teams = "SELECT TeamID, TeamName, competition_category FROM team";
    $stmt_teams = $pdo->query($sql_teams);
    $teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);

    // 獲取 submission 資料
    $sql_submissions = "
        SELECT s.SubmissionID, s.TeamID, t.TeamName, s.Description, s.PosterLink, s.VideoLink, s.CodeLink, s.UploadDate 
        FROM submission s
        JOIN team t ON s.TeamID = t.TeamID";
    $stmt_submissions = $pdo->query($sql_submissions);
    $submissions = $stmt_submissions->fetchAll(PDO::FETCH_ASSOC);

    // 新增評分
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $team_id = $_POST['team_id'];
        $score_value = $_POST['score_value'];
        $comment = $_POST['comment'];

        // 檢查是否已經評分過
        $sql_check_score = "SELECT 1 FROM score WHERE JudgeID = :judge_id AND TeamID = :team_id";
        $stmt_check = $pdo->prepare($sql_check_score);
        $stmt_check->bindParam(':judge_id', $judge_id, PDO::PARAM_INT);
        $stmt_check->bindParam(':team_id', $team_id, PDO::PARAM_INT);
        $stmt_check->execute();

        if ($stmt_check->fetch()) {
            $message = "無法重複評分！";
        } else {
            // 插入評分資料
            $sql_insert_score = "INSERT INTO score (JudgeID, TeamID, ScoreValue, Comment) 
                                 VALUES (:judge_id, :team_id, :score_value, :comment)";
            $stmt_insert = $pdo->prepare($sql_insert_score);
            $stmt_insert->bindParam(':judge_id', $judge_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':team_id', $team_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':score_value', $score_value, PDO::PARAM_INT);
            $stmt_insert->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt_insert->execute();

            $message = "評分已成功提交！";
        }
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
    <title>評審主頁面</title>
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

        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* ✅ 登出按鈕右上角 */
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 30px;
            background-color: #007BFF;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            z-index: 1000;
        }

        .logout-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <a href="index.php" class="logout-btn">登出</a>
    <header>
        <h1>評審主頁面</h1>
        <p>歡迎，<?= htmlspecialchars($judge_name) ?>！</p>
    </header>
    <div class="container">
        <?php if (!empty($message)): ?>
            <p class="<?= strpos($message, '無法') !== false ? 'error-message' : 'message' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>

        <h2>隊伍列表</h2>
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

        <h2>新增評分</h2>
        <form method="POST" action="">
            <label for="team_id">選擇隊伍：</label>
            <select id="team_id" name="team_id" required>
                <option value="">請選擇隊伍</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= htmlspecialchars($team['TeamID']) ?>"><?= htmlspecialchars($team['TeamName']) ?> (<?= htmlspecialchars($team['competition_category']) ?>)</option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label for="score_value">評分：</label>
            <input type="number" id="score_value" name="score_value" min="0" max="100" required>
            <br><br>
            <label for="comment">評論：</label>
            <textarea id="comment" name="comment" rows="4" cols="50" required></textarea>
            <br><br>
            <button type="submit" class="btn">提交評分</button>
        </form>

        <h2>目前已提交作品</h2>
        <table>
            <thead>
                <tr>
                    <th>作品 ID</th>
                    <th>隊伍名稱</th>
                    <th>描述</th>
                    <th>海報</th>
                    <th>影片</th>
                    <th>程式碼</th>
                    <th>上傳日期</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td><?= htmlspecialchars($submission['SubmissionID']) ?></td>
                        <td><?= htmlspecialchars($submission['TeamName']) ?></td>
                        <td><?= htmlspecialchars($submission['Description']) ?></td>
                        <td><a href="<?= htmlspecialchars($submission['PosterLink']) ?>" target="_blank">海報連結</a></td>
                        <td><a href="<?= htmlspecialchars($submission['VideoLink']) ?>" target="_blank">影片連結</a></td>
                        <td><a href="<?= htmlspecialchars($submission['CodeLink']) ?>" target="_blank">程式碼連結</a></td>
                        <td><?= htmlspecialchars($submission['UploadDate']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</body>

</html>
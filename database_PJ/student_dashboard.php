<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

session_start(); // 啟用 session

// 確保學生已登入
if (!isset($_SESSION['student_id'])) {
    header('Location: login_student.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';

try {
    // 獲取學生的名稱
    $sql_student = "SELECT Name FROM teammember WHERE StudentID = :student_id";
    $stmt_student = $pdo->prepare($sql_student);
    $stmt_student->bindParam(':student_id', $student_id, PDO::PARAM_STR);
    $stmt_student->execute();
    $student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    // 獲取學生所屬隊伍資料
    $sql_team = "SELECT t.TeamID, t.TeamName, t.competition_category, t.RegistrationDate, t.Rank 
                 FROM team t
                 INNER JOIN teammember tm ON t.TeamID = tm.TeamID
                 WHERE tm.StudentID = :student_id";
    $stmt_team = $pdo->prepare($sql_team);
    $stmt_team->bindParam(':student_id', $student_id, PDO::PARAM_STR);
    $stmt_team->execute();
    $team = $stmt_team->fetch(PDO::FETCH_ASSOC);

    // 處理上傳表單提交
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_submission'])) {
        $description = $_POST['description'];
        $poster_link = $_POST['poster_link'];
        $video_link = $_POST['video_link'];
        $code_link = $_POST['code_link'];
        $upload_date = date('Y-m-d H:i:s');

        if ($team) {
            $team_id = $team['TeamID'];

            // 檢查是否已提交作品
            $sql_check = "SELECT COUNT(*) FROM submission WHERE TeamID = :team_id";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->bindParam(':team_id', $team_id, PDO::PARAM_INT);
            $stmt_check->execute();
            $already_submitted = $stmt_check->fetchColumn();

            if ($already_submitted > 0) {
                $message = "您的隊伍已經提交過作品，無法重複提交！";
            } else {
                // 插入資料到 submission 表
                $sql_insert = "INSERT INTO submission (TeamID, Description, PosterLink, VideoLink, CodeLink, UploadDate) 
                               VALUES (:team_id, :description, :poster_link, :video_link, :code_link, :upload_date)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->bindParam(':team_id', $team_id, PDO::PARAM_INT);
                $stmt_insert->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt_insert->bindParam(':poster_link', $poster_link, PDO::PARAM_STR);
                $stmt_insert->bindParam(':video_link', $video_link, PDO::PARAM_STR);
                $stmt_insert->bindParam(':code_link', $code_link, PDO::PARAM_STR);
                $stmt_insert->bindParam(':upload_date', $upload_date, PDO::PARAM_STR);
                $stmt_insert->execute();

                $message = "作品資料已成功上傳！";
            }
        } else {
            $message = "無法上傳，請先加入隊伍！";
        }
    }
} catch (PDOException $e) {
    $message = "資料獲取失敗: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header .welcome {
            font-size: 16px;
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="welcome">
            歡迎，<?= htmlspecialchars($student['Name']) ?>！
        </div>
    </header>
    <div class="container">
        <h2>所屬隊伍</h2>
        <?php if ($team): ?>
            <table>
                <tr>
                    <th>隊伍 ID</th>
                    <td><?= htmlspecialchars($team['TeamID']) ?></td>
                </tr>
                <tr>
                    <th>隊伍名稱</th>
                    <td><?= htmlspecialchars($team['TeamName']) ?></td>
                </tr>
                <tr>
                    <th>競賽組別</th>
                    <td><?= htmlspecialchars($team['competition_category']) ?></td>
                </tr>
                <tr>
                    <th>報名日期</th>
                    <td><?= htmlspecialchars($team['RegistrationDate']) ?></td>
                </tr>
                <tr>
                    <th>排名</th>
                    <td><?= htmlspecialchars($team['Rank'] !== null ? $team['Rank'] : '未排名') ?></td>
                </tr>
            </table>
        <?php else: ?>
            <p>目前尚未加入任何隊伍。</p>
        <?php endif; ?>

        <!-- 上傳作品表單 -->
        <h2>上傳作品</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="description">作品描述 (500字以內)：</label>
                <textarea id="description" name="description" rows="4" maxlength="500" required></textarea>
            </div>
            <div class="form-group">
                <label for="poster_link">海報連結：</label>
                <input type="url" id="poster_link" name="poster_link" required>
            </div>
            <div class="form-group">
                <label for="video_link">影片連結：</label>
                <input type="url" id="video_link" name="video_link" required>
            </div>
            <div class="form-group">
                <label for="code_link">程式碼連結 (GitHub)：</label>
                <input type="url" id="code_link" name="code_link" required>
            </div>
            <button type="submit" name="submit_submission" class="btn">提交</button>
        </form>

        <!-- 回首頁按鈕 -->
        <a href="index.php" class="btn">回首頁</a>
    </div>
</body>
</html>

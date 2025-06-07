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

    $all_data = [];

    foreach ($teams as $team) {
        $team_id = $team['TeamID'];

        // 取得隊員資料
        $stmt_students = $pdo->prepare("SELECT Name, StudentID, Phone FROM teammember WHERE TeamID = :team_id");
        $stmt_students->execute([':team_id' => $team_id]);
        $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

        // 取得作品資料
        $stmt_work = $pdo->prepare("SELECT Description, PosterLink, VideoLink, CodeLink, UploadDate FROM submission WHERE TeamID = :team_id");
        $stmt_work->execute([':team_id' => $team_id]);
        $submission = $stmt_work->fetch(PDO::FETCH_ASSOC);

        $all_data[] = [
            'team' => $team,
            'students' => $students,
            'submission' => $submission
        ];
    }


    $sql_submissions = "
        SELECT t.TeamName, s.Description, s.PosterLink, s.VideoLink, s.CodeLink, s.UploadDate 
        FROM team t
        LEFT JOIN submission s ON t.TeamID = s.TeamID 
        WHERE t.TeacherID = :teacher_id AND s.SubmissionID IS NOT NULL
        ORDER BY s.UploadDate DESC
    ";
    $stmt_sub = $pdo->prepare($sql_submissions);
    $stmt_sub->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $stmt_sub->execute();
    $student_works = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);

    // 歷屆作品顯示切換
    $view_past_projects = isset($_GET['view_past_projects']) && $_GET['view_past_projects'] == '1';
    $past_projects = [];
    if ($view_past_projects) {
        $sql_past = "SELECT PastTeamID, ProjectName, PostYear FROM past_projects";
        $stmt_past = $pdo->query($sql_past);
        $past_projects = $stmt_past->fetchAll(PDO::FETCH_ASSOC);
    }
    // 獲取歷屆作品資料
    $view_past_projects = isset($_GET['view_past_projects']) && $_GET['view_past_projects'] == '1';
    $past_projects = [];
    if ($view_past_projects) {
        $sql_past_projects = "SELECT PastTeamID, ProjectName, PostYear 
                              FROM past_projects";
        $stmt_past_projects = $pdo->query($sql_past_projects);
        $past_projects = $stmt_past_projects->fetchAll(PDO::FETCH_ASSOC);
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

        <h2>帶領隊伍與學生資料</h2>
        <?php if (empty($all_data)): ?>
            <p>目前尚未帶領任何隊伍。</p>
        <?php else: ?>
            <?php foreach ($all_data as $entry): ?>
                <div class="card">
                    <h3>隊伍：<?= htmlspecialchars($entry['team']['TeamName']) ?>（<?= htmlspecialchars($entry['team']['competition_category']) ?>）</h3>

                    <strong>學生名單：</strong>
                    <?php if (!empty($entry['students'])): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>姓名</th>
                                    <th>學號</th>
                                    <th>電話</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entry['students'] as $stu): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stu['Name']) ?></td>
                                        <td><?= htmlspecialchars($stu['StudentID']) ?></td>
                                        <td><?= htmlspecialchars($stu['Phone']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>尚無學生資料。</p>
                    <?php endif; ?>

                    <br><strong>學生上傳作品：</strong><br>
                    <?php if ($entry['submission']): ?>
                        <p>上傳時間：<?= htmlspecialchars($entry['submission']['UploadDate']) ?></p>
                        <p>描述：<?= nl2br(htmlspecialchars($entry['submission']['Description'])) ?></p>
                        <div class="links">
                            <?php if (!empty($entry['submission']['PosterLink'])): ?>
                                <a href="<?= $entry['submission']['PosterLink'] ?>" target="_blank">海報</a>
                            <?php endif; ?>
                            <?php if (!empty($entry['submission']['VideoLink'])): ?>
                                <a href="<?= $entry['submission']['VideoLink'] ?>" target="_blank">影片</a>
                            <?php endif; ?>
                            <?php if (!empty($entry['submission']['CodeLink'])): ?>
                                <a href="<?= $entry['submission']['CodeLink'] ?>" target="_blank">程式碼</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #888;">尚未提交作品</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <!-- 回首頁按鈕 -->
        <a href="index.php" class="btn">回首頁</a>
    </div>
</body>

</html>
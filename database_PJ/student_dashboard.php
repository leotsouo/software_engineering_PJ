<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login_student.php');
    exit;
}

$student_id = $_SESSION['student_id'];
$message = '';

try {
    $sql_student = "SELECT Name FROM teammember WHERE StudentID = :student_id";
    $stmt_student = $pdo->prepare($sql_student);
    $stmt_student->bindParam(':student_id', $student_id, PDO::PARAM_STR);
    $stmt_student->execute();
    $student = $stmt_student->fetch(PDO::FETCH_ASSOC);

    $sql_team = "SELECT t.TeamID, t.TeamName, t.competition_category, t.RegistrationDate, t.Rank 
                 FROM team t INNER JOIN teammember tm ON t.TeamID = tm.TeamID 
                 WHERE tm.StudentID = :student_id";
    $stmt_team = $pdo->prepare($sql_team);
    $stmt_team->bindParam(':student_id', $student_id, PDO::PARAM_STR);
    $stmt_team->execute();
    $team = $stmt_team->fetch(PDO::FETCH_ASSOC);

    $submission = null;
    if ($team) {
        $sql_submission = "SELECT * FROM submission WHERE TeamID = :team_id";
        $stmt_submission = $pdo->prepare($sql_submission);
        $stmt_submission->bindParam(':team_id', $team['TeamID'], PDO::PARAM_INT);
        $stmt_submission->execute();
        $submission = $stmt_submission->fetch(PDO::FETCH_ASSOC);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $description = $_POST['description'] ?? '';
        $team_id = $team['TeamID'];
        $upload_date = date('Y-m-d H:i:s');
        $upload_dir = 'uploads/submissions/' . $team_id . '/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $max_size = 10 * 1024 * 1024;

        if (
            isset($_POST['submit_submission']) && (
                $_FILES['poster_file']['size'] > $max_size ||
                $_FILES['video_file']['size'] > $max_size ||
                $_FILES['code_file']['size'] > $max_size
            )
        ) {
            $message = "❌ 上傳失敗：每個檔案大小不得超過 10MB。";
        } elseif (isset($_POST['submit_submission'])) {
            if ($submission) {
                $message = "您的隊伍已經提交過作品，無法重複提交！";
            } else {
                $poster_path = $upload_dir . 'poster_' . basename($_FILES['poster_file']['name']);
                $video_path  = $upload_dir . 'video_' . basename($_FILES['video_file']['name']);
                $code_path   = $upload_dir . 'code_' . basename($_FILES['code_file']['name']);

                move_uploaded_file($_FILES['poster_file']['tmp_name'], $poster_path);
                move_uploaded_file($_FILES['video_file']['tmp_name'],  $video_path);
                move_uploaded_file($_FILES['code_file']['tmp_name'],   $code_path);

                $sql_insert = "INSERT INTO submission (TeamID, Description, PosterLink, VideoLink, CodeLink, UploadDate) 
                               VALUES (:team_id, :description, :poster_link, :video_link, :code_link, :upload_date)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([
                    ':team_id' => $team_id,
                    ':description' => $description,
                    ':poster_link' => $poster_path,
                    ':video_link' => $video_path,
                    ':code_link' => $code_path,
                    ':upload_date' => $upload_date
                ]);
                $message = "✅ 作品資料已成功上傳！";
            }
        } elseif (isset($_POST['update_submission'])) {
            if (
                (!empty($_FILES['poster_file']['name']) && $_FILES['poster_file']['size'] > $max_size) ||
                (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['size'] > $max_size) ||
                (!empty($_FILES['code_file']['name']) && $_FILES['code_file']['size'] > $max_size)
            ) {
                $message = "❌ 更新失敗：每個檔案大小不得超過 10MB。";
            } else {
                $poster_path = $submission['PosterLink'];
                $video_path  = $submission['VideoLink'];
                $code_path   = $submission['CodeLink'];

                if (!empty($_FILES['poster_file']['name'])) {
                    $poster_path = $upload_dir . 'poster_' . basename($_FILES['poster_file']['name']);
                    move_uploaded_file($_FILES['poster_file']['tmp_name'], $poster_path);
                }
                if (!empty($_FILES['video_file']['name'])) {
                    $video_path = $upload_dir . 'video_' . basename($_FILES['video_file']['name']);
                    move_uploaded_file($_FILES['video_file']['tmp_name'], $video_path);
                }
                if (!empty($_FILES['code_file']['name'])) {
                    $code_path = $upload_dir . 'code_' . basename($_FILES['code_file']['name']);
                    move_uploaded_file($_FILES['code_file']['tmp_name'], $code_path);
                }

                $sql_update = "UPDATE submission SET Description = :description, PosterLink = :poster_link, VideoLink = :video_link, CodeLink = :code_link 
                               WHERE TeamID = :team_id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([
                    ':description' => $description,
                    ':poster_link' => $poster_path,
                    ':video_link' => $video_path,
                    ':code_link' => $code_path,
                    ':team_id' => $team_id
                ]);
                $message = "✅ 作品資料已更新！";
            }
        } elseif (isset($_POST['delete_submission'])) {
            if ($submission) {
                if (file_exists($submission['PosterLink'])) unlink($submission['PosterLink']);
                if (file_exists($submission['VideoLink'])) unlink($submission['VideoLink']);
                if (file_exists($submission['CodeLink'])) unlink($submission['CodeLink']);
            }
            $sql_delete = "DELETE FROM submission WHERE TeamID = :team_id";
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->execute([':team_id' => $team_id]);
            $message = "✅ 作品資料與檔案已刪除！";
            $submission = null;
        }

        if ($team) {
            $stmt_submission->execute();
            $submission = $stmt_submission->fetch(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    $message = "資料處理錯誤：" . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <title>學生儀表板</title>
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
            background-color: #0073e6;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 10px;
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

        .form-group input,
        .form-group textarea {
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
    <script>
        function checkFileSize(input) {
            const maxSize = 10 * 1024 * 1024;
            if (input.files.length > 0 && input.files[0].size > maxSize) {
                alert("❌ 檔案太大，請選擇小於 10MB 的檔案！");
                input.value = '';
            }
        }
    </script>
</head>

<body>
    <header>
        <div>歡迎，<?= htmlspecialchars($student['Name']) ?>！</div>
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
                    <td><?= $team['Rank'] !== null ? htmlspecialchars($team['Rank']) : '未排名' ?></td>
                </tr>
            </table>
        <?php else: ?><p>尚未加入任何隊伍。</p><?php endif; ?>

        <h2>作品提交區</h2>
        <?php if (!empty($message)): ?><p class="message"><?= htmlspecialchars($message) ?></p><?php endif; ?>

        <?php if (!$submission): ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submit_submission" value="1">
                <div class="form-group"><label>描述：</label><textarea name="description" required maxlength="500" rows="4"></textarea></div>
                <div class="form-group">（請上傳小於 10MB）<input type="file" name="poster_file" accept=".jpg,.jpeg,.png,.pdf" required onchange="checkFileSize(this)"></div>
                <div class="form-group">（請上傳小於 10MB）<input type="file" name="video_file" accept="video/*" required onchange="checkFileSize(this)"></div>
                <div class="form-group">（請上傳小於 10MB）<input type="file" name="code_file" accept=".zip,.rar,.7z" required onchange="checkFileSize(this)"></div>
                <button type="submit" class="btn">提交作品</button>
            </form>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_submission" value="1">
                <div class="form-group"><label>描述：</label><textarea name="description" required maxlength="500" rows="4"><?= htmlspecialchars($submission['Description']) ?></textarea></div>
                <div class="form-group"><label>海報檔案：</label><input type="file" name="poster_file" accept=".jpg,.jpeg,.png,.pdf" onchange="checkFileSize(this)"></div>
                <div class="form-group"><label>影片檔案：</label><input type="file" name="video_file" accept="video/*" onchange="checkFileSize(this)"></div>
                <div class="form-group"><label>程式碼壓縮檔：</label><input type="file" name="code_file" accept=".zip,.rar,.7z" onchange="checkFileSize(this)"></div>
                <button type="submit" class="btn">更新作品</button>
            </form>

            <form method="POST" onsubmit="return confirm('確定要刪除這份作品嗎？')">
                <input type="hidden" name="delete_submission" value="1">
                <button type="submit" class="btn" style="background-color: #d9534f;">刪除作品</button>
            </form>
        <?php endif; ?>

        <a href="index.php" class="btn">回首頁</a>
    </div>
</body>

</html>
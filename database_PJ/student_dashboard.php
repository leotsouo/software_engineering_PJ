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
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        padding: 14px 26px; /* 放大按鈕 */
        border-radius: 7px;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        font-weight: bold;
        border: none;
        cursor: pointer;
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

    .logout-btn {
        background-color: #007BFF;
        color: white;
        padding: 8px 16px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
    }

    .logout-btn:hover {
        background-color: #0056b3;
    }

    .button-group {
        text-align: center;
        margin-bottom: 20px;
    }

    .button-group .btn {
        margin: 0 10px 12px 0;
        padding: 14px 26px; /* 放大按鈕 */
        font-size: 16px;
        border-radius: 10px;
        background-color: #0073e6;
        color: white;
        border: none;
        cursor: pointer;
    }

    .button-group .btn:hover {
        background-color: #004a99;
    }

    .action-section {
        display: none;
        margin-top: 20px;
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
        <div>歡迎，<?= isset($student['Name']) ? htmlspecialchars($student['Name']) : '學生' ?>！</div>
        <a href="logout.php" class="logout-btn">登出回首頁</a>
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

        <h2>功能</h2>
        <div class="button-group">
            <button class="btn" onclick="toggleSection('submission')">作品提交</button>
            <button class="btn" onclick="toggleSection('update')">更新作品</button>
            <button class="btn" onclick="toggleSection('delete')">刪除作品</button>
            <button class="btn" onclick="toggleSection('download')">下載證書</button>
            <button class="btn" onclick="toggleSection('download')">查看評語</button>
            <form method="POST" action="cancel_registration.php" style="display:inline-block;">
                <button type="submit" class="btn" onclick="return confirm('確定要取消報名嗎？');">取消報名</button>
            </form>
            <a href="view_feedback.php" class="btn" style="margin-left: 8px;">📝 查看評語</a>
        </div>

        <!-- 區塊容器：作品提交 -->
        <div id="section-submission" class="action-section">
            <?php if (!empty($message) && isset($_POST['submit_submission'])): ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submit_submission" value="1">
                <div class="form-group"><label>作品描述：</label><textarea name="description" required maxlength="500" rows="4"></textarea></div>
                <div class="form-group"><label>海報檔案（請上傳小於 10MB）：</label><input type="file" name="poster_file" accept=".jpg,.jpeg,.png,.pdf" required onchange="checkFileSize(this)"></div>
                <div class="form-group"><label>影片檔案（請上傳小於 10MB）：</label><input type="file" name="video_file" accept="video/*" required onchange="checkFileSize(this)"></div>
                <div class="form-group"><label>程式碼壓縮檔（請上傳小於 10MB）：</label><input type="file" name="code_file" accept=".zip,.rar,.7z" required onchange="checkFileSize(this)"></div>
                <button type="submit" class="btn">提交作品</button>
            </form>
        </div>

        <!-- 區塊容器：更新作品 -->
        <div id="section-update" class="action-section">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_submission" value="1">
                <div class="form-group"><label>作品描述：</label><textarea name="description" required maxlength="500" rows="4"><?php if (is_array($submission) && isset($submission['Description'])) echo htmlspecialchars($submission['Description']); ?></textarea></div>
                <div class="form-group">
                    <label>已上傳檔案：</label>
                    <ul>
                        <?php if (!empty($submission['PosterLink'])): ?>
                            <li><a href="<?= htmlspecialchars($submission['PosterLink']) ?>" target="_blank">📄 海報檔案</a></li>
                        <?php endif; ?>
                        <?php if (!empty($submission['VideoLink'])): ?>
                            <li><a href="<?= htmlspecialchars($submission['VideoLink']) ?>" target="_blank">🎬 影片檔案</a></li>
                        <?php endif; ?>
                        <?php if (!empty($submission['CodeLink'])): ?>
                            <li><a href="<?= htmlspecialchars($submission['CodeLink']) ?>" target="_blank">💻 程式碼壓縮檔</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="form-group"><label>海報檔案：</label><input type="file" name="poster_file" accept=".jpg,.jpeg,.png,.pdf" onchange="checkFileSize(this)"></div>
                <div class="form-group"><label>影片檔案：</label><input type="file" name="video_file" accept="video/*" onchange="checkFileSize(this)"></div>
                <div class="form-group"><label>程式碼壓縮檔：</label><input type="file" name="code_file" accept=".zip,.rar,.7z" onchange="checkFileSize(this)"></div>
                <button type="submit" class="btn">更新作品</button>
            </form>
        </div>

        <!-- 區塊容器：刪除作品 -->
        <div id="section-delete" class="action-section">
            <form method="POST" onsubmit="return confirm('確定要刪除這份作品嗎？')">
                <input type="hidden" name="delete_submission" value="1">
                <button type="submit" class="btn" style="background-color: #d9534f;">刪除作品</button>
            </form>
        </div>

        <!-- 區塊容器：下載證書 -->
        <div id="section-download" class="action-section">
            <form action="download_certificate.php" method="POST" target="_blank">
                <input type="hidden" name="team_name" value="<?= htmlspecialchars($team['TeamName']) ?>">
                <p>您的參賽證書已準備好，點擊下方按鈕即可下載：</p>
                <button type="submit" class="btn">🎓 下載參賽證書</button>
            </form>
        </div>

        <script>
            function toggleSection(id) {
                document.querySelectorAll('.action-section').forEach(el => el.style.display = 'none');
                const section = document.getElementById('section-' + id);
                if (section) section.style.display = 'block';
            }
        </script>
    </div>
</body>

</html>
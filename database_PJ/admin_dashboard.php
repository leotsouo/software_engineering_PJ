<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

session_start(); // 啟用 session

// 確保系統管理員已登入
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// 從 session 取得管理員資訊
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];

// 用來存放「公告管理」或「輪播圖管理」的操作後訊息
$message = '';
// ------------------ 評分標準管理：新增與刪除 ------------------
try {
    // 撈出全部標準供刪除用
    $stmt_criteria_all = $pdo->query("SELECT * FROM criteria ORDER BY id ASC");
    $criteria_all = $stmt_criteria_all->fetchAll(PDO::FETCH_ASSOC);

    // 新增評分標準
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criteria_form'])) {
        $new_criteria = trim($_POST['criteria_name']);
        if (!empty($new_criteria)) {
            $stmt = $pdo->prepare("INSERT INTO criteria (name) VALUES (:name)");
            $stmt->bindParam(':name', $new_criteria);
            $stmt->execute();
            $message = "✅ 評分標準已成功新增！";
        } else {
            $message = "⚠️ 請輸入評分標準名稱。";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // 刪除評分標準
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_criteria_form'])) {
        $delete_id = $_POST['criteria_id'] ?? '';
        if (!empty($delete_id)) {
            $stmt = $pdo->prepare("DELETE FROM criteria WHERE id = :id");
            $stmt->bindParam(':id', $delete_id);
            $stmt->execute();
            $message = "🗑️ 評分標準已成功刪除！";
        } else {
            $message = "⚠️ 請選擇要刪除的標準。";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} catch (PDOException $e) {
    die("評分標準管理失敗: " . $e->getMessage());
}

// ------------------ 公告管理：新增、編輯、刪除 ------------------
try {
    // 1. 取得所有公告
    $sql_announcements = "SELECT AnnouncementID, Title, Content, PublishDate
                          FROM announcement
                          ORDER BY PublishDate DESC";
    $stmt_announcements = $pdo->prepare($sql_announcements);
    $stmt_announcements->execute();
    $announcements = $stmt_announcements->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("公告資料獲取失敗: " . $e->getMessage());
}

// ------------------ 輪播圖管理：新增、編輯、刪除 ------------------
try {
    // 1. 取得所有輪播圖
    $sql_carousels = "SELECT CarouselID, ImagePath, Caption, PublishDate
                      FROM carousel
                      ORDER BY PublishDate DESC";
    $stmt_carousels = $pdo->prepare($sql_carousels);
    $stmt_carousels->execute();
    $carousels = $stmt_carousels->fetchAll(PDO::FETCH_ASSOC);

    // 2. 新增或更新輪播圖
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['carousel_form'])) {
        $caption = $_POST['caption'];

        // 檔案上傳（若有選擇圖片）
        $uploadedImagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // 建立 uploads/carousel 目錄（若不存在）
            $uploadDir = __DIR__ . '/uploads/carousel/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            // 取得上傳資訊
            $tmpName = $_FILES['image']['tmp_name'];
            $fileName = time() . '_' . $_FILES['image']['name'];  // 簡易做法：用時間戳＋原檔名
            $destination = $uploadDir . $fileName;

            // 移動檔案
            if (move_uploaded_file($tmpName, $destination)) {
                // 設定要存入資料庫的路徑（相對或絕對皆可）
                $uploadedImagePath = 'uploads/carousel/' . $fileName;
            }
        }

        if (isset($_POST['carousel_id']) && !empty($_POST['carousel_id'])) {
            // 更新輪播圖
            $carousel_id = $_POST['carousel_id'];

            // 若沒有重新上傳新圖片，就維持舊的 ImagePath
            if (empty($uploadedImagePath)) {
                $sql_update_carousel = "UPDATE carousel
                                        SET Caption = :caption,
                                            PublishDate = NOW()
                                        WHERE CarouselID = :id";
                $stmt_update_carousel = $pdo->prepare($sql_update_carousel);
                $stmt_update_carousel->bindParam(':caption', $caption, PDO::PARAM_STR);
                $stmt_update_carousel->bindParam(':id', $carousel_id, PDO::PARAM_INT);
            } else {
                $sql_update_carousel = "UPDATE carousel
                                        SET ImagePath = :imagePath,
                                            Caption = :caption,
                                            PublishDate = NOW()
                                        WHERE CarouselID = :id";
                $stmt_update_carousel = $pdo->prepare($sql_update_carousel);
                $stmt_update_carousel->bindParam(':imagePath', $uploadedImagePath, PDO::PARAM_STR);
                $stmt_update_carousel->bindParam(':caption', $caption, PDO::PARAM_STR);
                $stmt_update_carousel->bindParam(':id', $carousel_id, PDO::PARAM_INT);
            }

            $stmt_update_carousel->execute();
            $message = "輪播圖已成功更新！";

        } else {
            // 新增輪播圖（必須要有圖片路徑才新增，否則可以先檢查是否有上傳成功）
            if (!empty($uploadedImagePath)) {
                $sql_insert_carousel = "INSERT INTO carousel (ImagePath, Caption, PublishDate)
                                        VALUES (:imagePath, :caption, NOW())";
                $stmt_insert_carousel = $pdo->prepare($sql_insert_carousel);
                $stmt_insert_carousel->bindParam(':imagePath', $uploadedImagePath, PDO::PARAM_STR);
                $stmt_insert_carousel->bindParam(':caption', $caption, PDO::PARAM_STR);
                $stmt_insert_carousel->execute();
                $message = "輪播圖已成功新增！";
            } else {
                $message = "請選擇要上傳的圖片。";
            }
        }

        // 自動刷新頁面
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // 3. 刪除輪播圖
    if (isset($_GET['delete_carousel_id']) && !empty($_GET['delete_carousel_id'])) {
        $delete_carousel_id = $_GET['delete_carousel_id'];

        // 刪除前先找出舊圖片路徑
        $sql_get_path = "SELECT ImagePath FROM carousel WHERE CarouselID = :id";
        $stmt_get_path = $pdo->prepare($sql_get_path);
        $stmt_get_path->bindParam(':id', $delete_carousel_id, PDO::PARAM_INT);
        $stmt_get_path->execute();
        $oldData = $stmt_get_path->fetch(PDO::FETCH_ASSOC);

        // 刪除資料表紀錄
        $sql_delete_carousel = "DELETE FROM carousel WHERE CarouselID = :id";
        $stmt_delete_carousel = $pdo->prepare($sql_delete_carousel);
        $stmt_delete_carousel->bindParam(':id', $delete_carousel_id, PDO::PARAM_INT);
        $stmt_delete_carousel->execute();

        // 若需要同時刪除實體檔案，可在此判斷
        if ($oldData && isset($oldData['ImagePath']) && file_exists(__DIR__ . '/' . $oldData['ImagePath'])) {
            unlink(__DIR__ . '/' . $oldData['ImagePath']);
        }

        $message = "輪播圖已成功刪除！";

        // 自動刷新頁面
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} catch (PDOException $e) {
    die("輪播圖資料獲取失敗: " . $e->getMessage());
}

//------------------ 帳號管理：新增、編輯、刪除 ------------------
try {
    // 教師
    $sql_teachers = "SELECT TeacherID, Name FROM teacher ORDER BY TeacherID ASC";
    $stmt_teachers = $pdo->prepare($sql_teachers);
    $stmt_teachers->execute();
    $teachers = $stmt_teachers->fetchAll(PDO::FETCH_ASSOC);

    // 學生 (隊員)
    $sql_students = "SELECT StudentID, Name FROM teammember ORDER BY StudentID ASC";
    $stmt_students = $pdo->prepare($sql_students);
    $stmt_students->execute();
    $students = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

    // 評審
    $sql_judges = "SELECT JudgeID, Name FROM judge ORDER BY JudgeID ASC";
    $stmt_judges = $pdo->prepare($sql_judges);
    $stmt_judges->execute();
    $judges = $stmt_judges->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("讀取可登入帳號失敗: " . $e->getMessage());
}

// ------------------ 附件上傳管理 ------------------
try {
    // 1. 新增附件
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attachment_form'])) {
        $announcement_id = $_POST['announcement_id']; // 對應的公告ID
        $attachment_name = $_POST['attachment_name']; // 附件名稱
        $uploadedFilePath = '';

        // 處理檔案上傳
        if (isset($_FILES['attachment_file']) && $_FILES['attachment_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/attachments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // 如果目錄不存在則建立
            }
            $tmpName = $_FILES['attachment_file']['tmp_name'];
            $fileName = time() . '_' . $_FILES['attachment_file']['name']; // 確保檔名唯一性
            $destination = $uploadDir . $fileName;

            // 移動上傳檔案
            if (move_uploaded_file($tmpName, $destination)) {
                $uploadedFilePath = 'uploads/attachments/' . $fileName; // 儲存路徑到資料庫
            } else {
                $message = "附件上傳失敗！";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }

        if (!empty($uploadedFilePath)) {
            $sql_insert_attachment = "INSERT INTO attachment (AnnouncementID, AttachmentName, AttachmentLink)
                                      VALUES (:announcement_id, :attachment_name, :attachment_link)";
            $stmt_insert_attachment = $pdo->prepare($sql_insert_attachment);
            $stmt_insert_attachment->bindParam(':announcement_id', $announcement_id, PDO::PARAM_INT);
            $stmt_insert_attachment->bindParam(':attachment_name', $attachment_name, PDO::PARAM_STR);
            $stmt_insert_attachment->bindParam(':attachment_link', $uploadedFilePath, PDO::PARAM_STR);
            $stmt_insert_attachment->execute();
            $message = "附件已成功上傳！";
        }

        // 自動刷新頁面
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} catch (PDOException $e) {
    die("附件資料操作失敗: " . $e->getMessage());
}
//------------------ 隊伍管理：讀取所有隊伍 ------------------
try {
    $sql_teams = "
      SELECT TeamID,
             competition_category,
             TeamName,
             RegistrationDate,
             Rank,
             TeacherID
      FROM team
      ORDER BY RegistrationDate DESC
    ";
    $stmt_teams = $pdo->prepare($sql_teams);
    $stmt_teams->execute();
    $teams = $stmt_teams->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("讀取隊伍資料失敗: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>後台管理 - 公告 & 輪播圖</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
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
        header h1, header p {
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }
        h2 {
            margin-top: 0;
            color: #0057b8;
            border-left: 6px solid #0073e6;
            padding-left: 10px;
        }
        .message {
            color: green;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            vertical-align: middle;
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
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin: 5px 0;
        }
        .btn:hover {
            background-color: #005bb5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-toggle {
            display: none;
            margin-top: 10px;
            padding: 15px;
            border-top: 2px solid #ddd;
        }
        .img-preview {
            max-width: 200px;
            max-height: 120px;
            object-fit: cover;
        }
    </style>
    <script>
        // 切換 公告表單
        function toggleAnnouncementForm(id = '', title = '', content = '') {
            const form = document.getElementById('announcement-form');
            const inputId = document.getElementById('announcement-id');
            const inputTitle = document.getElementById('title');
            const inputContent = document.getElementById('content');

            // 如果傳入 id，表示編輯；若 id 為空，表示新增
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
            inputId.value = id || '';
            inputTitle.value = title || '';
            inputContent.value = content || '';
        }
        // 切換 附件表單
        function toggleAttachmentForm(announcementId = '') {
            const form = document.getElementById('attachment-form');
            const inputAnnouncementId = document.getElementById('announcement-id-attachment');

            form.style.display = form.style.display === 'block' ? 'none' : 'block';
            inputAnnouncementId.value = announcementId;
        }


        // 切換 輪播圖表單
        function toggleCarouselForm(id = '', caption = '', imagePath = '') {
            const form = document.getElementById('carousel-form');
            const inputId = document.getElementById('carousel-id');
            const inputCaption = document.getElementById('caption');
            const imagePreview = document.getElementById('image-preview');

            form.style.display = form.style.display === 'block' ? 'none' : 'block';
            inputId.value = id || '';
            inputCaption.value = caption || '';

            // 如果是編輯，顯示舊圖
            if (imagePath) {
                imagePreview.src = imagePath;
                imagePreview.style.display = 'block';
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
            }
        }
    </script>
</head>
<body>

<header>
    <h1>後台管理頁面</h1>
    <p>歡迎，<?= htmlspecialchars($admin_name) ?>！</p>
</header>

<div class="container">

    <!-- 若有任何操作訊息，顯示在這裡 -->
    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- -------------------- 公告管理 -------------------- -->
    <div class="section">
        <h2>公告管理</h2>
        <table>
            <thead>
                <tr>
                    <th>公告ID</th>
                    <th>標題</th>
                    <th>內容</th>
                    <th>發布日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="5">目前無公告。</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <tr>
                            <td><?= htmlspecialchars($announcement['AnnouncementID']) ?></td>
                            <td><?= htmlspecialchars($announcement['Title']) ?></td>
                            <td><?= nl2br(htmlspecialchars($announcement['Content'])) ?></td>
                            <td><?= htmlspecialchars($announcement['PublishDate']) ?></td>
                            <td>
                                <button class="btn"
                                    type="button"
                                    onclick="location.href='edit_announcement.php?id=<?= urlencode($announcement['AnnouncementID']) ?>'">
                                    編輯
                                </button>
                                <a class="btn"
                                    href="delete_announcement.php?id=<?= urlencode($announcement['AnnouncementID']) ?>"
                                    onclick="return confirm('確定要刪除此公告？');">
                                    刪除
                                </a>
                                <!--新增上傳附件功能!---點了上傳附件按鈕之後取得對應的AnnouncementID,跳出上傳附件的區塊讓用戶輸入的資料存到table:attachment-->
                                <button class="btn"
                                    onclick="toggleAttachmentForm('<?= htmlspecialchars($announcement['AnnouncementID']) ?>')">
                                    上傳附件
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <a class="btn" href="add_announcement.php">新增公告</a>

        <!-- 新增/編輯公告表單 -->
        <div id="announcement-form" class="form-toggle">
            <h3>新增/編輯 公告</h3>
            <form method="POST" action="">
                <!-- 用於判斷是公告表單還是輪播圖表單 -->
                <input type="hidden" name="announcement_form" value="1">
                <input type="hidden" id="announcement-id" name="announcement_id">

                <div class="form-group">
                    <label for="title">標題：</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="content">內容：</label>
                    <textarea id="content" name="content" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn">提交</button>
            </form>
        </div>
    </div>
    <!-- 附件上傳表單 -->
    <div id="attachment-form" class="form-toggle" style="display: none;">
        <h3>上傳附件</h3>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="attachment_form" value="1">
            <input type="hidden" id="announcement-id-attachment" name="announcement_id">

            <div class="form-group">
                <label for="attachment-name">附件名稱：</label>
                <input type="text" id="attachment-name" name="attachment_name" required>
            </div>

            <div class="form-group">
                <label for="attachment-file">選擇檔案：</label>
                <input type="file" id="attachment-file" name="attachment_file" required>
            </div>

            <button type="submit" class="btn">提交</button>
        </form>
    </div>

    <!-- -------------------- 評分標準管理 -------------------- -->
    <div class="section">
        <h2>評分標準管理</h2>

        <!-- 新增 -->
        <form method="POST" action="" style="margin-bottom: 20px;">
            <input type="hidden" name="criteria_form" value="1">
            <div class="form-group">
                <label for="criteria-name">新增評審評分標準(例如:創意、可行性、簡報表現...等等):</label>
                <input type="text" id="criteria-name" name="criteria_name" required>
            </div>
            <button type="submit" class="btn">新增</button>
        </form>

        <!-- 刪除 -->
        <form method="POST" action="">
            <input type="hidden" name="delete_criteria_form" value="1">
            <div class="form-group">
                <label for="criteria-id">刪除評分標準：</label>
                <select name="criteria_id" id="criteria-id" required>
                    <option value="">請選擇</option>
                    <?php foreach ($criteria_all as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn" style="background-color: #d9534f;">刪除</button>
        </form>
    </div>



    <!-- -------------------- 輪播圖管理 -------------------- -->
    <div class="section">
        <h2>輪播圖管理</h2>
        <table>
            <thead>
                <tr>
                    <th>輪播圖ID</th>
                    <th>圖片</th>
                    <th>說明文字</th>
                    <th>發布日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($carousels)): ?>
                    <tr><td colspan="5">目前無輪播圖。</td></tr>
                <?php else: ?>
                    <?php foreach ($carousels as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['CarouselID']) ?></td>
                            <td>
                                <?php if (!empty($c['ImagePath'])): ?>
                                    <img src="<?= htmlspecialchars($c['ImagePath']) ?>" alt="carousel" style="max-width:200px; max-height:100px;">
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['Caption']) ?></td>
                            <td><?= htmlspecialchars($c['PublishDate']) ?></td>
                            <td>
                                <button class="btn"
                                        onclick="toggleCarouselForm(
                                            '<?= htmlspecialchars($c['CarouselID']) ?>',
                                            '<?= htmlspecialchars($c['Caption']) ?>',
                                            '<?= htmlspecialchars($c['ImagePath']) ?>'
                                        )">
                                    編輯
                                </button>
                                <a class="btn"
                                         href="?delete_carousel_id=<?= htmlspecialchars($c['CarouselID']) ?>"
                                         onclick="return confirm('確定要刪除這張輪播圖？');">
                                    刪除
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <button class="btn" onclick="toggleCarouselForm()">新增輪播圖</button>

        <!-- 新增/編輯輪播圖表單 -->
        <div id="carousel-form" class="form-toggle">
            <h3>新增/編輯 輪播圖</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- 用於判斷是輪播圖表單 -->
                <input type="hidden" name="carousel_form" value="1">
                <input type="hidden" id="carousel-id" name="carousel_id">

                <div class="form-group">
                    <label for="caption">說明文字：</label>
                    <input type="text" id="caption" name="caption" required>
                </div>

                <div class="form-group">
                    <label for="image">圖片檔案 (如需更換或新增)：</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <!-- 顯示舊圖片用的預覽 -->
                <img id="image-preview" class="img-preview" style="display:none; margin-bottom:10px;" />

                <button type="submit" class="btn">提交</button>
            </form>
        </div>
    </div>
     <!-- -------------------- 帳號管理 -------------------- -->
    <!-- -------------------- 可登入帳號列表 -------------------- -->
    <div class="section">
        <h2>可登入帳號列表</h2>

        <!-- 教師帳號 -->
        <h3>教師</h3>
        <table>
            <thead>
                <tr><th>TeacherID</th><th>Name</th><th>操作</th>
            </thead>
            <tbody>
                <?php if (empty($teachers)): ?>
                    <tr><td colspan="2">無教師帳號</td></tr>
                <?php else: foreach ($teachers as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['TeacherID']) ?></td>
                        <td><?= htmlspecialchars($t['Name']) ?></td>
                        <td>
                            <a class="btn"
                                href="edit_teacher.php?id=<?= urlencode($t['TeacherID']) ?>">
                                編輯
                            </a>
                            <a class="btn"
                                href="delete_teacher.php?id=<?= urlencode($t['TeacherID']) ?>"
                                onclick="return confirm('確定要刪除此教師？');">
                                刪除
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- 學生帳號 -->
        <h3>學生</h3>
        <table>
            <thead>
                <tr><th>StudentID</th><th>Name</th><th>操作</th>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="2">無學生帳號</td></tr>
                <?php else: foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['StudentID']) ?></td>
                        <td><?= htmlspecialchars($s['Name']) ?></td>
                        <td>
                            <a class="btn"
                                href="edit_student.php?id=<?= urlencode($t['StudentID']) ?>">
                                編輯
                            </a>
                            <a class="btn"
                                href="delete_student.php?id=<?= urlencode($t['StudentID']) ?>"
                                onclick="return confirm('確定要刪除此學生？');">
                                刪除
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <!-- 評審帳號 -->
        <h3>評審</h3>
        <table>
            <thead>
                <tr><th>JudgeID</th><th>Name</th><th>操作</th>
            </thead>
            <tbody>
                <?php if (empty($judges)): ?>
                    <tr><td colspan="2">無評審帳號</td></tr>
                <?php else: foreach ($judges as $j): ?>
                    <tr>
                        <td><?= htmlspecialchars($j['JudgeID']) ?></td>
                        <td><?= htmlspecialchars($j['Name']) ?></td>
                        <td>
                            <a class="btn"
                                href="edit_judge.php?id=<?= urlencode($t['JudgeID']) ?>">
                                編輯
                            </a>
                            <a class="btn"
                                href="delete_judge.php?id=<?= urlencode($t['JudgeID']) ?>"
                                onclick="return confirm('確定要刪除此評審？');">
                                刪除
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
                <!-- 可登入帳號列表 區塊最底 -->
        <div style="text-align: left; margin-top: 10px;">
        <button class="btn" onclick="location.href='add_user.php'">新增帳號</button>
        </div>
    </div>
        <!-- -------------------- 隊伍管理 -------------------- -->
    <div class="section">
        <h2>隊伍管理</h2>
        <table>
            <thead>
                <tr>
                    <th>TeamID</th>
                    <th>組別</th>
                    <th>隊伍名稱</th>
                    <th>報名日期</th>
                    <th>排名</th>
                    <th>TeacherID</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($teams)): ?>
                    <tr><td colspan="7">目前無隊伍資料。</td></tr>
                <?php else: ?>
                    <?php foreach ($teams as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['TeamID']) ?></td>
                            <td><?= htmlspecialchars($t['competition_category']) ?></td>
                            <td><?= htmlspecialchars($t['TeamName']) ?></td>
                            <td><?= htmlspecialchars($t['RegistrationDate']) ?></td>
                            <td><?= htmlspecialchars($t['Rank']) ?></td>
                            <td><?= htmlspecialchars($t['TeacherID']) ?></td>
                            <td>
                                <a class="btn"
                                   href="edit_team.php?id=<?= urlencode($t['TeamID']) ?>">
                                   編輯
                                </a>
                                <a class="btn"
                                   href="delete_team.php?id=<?= urlencode($t['TeamID']) ?>"
                                   onclick="return confirm('確定要刪除這個隊伍？');">
                                   刪除
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 新增隊伍按鈕 -->
        <div style="text-align: left; margin-top: 10px;">
            <button class="btn" onclick="location.href='add_team.php'">新增隊伍</button>
        </div>
    </div>

    <!-- 回首頁按鈕 (可自行調整連結) -->
    <a href="index.php" class="btn">回首頁</a>

</div>
</body>
</html>

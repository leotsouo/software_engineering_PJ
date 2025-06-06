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

// ------------------ 證書上傳功能 ------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_certificate_pdf'])) {
    $uploadDir = __DIR__ . '/uploads/certificates/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['certificate_pdf']) && $_FILES['certificate_pdf']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['certificate_pdf']['tmp_name'];
        $fileName = 'certificate_template.pdf'; // 固定名稱
        $destination = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $destination)) {
            $message = "證書 PDF 已成功上傳！";
        } else {
            $message = "證書上傳失敗！";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
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

    // 2. 新增或更新公告
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['announcement_form'])) {
        $title = $_POST['title'];
        $content = $_POST['content'];

        if (isset($_POST['announcement_id']) && !empty($_POST['announcement_id'])) {
            // 更新公告
            $announcement_id = $_POST['announcement_id'];
            $sql_update = "UPDATE announcement
                           SET Title = :title,
                               Content = :content,
                               PublishDate = NOW()
                           WHERE AnnouncementID = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt_update->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt_update->bindParam(':id', $announcement_id, PDO::PARAM_INT);
            $stmt_update->execute();
            $message = "公告已成功更新！";
        } else {
            // 新增公告
            $sql_insert = "INSERT INTO announcement (Title, Content, PublishDate)
                           VALUES (:title, :content, NOW())";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt_insert->bindParam(':content', $content, PDO::PARAM_STR);
            $stmt_insert->execute();
            $message = "公告已成功新增！";
        }

        // 自動刷新頁面
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // 3. 刪除公告
    if (isset($_GET['delete_announcement_id']) && !empty($_GET['delete_announcement_id'])) {
        $delete_id = $_GET['delete_announcement_id'];
        $sql_delete = "DELETE FROM announcement WHERE AnnouncementID = :id";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->bindParam(':id', $delete_id, PDO::PARAM_INT);
        $stmt_delete->execute();
        $message = "公告已成功刪除！";

        // 自動刷新頁面
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
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

        header h1,
        header p {
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

        table th,
        table td {
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
                        <tr>
                            <td colspan="5">目前無公告。</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <tr>
                                <td><?= htmlspecialchars($announcement['AnnouncementID']) ?></td>
                                <td><?= htmlspecialchars($announcement['Title']) ?></td>
                                <td><?= nl2br(htmlspecialchars($announcement['Content'])) ?></td>
                                <td><?= htmlspecialchars($announcement['PublishDate']) ?></td>
                                <td>
                                    <button class="btn"
                                        onclick="toggleAnnouncementForm(
                                            '<?= htmlspecialchars($announcement['AnnouncementID']) ?>',
                                            '<?= htmlspecialchars($announcement['Title']) ?>',
                                            '<?= htmlspecialchars($announcement['Content']) ?>'
                                        )">
                                        編輯
                                    </button>
                                    <a class="btn" href="?delete_announcement_id=<?= htmlspecialchars($announcement['AnnouncementID']) ?>">刪除</a>
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

            <button class="btn" onclick="toggleAnnouncementForm()">新增公告</button>

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
                        <tr>
                            <td colspan="5">目前無輪播圖。</td>
                        </tr>
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
                                    <a class="btn" href="?delete_carousel_id=<?= htmlspecialchars($c['CarouselID']) ?>">刪除</a>
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

        <!-- -------------------- 證書上傳區塊 -------------------- -->
        <div class="section">
            <h2>上傳參賽證書 PDF</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="upload_certificate_pdf" value="1">
                <div class="form-group">
                    <label for="certificate_pdf">選擇證書 PDF 檔案：</label>
                    <input type="file" name="certificate_pdf" id="certificate_pdf" accept="application/pdf" required>
                </div>
                <button type="submit" class="btn">上傳</button>
            </form>

            <!-- 顯示目前上傳的證書 -->
            <?php
            $certificatePath = 'uploads/certificates/certificate_template.pdf';
            if (file_exists(__DIR__ . '/' . $certificatePath)) {
                echo '
    <div style="
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 25px;
        padding: 15px 20px;
        border: 1px solid #0073e6;
        border-left: 6px solid #0073e6;
        border-radius: 6px;
        background-color: #f0f8ff;
        max-width: 600px;
        font-size: 16px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    ">
        <span style="font-size: 20px;">📎</span>
        <div>
            <div style="font-weight: bold; color: #0057b8; margin-bottom: 4px;">目前已上傳證書</div>
            <a href="' . $certificatePath . '" target="_blank" style="color: #0073e6; text-decoration: underline; font-weight: 500;">
                certificate_template.pdf
            </a>
        </div>
    </div>';
            } else {
                echo '
    <div style="
        margin-top: 25px;
        padding: 15px 20px;
        border: 1px solid #d9534f;
        border-left: 6px solid #d9534f;
        border-radius: 6px;
        background-color: #fbeaea;
        max-width: 600px;
        font-size: 16px;
    ">
        <span style="font-weight: bold; color: #c9302c;">❌ 尚未上傳任何證書 PDF</span>
    </div>';
            }
            ?>


            <!-- 回首頁按鈕 (可自行調整連結) -->
            <a href="index.php" class="btn">回首頁</a>

        </div>
</body>

</html>
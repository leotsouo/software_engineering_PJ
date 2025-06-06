<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

try {
    // 獲取輪播圖資料
    $sql_carousel = "SELECT ImagePath, Caption FROM carousel ORDER BY PublishDate DESC";
    $stmt_carousel = $pdo->prepare($sql_carousel);
    $stmt_carousel->execute();
    $carousels = $stmt_carousel->fetchAll(PDO::FETCH_ASSOC);

    // 獲取公告資料
    $sql_announcements = "SELECT AnnouncementID, Title, Content, PublishDate FROM announcement ORDER BY PublishDate DESC";
    $stmt_announcements = $pdo->prepare($sql_announcements);
    $stmt_announcements->execute();
    $announcements = $stmt_announcements->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("資料獲取失敗: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>創意競賽管理系統</title>
    <style>
        /* 樣式與之前一致 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #eef3fa;
            color: #333;
        }

        header {
            background-color: #0057b8;
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        header .logo {
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        header .buttons {
            display: flex;
            gap: 20px;
        }

        header .buttons a {
            text-decoration: none;
            color: white;
            background-color: #0073e6;
            padding: 12px 25px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out;
        }

        header .buttons a:hover {
            background-color: #004a99;
            transform: scale(1.05);
        }

        nav {
            background-color: #0073e6;
            color: white;
            display: flex;
            justify-content: center;
            padding: 15px 0;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin: 0 30px;
            font-size: 18px;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        nav a:hover {
            color: #cce7ff;
        }

        .carousel {
            position: relative;
            width: 100%;
            height: 60vh;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 0;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .carousel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 100%;
            opacity: 0;
            transition: all 0.7s ease;
        }

        .carousel img.active {
            left: 0;
            opacity: 1;
        }

        .carousel img.previous {
            left: -100%;
        }

        .announcement {
            max-width: 1200px;
            margin: 40px auto;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .announcement h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #0057b8;
            border-left: 6px solid #0073e6;
            padding-left: 15px;
        }

        .announcement ul {
            list-style: none;
            padding-left: 15px;
        }

        .announcement ul li {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .announcement ul li span {
            font-size: 14px;
            color: #666;
        }

        .announcement ul li a {
            text-decoration: none;
            color: #0057b8;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .announcement ul li a:hover {
            color: #0073e6;
        }

        footer {
            text-align: center;
            padding: 20px;
            background-color: #004a99;
            color: white;
            font-size: 16px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const images = document.querySelectorAll('.carousel img');
            let currentIndex = 0;

            function switchImage() {
                images[currentIndex].classList.remove('active');
                currentIndex = (currentIndex + 1) % images.length;
                images[currentIndex].classList.add('active');
            }

            setInterval(switchImage, 3000);
        });
    </script>
</head>

<body>
    <header>
        <div class="logo"> 高大激發學生創意競賽</div>


        <div class="buttons">
            <a href="signup_form.php">報名</a>
            <a href="login_select.html">登入</a>
            <a href="cancel_registration.php">取消報名</a>
        </div>
    </header>

    <nav>
        <a href="about.html">關於競賽</a>
        <a href="rule.html">競賽規則</a>
        <a href="rank.php">成績排名</a>
    </nav>

    <div class="carousel">
        <?php foreach ($carousels as $carousel): ?>
            <img src="<?= htmlspecialchars($carousel['ImagePath']) ?>" alt="<?= htmlspecialchars($carousel['Caption']) ?>" class="<?= $carousel === reset($carousels) ? 'active' : '' ?>">
        <?php endforeach; ?>
    </div>

    <div class="announcement">
        <h2>最新公告</h2>
        <ul>
            <?php if (empty($announcements)): ?>
                <li>目前無公告。</li>
            <?php else: ?>
                <?php foreach ($announcements as $announcement): ?>
                    <li>
                        <a href="Announcement.php?id=<?= htmlspecialchars($announcement['AnnouncementID']) ?>">
                            <?= htmlspecialchars($announcement['Title']) ?>
                        </a>
                        <span> (發佈日期: <?= htmlspecialchars($announcement['PublishDate']) ?>)</span>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <footer>
        高大激發學生創意競賽第九組 © 2025
    </footer>
</body>

</html>
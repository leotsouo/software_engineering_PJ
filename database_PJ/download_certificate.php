<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_name = trim($_POST['team_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $sql = "SELECT t.TeamID 
            FROM team t
            JOIN teammember m ON t.TeamID = m.TeamID 
            WHERE t.TeamName = ? AND m.Email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$team_name, $email]);
    $record = $stmt->fetch();

    if ($record) {
        // 修正路徑與檔名
        $file_path = __DIR__ . '/uploads/certificates/certificate_template.pdf';
        if (file_exists($file_path)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="certificate.pdf"');
            readfile($file_path);
            exit;
        } else {
            $error = "❌ 證書尚未上傳";
        }
    } else {
        $error = "❌ 查無此隊伍與 Email 的對應資料";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>下載參賽證書</title>
    <style>
        body {
            font-family: Arial;
            padding: 40px;
            background-color: #eef3fa;
        }

        .form-container {
            background: white;
            max-width: 400px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #aaa;
        }

        h2 {
            text-align: center;
            color: #0073e6;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .error {
            color: red;
            text-align: center;
        }

        .btn-back {
            display: block;
            margin: 15px auto 0;
            text-align: center;
            text-decoration: none;
            color: #0073e6;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>下載參賽證書</h2>
        <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="post">
            <input type="text" name="team_name" placeholder="輸入隊伍名稱" required>
            <input type="email" name="email" placeholder="輸入參賽者 Email" required>
            <button type="submit">下載證書</button>
        </form>
        <a href="index.php" class="btn-back">回首頁</a>
    </div>
</body>

</html>
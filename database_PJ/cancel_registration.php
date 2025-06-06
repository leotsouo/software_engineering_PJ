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
    $team = $stmt->fetch();

    if ($team) {
        // 刪除該隊伍所有成員
        $sql1 = "DELETE FROM teammember WHERE TeamID = ?";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([$team['TeamID']]);

        // 刪除隊伍資料
        $sql2 = "DELETE FROM team WHERE TeamID = ?";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([$team['TeamID']]);

        $success = "✅ 已成功取消報名並刪除隊伍資料。";
    } else {
        $error = "❌ 查無此隊伍與 Email 的對應資料。";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>取消報名</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 50px;
        }

        .form-container {
            background: white;
            max-width: 420px;
            margin: auto;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #4CAF50;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
        }

        .message {
            text-align: center;
            font-weight: bold;
            color: red;
            margin-bottom: 10px;
        }

        .success {
            color: green;
        }

        .back-btn {
            display: block;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            color: #0073e6;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>取消報名</h2>

        <?php if (!empty($error)) echo "<div class='message'>$error</div>"; ?>
        <?php if (!empty($success)) echo "<div class='message success'>$success</div>"; ?>

        <form method="post">
            <input type="text" name="team_name" placeholder="輸入隊伍名稱" required>
            <input type="email" name="email" placeholder="輸入報名者 Email" required>
            <button type="submit">確認取消報名</button>
        </form>

        <a href="index.php" class="back-btn">← 返回首頁</a>
    </div>
</body>

</html>
<?php
require_once 'db_connection.php'; // 引入資料庫連接文件

session_start(); // 啟用 session

// 登入處理邏輯
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username']; // 假設帳號是 JudgeID
    $password = $_POST['password']; // 假設密碼是 Email

    try {
        // 查詢資料庫，檢查帳號和密碼是否正確
        $sql = "SELECT JudgeID, Name FROM judge WHERE JudgeID = :username AND Phone = :password";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        $judge = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($judge) {
            // 登入成功，將評審資訊存入 session
            $_SESSION['judge_id'] = $judge['JudgeID'];
            $_SESSION['judge_name'] = $judge['Name'];

            // 跳轉到評審主頁面
            header('Location: judge_dashboard.php');
            exit;
        } else {
            // 登入失敗，提示錯誤訊息
            $error_message = "帳號或密碼錯誤！";
        }
    } catch (PDOException $e) {
        die("資料庫錯誤: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>評審登入</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 400px;
        }
        .login-container h2 {
            font-size: 24px;
            color: #0057b8;
            margin-bottom: 20px;
        }
        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .login-container button:hover {
            background-color: #004a99;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>評審登入</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <form action="login_judge.php" method="POST">
            <input type="text" name="username" placeholder="輸入帳號 (JudgeID)" required>
            <input type="password" name="password" placeholder="輸入密碼 (Phone)" required>
            <button type="submit">登入</button>
        </form>
    </div>
</body>
</html>

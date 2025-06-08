<?php
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>登出中...</title>
    <meta http-equiv="refresh" content="2;url=index.php">
    <style>
        body {
            font-family: Arial;
            text-align: center;
            margin-top: 100px;
        }
    </style>
</head>
<body>
    <h2> 您已成功登出</h2>
    <p>即將導回首頁...</p>
</body>
</html>

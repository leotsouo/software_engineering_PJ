<?php
$host = 'localhost';
$dbname = 'database_pj'; // 替換為您的資料庫名稱
$username = 'root'; // 替換為您的資料庫用戶名
$password = ''; // 替換為您的資料庫密碼

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 資料庫連接成功
} catch (PDOException $e) {
    die("資料庫連接失敗: " . $e->getMessage());
}
?>

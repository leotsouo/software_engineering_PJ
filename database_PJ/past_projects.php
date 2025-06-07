<?php
// view_past_projects.php
require_once 'db_connection.php';
session_start();

// （如果需要登入才能看，就打開下面這段）
// if (!isset($_SESSION['teacher_id']) && !isset($_SESSION['admin_id'])) {
//     header('Location: login_select.html');
//     exit;
//}

try {
    // 取得歷屆作品
    $sql = "SELECT PastTeamID, ProjectName, PostYear 
            FROM past_projects
            ORDER BY PostYear DESC, PastTeamID";
    $stmt = $pdo->query($sql);
    $past_projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("資料庫錯誤：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
  <meta charset="UTF-8">
  <title>歷屆作品一覽</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: Arial, sans-serif; background: #eef3fa; margin:0; padding:0; }
    .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 20px; 
                 border-radius: 8px; box-shadow: 0 6px 10px rgba(0,0,0,0.1); }
    h1 { color: #0057b8; text-align:center; margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; }
    th, td { border:1px solid #ddd; padding:10px; text-align:left; }
    th { background:#0057b8; color:#fff; }
    tr:nth-child(even) { background:#f9f9f9; }
    .btn-back {
      display:inline-block; margin:20px 0; color:#0073e6; text-decoration:none;
      padding:8px 15px; border:1px solid #0073e6; border-radius:4px;
    }
    .btn-back:hover { background:#0073e6; color:#fff; }
  </style>
</head>
<body>
  <div class="container">
    <h1>歷屆作品一覽</h1>

    <?php if (empty($past_projects)): ?>
      <p>目前沒有歷屆作品資料。</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>PastTeamID</th>
            <th>作品名稱</th>
            <th>發表年份</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($past_projects as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p['PastTeamID']) ?></td>
            <td><?= htmlspecialchars($p['ProjectName']) ?></td>
            <td><?= htmlspecialchars($p['PostYear']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <a href="index.php" class="btn-back">« 回到首頁</a>
  </div>
</body>
</html>
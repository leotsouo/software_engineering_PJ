<?php
session_start();
require_once 'db_connection.php';

// 檢查是否登入且為評審
if (!isset($_SESSION['judge_id'])) {
    header("Location: login_judge.php");
    exit();
}

$judge_name = $_SESSION['judge_name'];

// 取得隊伍資料
$sql = "SELECT team_id, team_name, group_name FROM teams";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>評審主頁面</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e9f0f7;
        }
        .container {
            margin-top: 30px;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="text-white bg-primary py-2 rounded">評審主頁面</h2>
            <p>歡迎，<?php echo htmlspecialchars($judge_name); ?>！</p>
        </div>

        <!-- 切換按鈕 -->
        <div class="btn-group mb-4">
            <button class="btn btn-primary" onclick="showSection('teams')">隊伍列表</button>
            <button class="btn btn-primary" onclick="showSection('addScore')">新增評分</button>
        </div>

        <!-- 隊伍列表 -->
        <div id="section-teams" class="section active card p-3">
            <h5 class="mb-3">隊伍列表</h5>
            <table class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>隊伍 ID</th>
                        <th>隊伍名稱</th>
                        <th>競賽組別</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['team_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['team_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['group_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- 新增評分 -->
        <div id="section-addScore" class="section card p-3">
            <h5 class="mb-3">新增評分</h5>
            <form method="POST" action="submit_score.php">
                <div class="mb-3">
                    <label for="team_id" class="form-label">選擇隊伍：</label>
                    <select name="team_id" id="team_id" class="form-select" required>
                        <option value="" disabled selected>請選擇隊伍</option>
                        <?php
                        // 再抓一次隊伍名稱給下拉選單
                        $result2 = $conn->query($sql);
                        while ($row = $result2->fetch_assoc()) {
                            echo "<option value=\"{$row['team_id']}\">{$row['team_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="score" class="form-label">評分：</label>
                    <input type="number" name="score" id="score" class="form-control" min="0" max="100" required>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">評論：</label>
                    <textarea name="comment" id="comment" rows="4" class="form-control" required></textarea>
                </div>
                <input type="submit" value="提交評分" class="btn btn-primary">
            </form>
        </div>
    </div>

    <!-- 區塊切換 JS -->
    <script>
        function showSection(section) {
            document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
            document.getElementById('section-' + section).classList.add('active');
        }
    </script>
</body>
</html>

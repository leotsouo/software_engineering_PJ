<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['judge_id'])) {
    header("Location: login_judge.php");
    exit();
}
// 分頁參數
$teams_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $teams_per_page;

// 總筆數 & 頁數計算
$total_teams = $pdo->query("SELECT COUNT(*) FROM team")->fetchColumn();
$total_pages = ceil($total_teams / $teams_per_page);

$judge_id = $_SESSION['judge_id'];
$judge_name = $_SESSION['judge_name'];

$team_stmt = $pdo->prepare("SELECT TeamID, TeamName, competition_category FROM team ORDER BY TeamID ASC LIMIT :limit OFFSET :offset");
$team_stmt->bindParam(':limit', $teams_per_page, PDO::PARAM_INT);
$team_stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$team_stmt->execute();
$teams = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

$criteria_stmt = $pdo->query("SELECT * FROM criteria ORDER BY id ASC");
$criteria_list = $criteria_stmt->fetchAll(PDO::FETCH_ASSOC);
// 撈 submission 對應到隊伍
$submission_stmt = $pdo->query("SELECT * FROM submission");
$submission_map = [];
while ($row = $submission_stmt->fetch(PDO::FETCH_ASSOC)) {
    $submission_map[$row['TeamID']] = $row;
}

// 撈自己評過的隊伍與分數，建立 map
$score_map_stmt = $pdo->prepare("SELECT TeamID, ScoreValue FROM score WHERE JudgeID = :judge_id");
$score_map_stmt->execute(['judge_id' => $judge_id]);
$score_map = [];
while ($row = $score_map_stmt->fetch(PDO::FETCH_ASSOC)) {
    $score_map[$row['TeamID']] = $row['ScoreValue'];
}

// 撈出自己打過的分數
$my_scores_stmt = $pdo->prepare("
    SELECT s.ScoreID, s.TeamID, t.TeamName, s.ScoreValue, s.Comment
    FROM score s
    JOIN team t ON s.TeamID = t.TeamID
    WHERE s.JudgeID = :judge_id
    ORDER BY s.Timestamp DESC
");
$my_scores_stmt->execute(['judge_id' => $judge_id]);
$my_scores = $my_scores_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>評審主頁面</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
            min-height: 100vh;
            margin: 0;
        }
        .dashboard-container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            width: 900px;
        }
        h2 {
            color: #0057b8;
            text-align: center;
            margin-bottom: 10px;
        }
        .welcome {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-group {
            text-align: center;
            margin-bottom: 20px;
        }
        .button-group button {
            margin: 0 10px;
            padding: 10px 20px;
            font-size: 15px;
            border: none;
            border-radius: 8px;
            background-color: #0073e6;
            color: white;
            cursor: pointer;
        }
        .button-group button:hover {
            background-color: #004a99;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #dceeff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        textarea {
            resize: vertical;
        }
        input[type="submit"], button {
            padding: 12px 20px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #004a99;
        }
        a.page-btn {
            text-decoration: none !important; /* 移除底線 */
            margin: 3px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        a.page-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

    </style>
</head>
<body>
    <div style="position: absolute; top: 20px; right: 30px;">
    <form action="logout.php" method="POST">
        <button type="submit" style="
            background-color: #d9534f;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        ">登出回首頁</button>
    </form>
</div>
<div class="dashboard-container">
    <h2>評審主頁面</h2>
    <div class="welcome">歡迎，<?= htmlspecialchars($judge_name) ?>！</div>

   <div class="button-group">
    <button onclick="showSection('teams')">隊伍列表</button>
    <button onclick="showSection('addScore')">新增評分</button>
    <button onclick="showSection('deleteScore')">刪除評分</button>
    </div>


    <!-- 隊伍列表 -->
<div id="section-teams" class="section active">
    <h4>隊伍列表</h4>
    <div class="form-group" style="margin-bottom: 15px;">
    <label for="teamSearch">🔍 搜尋隊伍（ID 或名稱）：</label>
    <input type="text" id="teamSearch" onkeyup="filterTeams()" placeholder="輸入隊伍 ID 或名稱" style="padding: 8px; width: 100%; border-radius: 6px;">
    </div>
    <table>
        <thead>
            <tr>
                <th>隊伍 ID</th>
                <th>隊伍名稱</th>
                <th>競賽組別</th>
                <th>評分狀態</th>
                <th>作品狀態</th> <!-- 🆕 -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $row): ?>
                <tr>
                    <td><?= $row['TeamID'] ?></td>
                    <td><?= htmlspecialchars($row['TeamName']) ?></td>
                    <td><?= htmlspecialchars($row['competition_category']) ?></td>
                    <td>
                        <?php if (isset($score_map[$row['TeamID']])): ?>
                            ✅ 已評：<?= $score_map[$row['TeamID']] ?> 分
                        <?php else: ?>
                             尚未評分
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($submission_map[$row['TeamID']])): ?>
                            <button onclick="showModal(<?= $row['TeamID'] ?>)" class="btn btn-primary btn-sm">
                            查看作品
                        </button>

                        <?php else: ?>
                            尚未上傳
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <!-- 分頁按鈕 -->
    <div style="text-align: center; margin-top: 20px;">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>"
           class="btn <?= $i === $page ? 'btn-dark' : 'btn-outline-primary' ?> page-btn">
            <?= $i ?>
        </a>
    <?php endfor; ?>
    </div>


</div>
    
    <!-- 新增評分 -->
    <div id="section-addScore" class="section">
        <form method="POST" action="submit_score.php">
            <div class="form-group">
                <label for="team_id">選擇要評分的隊伍：</label>
                <select name="team_id" id="team_id" required>
                    <option value="" disabled selected>請選擇隊伍</option>
                    <?php foreach ($teams as $row): ?>
                        <option value="<?= $row['TeamID'] ?>"><?= htmlspecialchars($row['TeamName']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (empty($criteria_list)): ?>
                <p style="color:red;">⚠️ 尚未設定評分標準，請先至 criteria_manage.php 新增</p>
            <?php else: ?>
                <?php foreach ($criteria_list as $crit): ?>
                    <div class="form-group">
                        <label><?= htmlspecialchars($crit['name']) ?>：</label>
                        <input type="number" name="scores[<?= $crit['id'] ?>]" min="0" max="100" required>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="form-group">
                <label for="comment">評語：</label>
                <textarea name="comment" id="comment" rows="4" required></textarea>
            </div>

            <input type="submit" value="提交評分">
        </form>
    </div>

    <!-- 刪除評分 -->
    <div id="section-deleteScore" class="section">
        <h4>刪除評分</h4>
        <?php if (empty($my_scores)): ?>
            <p>⚠️ 尚無可刪除評分紀錄</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>隊伍名稱</th>
                        <th>分數</th>
                        <th>評論</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_scores as $score): ?>
                        <tr>
                            <td><?= htmlspecialchars($score['TeamName']) ?></td>
                            <td><?= $score['ScoreValue'] ?></td>
                            <td><?= htmlspecialchars($score['Comment']) ?></td>
                            <td>
                                <form method="POST" action="delete_score.php" onsubmit="return confirm('確定要刪除這筆評分嗎？');">
                                    <input type="hidden" name="score_id" value="<?= $score['ScoreID'] ?>">
                                    <button type="submit" style="background-color: #d9534f;">刪除</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<script>
    
    const submissions = <?= json_encode($submission_map) ?>;
    function filterTeams() {
        const input = document.getElementById('teamSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#section-teams tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const teamId = cells[0]?.textContent.toLowerCase() || '';
            const teamName = cells[1]?.textContent.toLowerCase() || '';

            if (teamId.includes(input) || teamName.includes(input)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    function showModal(teamID) {
        const data = submissions[teamID];
        if (!data) return;

        const html = `
            <p><strong>簡介：</strong> ${data.Description || '無'}</p>
            <p><strong>海報：</strong> <a href="${data.PosterLink}" target="_blank">${data.PosterLink}</a></p>
            <p><strong>影片：</strong> <a href="${data.VideoLink}" target="_blank">${data.VideoLink}</a></p>
            <p><strong>原始碼：</strong> <a href="${data.CodeLink}" target="_blank">${data.CodeLink}</a></p>
        `;
        document.getElementById('modal-content').innerHTML = html;
        document.getElementById('submissionModal').style.display = 'block';
        document.getElementById('modalOverlay').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('submissionModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
    }
</script>

<script>
function showSection(section) {
    document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
    document.getElementById('section-' + section).classList.add('active');
}
</script>
<!-- Modal 彈出框 -->
<div id="submissionModal" style="
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
    padding: 20px;
    z-index: 9999;
    width: 400px;
    max-width: 90%;
">
    <h3 id="modal-title">作品資訊</h3>
    <div id="modal-content"></div>
    <button onclick="closeModal()" style="margin-top: 10px;">關閉</button>
</div>
<!-- Modal 背景 -->
<div id="modalOverlay" style="
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: rgba(0,0,0,0.4);
    z-index: 9998;
" onclick="closeModal()"></div>

</body>
</html>

<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['judge_id'])) {
    header("Location: login_judge.php");
    exit();
}

// 讀取現有評分標準
$stmt = $pdo->query("SELECT * FROM criteria ORDER BY id ASC");
$criteria_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>評分標準管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3fa;
            display: flex;
            justify-content: center;
            padding: 50px 0;
        }
        .container {
            background: white;
            padding: 30px;
            width: 600px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        h2 {
            color: #0057b8;
            text-align: center;
            margin-bottom: 20px;
        }
        .criteria-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .criteria-item span {
            flex-grow: 1;
            padding: 0 10px;
            cursor: pointer;
        }
        input[type="text"] {
            width: 70%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-right: 10px;
        }
        button {
            background-color: #0073e6;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #004a99;
        }
        .delete-btn {
            background-color: #d9534f;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>評分標準管理</h2>

    <div id="criteria-list">
        <?php foreach ($criteria_list as $item): ?>
            <div class="criteria-item" data-id="<?= $item['id'] ?>">
                <span onclick="editCriteria(this)"><?= htmlspecialchars($item['name']) ?></span>
                <button class="delete-btn" onclick="deleteCriteria(<?= $item['id'] ?>)">刪除</button>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 20px;">
        <input type="text" id="new-criteria" placeholder="新增評分標準">
        <button onclick="addCriteria()">新增</button>
    </div>
</div>

<script>
function addCriteria() {
    const name = document.getElementById('new-criteria').value.trim();
    if (!name) return alert("請輸入名稱");
    fetch('criteria_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=add&name=' + encodeURIComponent(name)
    }).then(res => res.text()).then(() => location.reload());
}

function deleteCriteria(id) {
    if (!confirm("確定要刪除嗎？")) return;
    fetch('criteria_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete&id=' + id
    }).then(res => res.text()).then(() => location.reload());
}

function editCriteria(span) {
    const oldName = span.innerText;
    const id = span.parentElement.getAttribute('data-id');
    const input = document.createElement('input');
    input.type = 'text';
    input.value = oldName;
    input.onblur = function() {
        const newName = input.value.trim();
        if (newName && newName !== oldName) {
            fetch('criteria_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=edit&id=' + id + '&name=' + encodeURIComponent(newName)
            }).then(() => location.reload());
        } else {
            span.innerText = oldName;
        }
    };
    span.replaceWith(input);
    input.focus();
}
</script>
</body>
</html>

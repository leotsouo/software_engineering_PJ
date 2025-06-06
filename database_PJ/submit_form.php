<?php
require_once 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 從表單獲取資料
        $teacher_id = $_POST['teacher_id'] ?? null;
        $team_name = $_POST['team_name'] ?? '';
        $registration_date = $_POST['registration_date'] ?? '';
        $members = $_POST['members'] ?? [];
        $competition_category = $_POST['competition_category'] ?? '';
        $project_name = $_POST['project_name'] ?? '';
        $project_summary = $_POST['project_summary'] ?? '';

        // 檢查是否有重複隊伍名稱
        $sql_check = "SELECT COUNT(*) FROM team WHERE TeamName = :team_name";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':team_name', $team_name);
        $stmt_check->execute();
        $existing_count = $stmt_check->fetchColumn();

        if ($existing_count > 0) {
            // 顯示美化錯誤訊息（清空表單 → 重新填寫）
            echo "
            <!DOCTYPE html>
            <html lang='zh-TW'>
            <head>
                <meta charset='UTF-8'>
                <title>隊伍名稱重複</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f5f5f5;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                    }
                    .error-box {
                        background-color: #fff;
                        padding: 30px 40px;
                        border-radius: 10px;
                        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
                        text-align: center;
                        max-width: 400px;
                    }
                    .error-box h2 {
                        color: #c62828;
                        margin-bottom: 15px;
                    }
                    .error-box p {
                        color: #555;
                        font-size: 16px;
                    }
                    .back-btn {
                        display: inline-block;
                        margin-top: 20px;
                        padding: 10px 20px;
                        background-color: #c62828;
                        color: white;
                        text-decoration: none;
                        border-radius: 5px;
                        transition: background-color 0.3s ease;
                    }
                    .back-btn:hover {
                        background-color: #a91f1f;
                    }
                </style>
            </head>
            <body>
                <div class='error-box'>
                    <h2>⚠️ 重複的隊伍名稱</h2>
                    <p>隊伍名稱 <strong>" . htmlspecialchars($team_name) . "</strong> 已被使用，請重新填寫表單。</p>
                    <a href='signup_form.php' class='back-btn'>返回重新填寫</a>
                </div>
            </body>
            </html>";
            exit;
        }

        // 插入 team 表
        $sql_team = "INSERT INTO team (TeamName, RegistrationDate, TeacherID, competition_category) 
                     VALUES (:team_name, :registration_date, :teacher_id, :competition_category)";
        $stmt_team = $pdo->prepare($sql_team);
        $stmt_team->bindParam(':team_name', $team_name);
        $stmt_team->bindParam(':registration_date', $registration_date);
        $stmt_team->bindParam(':teacher_id', $teacher_id);
        $stmt_team->bindParam(':competition_category', $competition_category);
        $stmt_team->execute();

        $team_id = $pdo->lastInsertId();

        // 插入隊員資料
        $sql_member = "INSERT INTO teammember (TeamID, Name, StudentID, Gender, Phone, Email) 
                       VALUES (:team_id, :name, :student_id, :gender, :phone, :email)";
        $stmt_member = $pdo->prepare($sql_member);

        foreach ($members as $member) {
            $name = $member['name'] ?? '';
            $student_id = $member['student_id'] ?? '';
            $gender = $member['gender'] ?? '';
            $phone = $member['phone'] ?? '';
            $email = $member['email'] ?? '';

            $stmt_member->bindParam(':team_id', $team_id);
            $stmt_member->bindParam(':name', $name);
            $stmt_member->bindParam(':student_id', $student_id);
            $stmt_member->bindParam(':gender', $gender);
            $stmt_member->bindParam(':phone', $phone);
            $stmt_member->bindParam(':email', $email);
            $stmt_member->execute();
        }

        // 儲存至 session 給 confirmation 頁面使用
        $_SESSION['confirmation_data'] = [
            'teacher_id' => $teacher_id,
            'team_name' => $team_name,
            'competition_category' => $competition_category,
            'registration_date' => $registration_date,
            'project_name' => $project_name,
            'project_summary' => $project_summary,
            'members' => $members
        ];

        header('Location: confirmation.php');
        exit;
    } catch (Exception $e) {
        echo "<p style='color:red;'>錯誤：{$e->getMessage()}</p>";
    }
} else {
    echo "<p style='color:red;'>無效的請求方法。</p>";
}

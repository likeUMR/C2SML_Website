<?php
session_start();
require_once 'utils/verification_utils.php';

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    // 将当前页面的 URL 作为来源 URL 传递给登录页面
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /login/login.php?redirect=$redirect_url");
    exit();
}

// 连接数据库
$db = new SQLite3('db/users.db');

// 获取用户ID
$user_id = $_SESSION['user_id'];

// 查询用户信息
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bindValue(1, $user_id);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

// 初始化消息变量
$error_message = '';
$success_message = '';
$info_message = '';
$new_email = ''; // 初始化新邮箱变量

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['send_code'])) {
        // 发送验证码
        $new_email = $_POST['new_email']; // 新的邮箱

        // 检查新邮箱是否已经存在
        $emailCheckQuery = "SELECT * FROM users WHERE email = ?";
        $emailCheckStmt = $db->prepare($emailCheckQuery);
        $emailCheckStmt->bindValue(1, $new_email);
        $emailCheckResult = $emailCheckStmt->execute();

        if ($emailCheckResult->fetchArray()) {
            // 如果新邮箱已经被注册
            $error_message = "该邮箱已被注册，请使用其他邮箱。";
        } else {
            // 如果新邮箱未被注册，发送验证码
            $verificationCode = generateVerificationCode();
            $sendStatus = sendVerificationEmail($new_email, $verificationCode);

            if ($sendStatus) {
                // 存储验证码到数据库
                storeVerificationCode($new_email, $verificationCode);
                $_SESSION['pending_email'] = $new_email; // 临时保存新邮箱
                $info_message = "验证码已发送到您的新邮箱，请查收。";
                echo "<script>
                alert('$info_message');
                </script>";
            } else {
                $error_message = "验证码发送失败，请稍后重试。";
            }
        }
    }

    if (isset($_POST['verify_code'])) {
        // 验证用户输入的验证码
        $enteredCode = $_POST['verification_code'];
        $pendingEmail = $_SESSION['pending_email'];

        if (verifyVerificationCode($pendingEmail, $enteredCode)) {
            // 验证成功，更新用户信息
            $stmt = $db->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bindValue(1, $pendingEmail);
            $stmt->bindValue(2, $user_id);
            $stmt->execute();

            $success_message = "邮箱更新成功！";
            unset($_SESSION['pending_email']); // 清除临时保存的邮箱
            $new_email = ''; // 清空新邮箱
            // 使用 JavaScript 弹窗
            echo "<script>
            alert('$success_message');
            window.location.href = 'person_info.php'; // 重定向回个人信息页面
            </script>";
            exit(); // 终止脚本执行
        } else {
            $error_message = "验证码无效或已过期，请重试。";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>会议登记系统 - 修改邮箱</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">修改邮箱</h1>

        <?php if (isset($success_message)): ?>
            <p class="text-green-500 mb-4"><?= $success_message; ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p class="text-red-500 mb-4"><?= $error_message; ?></p>
        <?php endif; ?>

        <!-- 显示原邮箱 -->
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">原邮箱</label>
            <p class="border rounded w-full py-2 px-3 text-gray-700"><?= htmlspecialchars($user['email']); ?></p>
        </div>

        <!-- 新邮箱输入 -->
        <form action="" method="post" class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="new_email">新邮箱</label>
            <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="email" name="new_email" value="<?= htmlspecialchars($new_email); ?>" required>
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline mt-2" type="submit" name="send_code">
                发送验证码
            </button>
        </form>

        <?php if (isset($info_message)): ?>
        <!-- 验证码输入 -->
        <form action="" method="post">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="verification_code">验证码</label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="verification_code" required>
            </div>
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="verify_code">
                提交修改
            </button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>

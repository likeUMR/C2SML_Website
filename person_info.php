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

?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会议登记系统 - 个人信息</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex">
    <?php include 'sidebar.php'; ?>
    <?php 
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

    // 处理其他信息更新
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $school = $_POST['school'];
        $phone = $_POST['phone'];
        $department = $_POST['department'];
        $tutor = $_POST['tutor'];

        // 更新用户信息
        $stmt = $db->prepare("UPDATE users SET username = ?, school = ?, phone = ?, department = ?, tutor = ? WHERE id = ?");
        $stmt->bindValue(1, $username);
        $stmt->bindValue(2, $school);
        $stmt->bindValue(3, $phone);
        $stmt->bindValue(4, $department);
        $stmt->bindValue(5, $tutor);
        $stmt->bindValue(6, $user_id);
        $stmt->execute();

        $success_message = "个人信息更新成功！";
        //重新加载页面应用新的用户信息
        echo "<script>
        alert('$success_message');
        window.location.href = 'person_info.php'; // 重定向回个人信息页面
        </script>";
        exit();
    }
    ?>
    <div class="flex-grow flex justify-center items-start p-6">
        <div class="bg-white p-8 rounded-lg shadow-lg w-1/2">
            <div class="text-center mb-6">
                <h2 class="text-3xl font-bold text-gray-900">个人信息</h2>
                <p class="mt-1 text-base text-gray-600">查看并更新您的个人资料</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-6" role="alert">
                    <p class="text-base"><?= $success_message; ?></p>
                </div>
            <?php endif; ?>

            <form action="" method="post" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <input id="username" name="username" type="text" required value="<?= htmlspecialchars($user['username']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-sm border-gray-300 rounded-md p-2">
                </div>
                <div>
                    <label for="school" class="block text-sm font-medium text-gray-700 mb-1">学校</label>
                    <input id="school" name="school" type="text" required value="<?= htmlspecialchars($user['school']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-sm border-gray-300 rounded-md p-2">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">邮箱</label>
                    <p id="email" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-50 rounded-md shadow-sm text-gray-700 text-sm"><?= htmlspecialchars($user['email']); ?></p>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">电话</label>
                    <input id="phone" name="phone" type="text" value="<?= htmlspecialchars($user['phone']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-sm border-gray-300 rounded-md p-2">
                </div>
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 mb-1">系/部门</label>
                    <input id="department" name="department" type="text" value="<?= htmlspecialchars($user['department']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-sm border-gray-300 rounded-md p-2">
                </div>
                <div>
                    <label for="tutor" class="block text-sm font-medium text-gray-700 mb-1">导师</label>
                    <input id="tutor" name="tutor" type="text" value="<?= htmlspecialchars($user['tutor']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm text-sm border-gray-300 rounded-md p-2">
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        更新信息
                    </button>
                </div>
            </form>

            <div class="text-center mt-6">
                <a href="change_email.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">修改邮箱</a>
            </div>
        </div>
    </div>
</body>
</html>
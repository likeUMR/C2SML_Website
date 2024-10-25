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
    <div class="flex-grow flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-lg">
            <div class="text-center">
                <img src="icon2.png" alt="Logo" class="mx-auto h-12 w-auto">
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">个人信息</h2>
                <p class="mt-2 text-sm text-gray-600">更新您的个人资料</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?= $success_message; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="post" class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="mb-4">
                        <label for="username" class="sr-only">姓名</label>
                        <input id="username" name="username" type="text" required value="<?= htmlspecialchars($user['username']); ?>"
                               class="appearance-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="姓名">
                    </div>
                    <div class="mb-4">
                        <label for="school" class="sr-only">学校</label>
                        <input id="school" name="school" type="text" required value="<?= htmlspecialchars($user['school']); ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="学校">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="sr-only">邮箱</label>
                        <input id="email" type="email" value="<?= htmlspecialchars($user['email']); ?>" disabled
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm bg-gray-100"
                               placeholder="邮箱">
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="sr-only">电话</label>
                        <input id="phone" name="phone" type="text" value="<?= htmlspecialchars($user['phone']); ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="电话">
                    </div>
                    <div class="mb-4">
                        <label for="department" class="sr-only">系/部门</label>
                        <input id="department" name="department" type="text" value="<?= htmlspecialchars($user['department']); ?>"
                               class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="系/部门">
                    </div>
                    <div class="mb-4">
                        <label for="tutor" class="sr-only">导师</label>
                        <input id="tutor" name="tutor" type="text" value="<?= htmlspecialchars($user['tutor']); ?>"
                               class="appearance-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                               placeholder="导师">
                    </div>
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        更新信息
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">其他操作</span>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="change_email.php"
                       class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        修改邮箱
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
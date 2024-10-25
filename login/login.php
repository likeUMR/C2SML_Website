<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会议登记系统 - 登录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-lg">
        <div class="text-center">
            <img src="<?php echo dirname($_SERVER['PHP_SELF']) . '/../icon2.png'; ?>" alt="Logo" class="mx-auto h-12 w-auto">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">会议登记系统登录</h2>
            <p class="mt-2 text-sm text-gray-600">使用您的系统账号登录</p>
        </div>

        <form class="mt-8 space-y-6" action="" method="POST">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect'] ?? '', ENT_QUOTES); ?>">

            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="email" class="sr-only">邮箱</label>
                    <input id="email" name="email" type="email" required
                        class="appearance-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="邮箱">
                </div>

                <div class="mb-4">
                    <label for="password" class="sr-only">密码</label>
                    <input id="password" name="password" type="password" required
                        class="appearance-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                        placeholder="密码">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <a href="<?php echo dirname($_SERVER['PHP_SELF']) . '/reset_password.php'; ?>" class="font-medium text-blue-600 hover:text-blue-500">忘记密码？</a>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    登录
                </button>
            </div>
        </form>

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">或</span>
                </div>
            </div>
            <div class="mt-6">
                <a href="register.php"
                    class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    创建账户
                </a>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="<?php echo dirname($_SERVER['PHP_SELF']) . '/../index.php'; ?>" class="text-blue-500 hover:text-blue-700">返回主页</a>
        </div>
    </div>

    <?php
    session_start();

    define('ROLE_SUPER_ADMIN', 'super_admin');
    define('ROLE_ADMIN', 'admin');
    define('ROLE_USER', 'user');

    $db = new SQLite3('../db/users.db');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);

        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // 获取来源 URL
            $redirect_url = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : '../index.php'; // 默认重定向到主页
            header("Location: " . urldecode($redirect_url));
            exit();
        } else {
            echo "<script>alert('邮箱或密码无效，请重试。');</script>";
        }
    }
    ?>
</body>

</html>
<!DOCTYPE html>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 获取网站根目录的相对路径
$root_path = dirname($_SERVER['PHP_SELF'], 2);
require_once  __DIR__ . '/../utils/verification_utils.php'; // 引入权限验证工具

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    // 将当前页面的 URL 作为来源 URL 传递给登录页面
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: $root_path/login/login.php?redirect=$redirect_url");
    exit();
}

// 检查用户权限
$userId = $_SESSION['user_id'];
$requiredPermissionLevel = 'administrator'; // 需要的权限等级

if (!hasPermission($userId, $requiredPermissionLevel)) {
    // 用户权限不足，跳转回主页
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
    header("Location: $relativePath/../index.php"); // 假设主页为 index.php    
    exit();
}
?>


<html>

<head>
    <title>会议登记系统 - 添加用户</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">添加用户</h1>
        <form action="process_add_user.php" method="post">
            <?php
            // 连接数据库获取表结构
            $db = new SQLite3('../db/users.db');
            $query = "PRAGMA table_info(users)";
            $result = $db->query($query);
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if ($row['name']!== 'id') {
                    echo "<label class='block font-bold mb-2'>{$row['name']}：</label>";
                    echo "<input type='text' name='new_{$row['name']}' class='border border-gray-300 p-2 w-full'>";
                }
            }
            $db->close();
          ?>
            <button type="submit" class="bg-blue-500 text-white p-2 mt-2">添加</button>
        </form>
    </div>
</body>

</html>
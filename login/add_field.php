<!DOCTYPE html>
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once  __DIR__ . '/../utils/verification_utils.php'; // 引入权限验证工具

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    // 将当前页面的 URL 作为来源 URL 传递给登录页面
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /login/login.php?redirect=$redirect_url");
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
    <title>会议登记系统 - 添加字段</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">添加字段</h1>
        <form action="process_add_field.php" method="post">
            <label class="block font-bold mb-2">新字段名：</label>
            <input type="text" name="new_field_name" class="border border-gray-300 p-2 w-full">
            <label class="block font-bold mb-2">默认值：</label>
            <input type="text" name="default_value" class="border border-gray-300 p-2 w-full">
            <button type="submit" class="bg-blue-500 text-white p-2 mt-2">添加</button>
        </form>
    </div>
</body>

</html>
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
    <title>会议登记系统 - 用户列表</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="flex bg-gray-100">
    <?php include  __DIR__ . '/../sidebar.php'; ?>
    <div class="content flex-grow ml-4 p-8">
        <h1 class="text-3xl font-bold mb-4">用户列表</h1>
        <!-- 添加用户链接 -->
        <a href="<?php echo $root_path; ?>/login/add_user.php" class="bg-green-500 text-white p-2 mb-4 mr-2">添加用户</a>
        <a href="<?php echo $root_path; ?>/login/rename_fields.php" class="bg-yellow-500 text-white p-2 mb-4 mr-2">重命名字段</a>
        <a href="<?php echo $root_path; ?>/login/add_field.php" class="bg-purple-500 text-white p-2 mb-4 mr-2">添加字段</a>
        <!-- <a href="sort_fields.php" class="bg-indigo-500 text-white p-2 mb-4">排序字段</a> -->
        <a href="<?php echo $root_path; ?>/login/export_data.php" class="bg-blue-500 text-white p-2 mb-4 mr-2">导出数据</a>
        <a href="<?php echo $root_path; ?>/login/import_page.php" class="bg-blue-500 text-white p-2 mb-4">导入数据</a>
        <?php
        // 连接数据库
        $db = new SQLite3(__DIR__ . '/../db/users.db');

        // 查询数据库中的所有数据，但排除 password_hash 字段
        $query = "SELECT id, username, school, email, role, title, other_type, department, phone, tutor, permission, permission_domain FROM users";
        $result = $db->query($query);

        // 获取列信息
        $columnInfo = $result->numColumns();

        // 开始输出 HTML 内容
        echo "<table class='w-full border-collapse border border-gray-300'>";
        echo "<tr>";
        for ($i = 0; $i < $columnInfo; $i++) {
            $columnName = $result->columnName($i);
            echo "<th class='px-4 py-2 bg-gray-200'>{$columnName}</th>";
        }
        echo "</tr>";

        // 遍历结果集并输出到表格中
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td class='px-4 py-2'>{$value}</td>";
            }
            // 添加修改按钮
            echo "<td><a href='{$root_path}/login/edit_user.php?id={$row['id']}' class='text-blue-500'>修改</a></td>";
            // 添加删除按钮
            echo "<td><a href='{$root_path}/login/delete_user.php?id={$row['id']}' class='text-red-500' onclick='return confirm(\"确定要删除该用户吗？\")'>删除</a></td>";
            echo "</tr>";
        }

        echo "</table>";
        // 关闭数据库连接
        $db->close();
     ?>
    </div>
</body>

</html>
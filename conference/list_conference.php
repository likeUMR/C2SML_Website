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
    header("Location: /index.php"); // 假设主页为 index.php
    exit();
}
?>





<html>

<head>
    <title>会议登记系统 - 大会信息</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="flex bg-gray-100">
    <?php include  __DIR__ . '/../sidebar.php'; ?>
    <div class="content flex-grow ml-4 p-8">
        <h1 class="text-3xl font-bold mb-4">大会信息</h1>
        <!-- 添加大会信息链接 -->
        <a href="add_conference.php" class="bg-green-500 text-white p-2 mb-4 mr-2">添加大会信息</a>
        <a href="export_data.php" class="bg-blue-500 text-white p-2 mb-4 mr-2">导出数据</a>
        <a href="import_page.php" class="bg-blue-500 text-white p-2 mb-4">导入数据</a>

        <?php
        // 连接数据库
        $db = new SQLite3('../db/conference_main.db');

        // 查询大会信息
        $query = "SELECT id, conference_name, start_date, end_date, location, description, website_url, sessions, committee_members FROM conference_info";
        $result = $db->query($query);

        // 获取列信息
        $columnInfo = $result->numColumns();

        // 开始输出 HTML 表格
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
            foreach ($row as $key => $value) {
                // 对组委会信息和分会列表进行解码展示
                // if ($key === 'sessions' || $key === 'committee_members') {
                //     $decoded_value = implode(', ', json_decode($value, true));
                //     echo "<td class='px-4 py-2'>{$decoded_value}</td>";
                // } else {
                    echo "<td class='px-4 py-2'>{$value}</td>";
                // }
            }
            // 添加修改按钮
            echo "<td><a href='edit_conference.php?id={$row['id']}' class='text-blue-500'>修改</a></td>";
            // 添加删除按钮
            echo "<td><a href='delete_conference.php?id={$row['id']}' class='text-red-500' onclick='return confirm(\"确定要删除该大会信息吗？\")'>删除</a></td>";
            echo "</tr>";
        }

        echo "</table>";
        // 关闭数据库连接
        $db->close();
        ?>
    </div>
</body>

</html>

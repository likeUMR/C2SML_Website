<?php
session_start();

require_once dirname(__FILE__) . '/utils/verification_utils.php'; // 使用相对路径

// 检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    // 将当前页面的 URL 作为来源 URL 传递给登录页面
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/login/login.php?redirect=$redirect_url");
    exit();
}

// 检查用户权限
$userId = $_SESSION['user_id'];
$requiredPermissionLevel = 'administrator'; // 需要的权限等级

if (!hasPermission($userId, $requiredPermissionLevel)) {
    // 用户权限不足，跳转回主页
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/index.php");
    exit();
}

// 连接到用户数据库
$dbUsers = new SQLite3(dirname(__FILE__) . '/db/users.db'); // 用户信息存储在 users.db 中

// 用户表字段说明：
// id (INTEGER PRIMARY KEY): 用户的唯一标识符
// username (TEXT): 用户名
// school (TEXT): 用户所在的学校
// email (TEXT): 用户的邮箱地址
// role (TEXT): 用户的角色（如 教师、学生、其它）
// title (TEXT): 用户的职称（可选）
// department (TEXT): 用户的部门（可选）
// phone (TEXT): 用户的电话（可选）
// tutor (TEXT): 用户的导师（可选）
// password_hash (TEXT): 用户的密码哈希值

// 连接到会议数据库
$dbConferences = new SQLite3(dirname(__FILE__) . '/db/conference_main.db'); // 会议信息存储在 conference_main.db 中

// 会议信息表字段说明：
// id (INTEGER PRIMARY KEY): 会议的唯一标识符
// conference_name (TEXT): 会议的名称
// start_date (TEXT): 会议的开始日期
// end_date (TEXT): 会议的结束日期
// location (TEXT): 会议的地点
// website (TEXT): 会议的官方网站链接（可选）
// description (TEXT): 会议的简介（可选）
// committee_members (TEXT): 组委会成员（以某种格式存储，例如用 + 分隔）
// sessions (TEXT): 会议的分会场信息（以某种格式存储，例如用 + 分隔）

// 查询当前注册者人数
$registrantsQuery = "SELECT COUNT(*) as count FROM users"; // 获取用户表中的注册人数
$registrantsResult = $dbUsers->query($registrantsQuery);
$registrantsCount = $registrantsResult->fetchArray(SQLITE3_ASSOC)['count'];

// 查询当前已注册会议的数量
$conferencesQuery = "SELECT COUNT(*) as count FROM conference_info"; // 获取会议信息表中的会议数量
$conferencesResult = $dbConferences->query($conferencesQuery);
$conferencesCount = $conferencesResult->fetchArray(SQLITE3_ASSOC)['count'];

// 关闭数据库连接
$dbUsers->close();
$dbConferences->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>会议登记系统 - 后台管理</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="flex bg-gray-100">
    <?php include dirname(__FILE__) . '/sidebar.php'; ?>
    <div class="content flex-grow ml-4 p-8">
        <h1 class="text-3xl font-bold mb-6">后台管理</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
            <!-- 注册者管理卡片 -->
            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/login/list_user.php" class="bg-white shadow-md rounded-lg p-6 block hover:shadow-lg transition-shadow duration-300">
                <h2 class="text-2xl font-semibold mb-2">注册者管理</h2>
                <p class="text-gray-700 mb-2"><strong>当前注册人数：</strong><?php echo htmlspecialchars($registrantsCount, ENT_QUOTES); ?></p>
            </a>

            <!-- 会议管理卡片 -->
            <a href="<?php echo dirname($_SERVER['PHP_SELF']); ?>/conference/list_conference.php" class="bg-white shadow-md rounded-lg p-6 block hover:shadow-lg transition-shadow duration-300">
                <h2 class="text-2xl font-semibold mb-2">会议管理</h2>
                <p class="text-gray-700 mb-2"><strong>当前已注册会议数量：</strong><?php echo htmlspecialchars($conferencesCount, ENT_QUOTES); ?></p>
            </a>
        </div>
    </div>
</body>
</html>

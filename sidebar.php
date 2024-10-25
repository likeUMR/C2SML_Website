<?php
// 检查会话是否已经启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once  'utils/verification_utils.php'; // 引入权限验证工具
$relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
?>
<div class="sidebar bg-gray-800 text-white h-screen w-64 p-8 flex flex-col justify-between">
<!-- <div class="sidebar bg-gray-800 text-white h-screen fixed top-0 left-0 p-8 flex flex-col justify-between" style="width: 13%;"> -->
    <div>
        <h1 class="text-3xl text-green-500 font-bold mb-4">菜单</h1>
        <!-- <?php echo $relativePath; ?> -->
        <nav>
            <ul class="space-y-2">
                <li><a href="<?php echo htmlspecialchars($relativePath . '/index.php'); ?>" class="block py-2 px-4 hover:bg-gray-700 rounded transition duration-200">首页</a></li>
                <li><a href="<?php echo htmlspecialchars($relativePath . '/person_info.php'); ?>" class="block py-2 px-4 hover:bg-gray-700 rounded transition duration-200">个人信息</a></li>
                <!-- <li><a href="<?php echo htmlspecialchars($relativePath . '/my_reviews.php'); ?>" class="block py-2 px-4 hover:bg-gray-700 rounded transition duration-200">我的审核</a></li> -->
                <!-- <li><a href="<?php echo htmlspecialchars($relativePath . '/my_conferences.php'); ?>" class="block py-2 px-4 hover:bg-gray-700 rounded transition duration-200">我的会议</a></li> -->
                <!-- <li><a href="<?php echo htmlspecialchars($relativePath . '/reminders.php'); ?>" class="block py-2 px-4 hover:bg-gray-700 rounded transition duration-200">提醒</a></li> -->

                <!-- 如果用户权限为 administrator，显示后台管理面板链接 -->
                <?php if (isset($_SESSION['user_id']) && hasPermission($_SESSION['user_id'], 'administrator')): ?>
                    <li><a href="<?php echo htmlspecialchars($relativePath . '/dashboard.php'); ?>" class="block py-2 px-4 hover:bg-blue-600 bg-blue-500 rounded transition duration-200">后台管理面板</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <div class="mt-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="mb-4">
                <a href="<?php echo htmlspecialchars($relativePath . '/logout.php'); ?>" class="block py-2 px-4 hover:bg-red-600 bg-red-500 rounded transition duration-200 text-center">退出登录</a>
            </div>
            <div class="pt-4 border-t border-gray-600">
                <?php
                $user_id = $_SESSION['user_id'];
                $db = new SQLite3(__DIR__ . '/db/users.db');
                $stmt = $db->prepare("SELECT username, role, title FROM users WHERE id = :user_id");
                $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                $result = $stmt->execute();
                $user = $result->fetchArray(SQLITE3_ASSOC);

                // 设置中国时区
                date_default_timezone_set('Asia/Shanghai');
                
                $hour = date('H');
                $greeting = ($hour >= 5 && $hour < 12) ? '早上' : (($hour >= 12 && $hour < 18) ? '下午' : '晚上');

                $title = '';
                switch ($user['role']) {
                    case '教师':
                        $title = !empty($user['title']) ? $user['title'] : '老师';
                        break;
                    case '学生':
                        $title = '同学';
                        break;
                    case '其它':
                        $title = !empty($user['other_type']) ? $user['title'] : '';
                        break;
                    default:
                        $title = $user['role'];
                }

                echo "<p class='text-sm'>{$greeting}好！</p>";
                echo "<p class='text-sm mt-1'>尊敬的 {$user['username']} {$title}</p>";
                ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <a href="<?php echo htmlspecialchars($relativePath . '/login/login.php'); ?>" class="block py-3 px-6 bg-green-500 hover:bg-green-600 rounded-lg transition duration-200 text-white font-bold shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    前往登录
                </a>
                <p class="mt-2 text-sm text-gray-400">登录以访问更多功能</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 移除这个 div -->
<!-- <div class="ml-[13%]">
    这里放置主要内容
</div> -->

<!-- 帮我参考login的风格，美化这个sidebar，使其变得非常的美观 -->
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
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/login.php?redirect=$redirect_url");
    exit();
}

// 检查用户权限
$userId = $_SESSION['user_id'];
$requiredPermissionLevel = 'administrator'; // 需要的权限等级

if (!hasPermission($userId, $requiredPermissionLevel)) {
    // 用户权限不足，跳转回主页
    header("Location: " . dirname(dirname($_SERVER['PHP_SELF'])) . "/index.php");
    exit();
}
?>

<html>
<head>
    <title>会议登记系统 - 删除用户</title>
</head>

<body>
    <?php
    try {
        // 日志: 开始删除用户操作
        error_log("开始尝试删除用户操作");

        // 过滤并验证用户ID
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === false || $id === null) {
            throw new Exception("无效的用户ID");
        }

        // 日志: 验证用户ID成功
        error_log("用户ID验证成功: {$id}");

        // 数据库路径
        $dbPath = dirname(__DIR__) . '/db/users.db';

        // 检查数据库文件是否存在和可写
        if (!file_exists($dbPath)) {
            throw new Exception("数据库文件不存在: {$dbPath}");
        }

        if (!is_writable($dbPath)) {
            throw new Exception("数据库文件不可写: {$dbPath}");
        }

        // 日志: 数据库文件检查通过
        error_log("数据库文件存在且可写: {$dbPath}");

        // 打开数据库
        $db = new SQLite3($dbPath, SQLITE3_OPEN_READWRITE);
        if (!$db) {
            throw new Exception("无法以可写模式连接到数据库: " . $db->lastErrorMsg());
        }

        // 日志: 数据库连接成功
        error_log("成功连接到数据库");

        // 开始事务
        $db->exec('BEGIN');
        error_log("事务开始");

        // 准备SQL语句
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        if (!$stmt) {
            throw new Exception("SQL语句准备失败: " . $db->lastErrorMsg());
        }

        // 绑定参数
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

        // 执行删除操作
        $result = $stmt->execute();
        if ($result === false) {
            throw new Exception("删除用户失败: " . $db->lastErrorMsg());
        }

        // 检查影响的行数
        $changes = $db->changes();
        if ($changes === 0) {
            throw new Exception("未找到指定ID的用户，或用户已被删除");
        }

        // 提交事务
        $db->exec('COMMIT');
        error_log("事务提交，用户ID {$id} 已被成功删除");

        // 关闭数据库连接
        $db->close();

        // 重定向到用户列表页面
        header("Location: " . dirname($_SERVER['PHP_SELF']) . "/list_user.php");
        exit();
    } catch (Exception $e) {
        // 如果有事务未提交，回滚事务
        if (isset($db) && $db->exec('ROLLBACK')) {
            error_log("事务回滚成功");
        }

        // 记录错误日志
        error_log("删除用户时发生错误: " . $e->getMessage() . "\n" . $e->getTraceAsString());

        // 显示错误信息给用户（注意防止XSS）
        echo "删除用户时发生错误: " . htmlspecialchars($e->getMessage());
    }
    ?>
</body>
</html>

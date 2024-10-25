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

// 连接数据库
$db = new SQLite3('../db/users.db');

// 查询数据库中的所有数据
$query = "SELECT * FROM users";
$result = $db->query($query);

// 设置 CSV 文件头
header('Content-Type: text/csv; charset=gb18030');
header('Content-Disposition: attachment; filename="users.csv"');

// 打开输出流
$output = fopen('php://output', 'w');

// 输出 GB18030 BOM (Excel 兼容)
// fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 获取列名并写入 CSV 文件头
$columnQuery = "PRAGMA table_info(users)";
$columnResult = $db->query($columnQuery);
$columnNames = array();
while ($columnRow = $columnResult->fetchArray(SQLITE3_ASSOC)) {
    $columnNames[] = $columnRow['name'];
}
// 将列名转换为 GB18030 编码
$gb18030_columnNames = array_map(function($value) {
    return mb_convert_encoding($value, 'GB18030', 'UTF-8');
}, $columnNames);
fputcsv($output, $gb18030_columnNames);

// 遍历结果集并写入 CSV 文件
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    // 将每一行的数据转换为 GB18030 编码
    $gb18030_row = array_map(function($value) {
        return mb_convert_encoding($value, 'GB18030', 'UTF-8');
    }, $row);
    fputcsv($output, $gb18030_row);
}

// 关闭数据库连接
$db->close();

// 关闭输出流
fclose($output);
?>

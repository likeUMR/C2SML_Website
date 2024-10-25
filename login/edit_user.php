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
    <title>会议登记系统 - 修改用户</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
    <?php
// 确保存在 id 参数
if (!isset($_GET['id'])) {
    die("无效的用户 ID");
}

$id = $_GET['id'];
$db = new SQLite3(dirname(__DIR__) . '/db/users.db');

// 使用参数化查询
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();

$row = $result->fetchArray(SQLITE3_ASSOC);

// 检查是否查询到了结果
if (!$row) {
    die("未找到用户信息");
}

// 获取表结构
$tableInfoQuery = "PRAGMA table_info(users)";
$tableInfoResult = $db->query($tableInfoQuery);
?>

<h1 class="text-3xl font-bold mb-4">修改用户信息</h1>
<form action="" method="post">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

    <?php
    // 遍历表结构并显示表单字段
    while ($tableInfoRow = $tableInfoResult->fetchArray(SQLITE3_ASSOC)) {
        if ($tableInfoRow['name'] !== 'id') {
            if ($tableInfoRow['name'] === 'password_hash') {
                echo "<label class='block font-bold mb-2'>password：</label>";
                // 对于密码字段,显示一个空的密码输入框
                echo "<input type='password' name='password' placeholder='输入新密码(留空则不修改)' class='border border-gray-300 p-2 w-full'>";
            } else {
                echo "<label class='block font-bold mb-2'>{$tableInfoRow['name']}：</label>";
                $value = isset($row[$tableInfoRow['name']]) ? $row[$tableInfoRow['name']] : '';
                echo "<input type='text' name='{$tableInfoRow['name']}' value='". htmlspecialchars($value, ENT_QUOTES) . "' class='border border-gray-300 p-2 w-full'>";
            }
        }
    }
    ?>
    <button type="submit" name="update_user" class="bg-blue-500 text-white p-2 mt-2">更新用户</button>
</form>

<?php
if (isset($_POST['update_user'])) {
    $updateQuery = "UPDATE users SET ";
    $first = true;

    // 遍历表结构生成更新语句
    $queryResult = $db->query("PRAGMA table_info(users)");
    while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] !== 'id') {
            if ($row['name'] === 'password_hash') {
                // 如果提供了新密码,则更新密码哈希
                if (!empty($_POST['password'])) {
                    if (!$first) {
                        $updateQuery .= ", ";
                    }
                    $newPasswordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $updateQuery .= "password_hash='" . SQLite3::escapeString($newPasswordHash) . "'";
                    $first = false;
                }
            } else {
                if (!$first) {
                    $updateQuery .= ", ";
                }
                $fieldValue = $_POST[$row['name']];
                $updateQuery .= $row['name'] . "='" . SQLite3::escapeString($fieldValue) . "'";
                $first = false;
            }
        }
    }
    $updateQuery .= " WHERE id=" . (int)$_POST['id'];

    $db->exec($updateQuery);
    header("Location: " . dirname($_SERVER['PHP_SELF']) . "/list_user.php");
}
$db->close();
?>

    </div>
</body>

</html>
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if (!isset($_SESSION['user_id'])) {
//     header("Location: /login/login.php");
//     exit();
// }

// 确保存在 id 参数
if (!isset($_GET['id'])) {
    die("无效的会议 ID");
}

// echo str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);

$id = $_GET['id'];

// 连接到数据库
$relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__);
// echo "Database Path: " . $relativePath . '/../db/conference_main.db'; // 调试用
try {
    // $db = new SQLite3($relativePath . '/../db/conference_main.db');
    $db = new SQLite3('../db/conference_main.db');
} catch (Exception $e) {
    die("数据库连接失败: " . $e->getMessage());
}


// 使用参数化查询获取会议详细信息
$query = "SELECT * FROM conference_info WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$result = $stmt->execute();
$row = $result->fetchArray(SQLITE3_ASSOC);

// 检查是否查询到了结果
if (!$row) {
    die("未找到会议信息");
}

// 关闭数据库连接
$db->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>会议登记系统 - 会议详情</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="flex bg-gray-100">
    <?php include  '../sidebar.php'; ?>
    <div class="content flex-grow ml-4 p-8">
        <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($row['conference_name'], ENT_QUOTES); ?></h1>
        
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <p class="text-gray-700 mb-2"><strong>时间：</strong><?php echo htmlspecialchars($row['start_date'], ENT_QUOTES); ?> - <?php echo htmlspecialchars($row['end_date'], ENT_QUOTES); ?></p>
            <p class="text-gray-700 mb-2"><strong>地点：</strong><?php echo htmlspecialchars($row['location'], ENT_QUOTES); ?></p>
            <?php if (!empty($row['website_url'])): ?>
                <p class="text-gray-700 mb-2"><strong>网页链接：</strong><a href="<?php echo htmlspecialchars($row['website_url'], ENT_QUOTES); ?>" target="_blank" class="text-blue-500 underline"><?php echo htmlspecialchars($row['website_url'], ENT_QUOTES); ?></a></p>
            <?php endif; ?>
            <p class="text-gray-700 mb-2"><strong>简介：</strong><?php echo nl2br(htmlspecialchars($row['description'], ENT_QUOTES)); ?></p>
            <p class="text-gray-700 mb-2"><strong>组委会成员：</strong><?php echo htmlspecialchars($row['committee_members'], ENT_QUOTES); ?></p>
            <p class="text-gray-700 mb-2"><strong>分会：</strong><?php echo htmlspecialchars($row['sessions'], ENT_QUOTES); ?></p>
        </div>

        <a href="../index.php" class="bg-blue-500 text-white p-2 rounded" onclick="clearQueryString(event)">返回会议列表</a>

        <script>
        function clearQueryString(event) {
            event.preventDefault(); // 防止默认行为（页面跳转）
            // 获取链接的目标地址
            var url = event.target.href.split('?')[0]; // 获取不带查询字符串的 URL
            window.location.href = url; // 跳转到新的 URL
        }
        </script>

    </div>
</body>
</html>

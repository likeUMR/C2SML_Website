<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>会议登记系统 - 首页</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="flex bg-gray-100">
    <?php include 'sidebar.php';
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__); ?>
    <div class="content flex-grow ml-4 p-8">
        <h1 class="text-3xl font-bold mb-6">会议列表</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php // 连接到数据库
            $db = new SQLite3('db/conference_main.db');
            // 查询所有会议的信息
            $query = "SELECT id, conference_name, start_date, end_date, location FROM conference_info ORDER BY start_date ASC";
            $result = $db->query($query);?>
            <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)) { ?>
                <a href="<?php echo $relativePath ?>/conference/conference_detail.php?id=<?php echo $row['id']; ?>" class="bg-white shadow-md rounded-lg p-6 block hover:shadow-lg transition-shadow duration-300">
                    <h2 class="text-2xl font-semibold mb-2"><?php echo htmlspecialchars($row['conference_name'], ENT_QUOTES); ?></h2>
                    <p class="text-gray-700 mb-2"><strong>时间：</strong><?php echo htmlspecialchars($row['start_date'], ENT_QUOTES); ?> - <?php echo htmlspecialchars($row['end_date'], ENT_QUOTES); ?></p>
                    <p class="text-gray-700"><strong>地点：</strong><?php echo htmlspecialchars($row['location'], ENT_QUOTES); ?></p>
                </a>
            <?php } ?>
        </div>
    </div>
</body>
</html>

<?php
// 关闭数据库连接
$db->close();
?>

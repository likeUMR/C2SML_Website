<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 删除大会信息</title>
</head>

<body>
    <?php
    // 获取大会的 ID
    $id = $_GET['id'];
    
    // 连接到 SQLite 数据库
    $db = new SQLite3('../db/conference_main.db');
    
    // 删除对应 ID 的大会信息
    $query = "DELETE FROM conference_info WHERE id = $id";
    $db->exec($query);
    
    // 关闭数据库连接
    $db->close();
    
    // 重定向到大会信息列表页面
    header("Location: list_conference.php");
    exit();
    ?>
</body>

</html>

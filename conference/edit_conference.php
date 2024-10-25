<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 修改大会信息</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
    <?php
    // 确保存在 id 参数
    if (!isset($_GET['id'])) {
        die("无效的大会 ID");
    }

    $id = $_GET['id'];
    $db = new SQLite3('../db/conference_main.db');

    // 使用参数化查询获取当前大会信息
    $query = "SELECT * FROM conference_info WHERE id = $id";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    // 获取大会信息
    $row = $result->fetchArray(SQLITE3_ASSOC);

    // 检查是否查询到了结果
    if (!$row) {
        die("未找到大会信息");
    }

    // 获取表结构
    $tableInfoQuery = "PRAGMA table_info(conference_info)";
    $tableInfoResult = $db->query($tableInfoQuery);
    ?>

    <h1 class="text-3xl font-bold mb-4">修改大会信息</h1>
    <form action="" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

        <?php
        // 遍历表结构并显示表单字段
        while ($tableInfoRow = $tableInfoResult->fetchArray(SQLITE3_ASSOC)) {
            if ($tableInfoRow['name'] !== 'id') {
                echo "<label class='block font-bold mb-2'>{$tableInfoRow['name']}：</label>";
                
                // 获取表单中已有的值
                $value = isset($row[$tableInfoRow['name']]) ? $row[$tableInfoRow['name']] : '';

                // 日期字段单独处理
                if ($tableInfoRow['name'] === 'start_date' || $tableInfoRow['name'] === 'end_date') {
                    echo "<input type='date' name='{$tableInfoRow['name']}' value='". htmlspecialchars($value, ENT_QUOTES) . "' class='border border-gray-300 p-2 w-full'>";
                } 
                // 对分会列表和组委会信息处理为逗号分隔的文本输入
                elseif ($tableInfoRow['name'] === 'sessions' || $tableInfoRow['name'] === 'committee_members') {
                    echo "<input type='text' name='{$tableInfoRow['name']}' value='". htmlspecialchars($value, ENT_QUOTES) . "' class='border border-gray-300 p-2 w-full' placeholder='用逗号分隔的列表'>";
                }
                // 其他字段默认处理方式
                else {
                    echo "<input type='text' name='{$tableInfoRow['name']}' value='". htmlspecialchars($value, ENT_QUOTES) . "' class='border border-gray-300 p-2 w-full'>";
                }
            }
        }
        ?>
        <button type="submit" name="update_conference" class="bg-blue-500 text-white p-2 mt-2">更新大会</button>
    </form>

    <?php

    // 引入 utils.php 文件
    require_once '../utils/conference_utils.php';
    
    // 处理表单提交逻辑
    if (isset($_POST['update_conference'])) {
        $updateQuery = "UPDATE conference_info SET ";
        $first = true;

        // 遍历表结构生成更新语句
        $queryResult = $db->query("PRAGMA table_info(conference_info)");
        while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] !== 'id') {
                if (!$first) {
                    $updateQuery .= ", ";
                }
                $fieldValue = $_POST[$row['name']];

                // 对 sessions 和 committee_members 进行格式化处理
                if ($row['name'] === 'sessions') {
                    $fieldValue = formatSessions($fieldValue);  // 使用你在 utils.php 中的函数
                } elseif ($row['name'] === 'committee_members') {
                    $fieldValue = formatCommitteeMembers($fieldValue);  // 使用你在 utils.php 中的函数
                }

                $updateQuery .= $row['name'] . "='" . SQLite3::escapeString($fieldValue) . "'";
                $first = false;
            }
        }
        $updateQuery .= " WHERE id=" . (int)$_POST['id'];

        // 执行更新语句
        $db->exec($updateQuery);

        // 跳转回大会列表页面
        header("Location: list_conference.php");
        exit();
    }

    // 关闭数据库连接
    $db->close();
    ?>

    </div>
</body>

</html>

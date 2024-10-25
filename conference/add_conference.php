<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 添加大会信息</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">添加大会信息</h1>

        <?php
        // 引入 utils.php 文件
        require_once '../utils/conference_utils.php';

        // 获取当前日期，格式为 'Y-m-d'
        $currentDate = date('Y-m-d');

        // 处理表单提交逻辑
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 连接数据库
            $db = new SQLite3('../db/conference_main.db');

            // 获取表单数据并格式化
            $conference_name = trim($_POST['new_conference_name']);
            $start_date = $_POST['new_start_date'];
            $end_date = $_POST['new_end_date'];
            $location = trim($_POST['new_location']);
            $description = trim($_POST['new_description']);
            $website_url = trim($_POST['new_website_url']);
            $sessions = formatSessions($_POST['new_sessions']);
            $committee_members = formatCommitteeMembers($_POST['new_committee_members']);

            // 检查 conference_name、start_date、end_date 和 location 是否为必填字段
            if (empty($conference_name) || empty($start_date) || empty($end_date) || empty($location)) {
                echo "<p class='text-red-500'>请填写所有必填字段。</p>";
            } else {
                // 检查是否有重叠的大会
                $overlapQuery = "
                    SELECT * FROM conference_info
                    WHERE conference_name = :conference_name
                    AND (start_date <= :end_date AND end_date >= :start_date)
                ";
                $stmt = $db->prepare($overlapQuery);
                $stmt->bindValue(':conference_name', $conference_name, SQLITE3_TEXT);
                $stmt->bindValue(':start_date', $start_date, SQLITE3_TEXT);
                $stmt->bindValue(':end_date', $end_date, SQLITE3_TEXT);
                $result = $stmt->execute();

                if ($result->fetchArray(SQLITE3_ASSOC)) {
                    echo "<p class='text-red-500'>已存在重叠的大会信息，请确认。</p>";
                } else {
                    // 准备插入SQL语句
                    $query = "
                        INSERT INTO conference_info (
                            conference_name, start_date, end_date, location, description, website_url, sessions, committee_members
                        ) VALUES (
                            '$conference_name', '$start_date', '$end_date', '$location', '$description', '$website_url', '$sessions', '$committee_members'
                        )
                    ";

                    // 执行SQL语句
                    $db->exec($query);

                    // 跳转到大会信息列表页面
                    header("Location: list_conference.php");
                    exit();
                }
            }

            // 关闭数据库连接
            $db->close();
        }
        ?>

        <form action="" method="post">
            <?php
            // 连接数据库获取表结构
            $db = new SQLite3('../db/conference_main.db');
            $query = "PRAGMA table_info(conference_info)";
            $result = $db->query($query);

            // 动态生成表单
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if ($row['name'] !== 'id') {
                    echo "<label class='block font-bold mb-2'>{$row['name']}：</label>";

                    // 使用日期选择器为 start_date 和 end_date 字段，并设置默认值
                    if ($row['name'] === 'start_date' || $row['name'] === 'end_date') {
                        echo "<input type='date' name='new_{$row['name']}' value='$currentDate' class='border border-gray-300 p-2 w-full' required>";
                    }
                    // 对分会列表和组委会信息处理为逗号分隔的文本输入
                    elseif ($row['name'] === 'sessions' || $row['name'] === 'committee_members') {
                        echo "<input type='text' name='new_{$row['name']}' class='border border-gray-300 p-2 w-full' placeholder='用逗号分隔的列表'>";
                    }
                    // 其他字段的默认处理方式，标记必填字段
                    else {
                        if (in_array($row['name'], ['conference_name', 'location', 'description'])) {
                            echo "<input type='text' name='new_{$row['name']}' class='border border-gray-300 p-2 w-full' required>";
                        } else {
                            echo "<input type='text' name='new_{$row['name']}' class='border border-gray-300 p-2 w-full'>";
                        }
                    }
                }
            }

            // 关闭数据库连接
            $db->close();
            ?>
            <button type="submit" class="bg-blue-500 text-white p-2 mt-2">添加大会信息</button>
        </form>
    </div>
</body>

</html>

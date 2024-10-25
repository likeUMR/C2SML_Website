<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 排序字段</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">调整字段顺序</h1>
        <form action="process_sort_fields.php" method="post">
            <?php
            // 连接数据库获取表结构
            $db = new SQLite3('../db/users.db');
            $query = "PRAGMA table_info(users)";
            $result = $db->query($query);
            $fields = array();
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                if ($row['name']!== 'id') {
                    $fields[] = $row['name'];
                }
            }
            $db->close();
           ?>
            <label class="block font-bold mb-2">选择要移到首位的字段：</label>
            <select name="field_to_move_first">
                <?php foreach ($fields as $field):?>
                    <option value="<?php echo $field;?>"><?php echo $field;?></option>
                <?php endforeach;?>
            </select>
            <button type="submit" class="bg-blue-500 text-white p-2 mt-2">调整顺序</button>
        </form>
    </div>
</body>

</html>
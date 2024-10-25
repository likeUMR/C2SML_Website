<?php
// 连接数据库
$db = new SQLite3('../db/users.db');

$fieldToMoveFirst = $_POST['field_to_move_first'];

// 获取当前表结构
$query = "PRAGMA table_info(users)";
$result = $db->query($query);
$columns = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $columns[] = $row['name'];
}

// 找到要移到首位的字段的当前位置
$currentIndex = array_search($fieldToMoveFirst, $columns);

// 如果字段不在第一位，则进行调整
if ($currentIndex > 0) {
    $tempColumn = $columns[$currentIndex];
    array_splice($columns, $currentIndex, 1);
    array_unshift($columns, $tempColumn);
}

// 构建新的表结构 SQL 语句
$alterSql = "ALTER TABLE users";
$first = true;
foreach ($columns as $column) {
    if ($column!== 'id') {
        if (!$first) {
            $alterSql.= ", ";
        }
        $alterSql.= "RENAME COLUMN ". ($first? $column : $columns[$first? 1 : 0]). " TO temp_". $column;
        $first = false;
    }
}
$db->exec($alterSql);

$first = true;
foreach ($columns as $column) {
    if ($column!== 'id') {
        if (!$first) {
            $alterSql = "ALTER TABLE users RENAME COLUMN temp_". $column. " TO ". $column;
            $db->exec($alterSql);
        }
        $first = false;
    }
}

// 查询更新后的表数据
$query = "SELECT * FROM users";
$result = $db->query($query);

// 获取列信息
$columnInfo = $result->numColumns();

// 开始输出 HTML 内容
echo "<table class='w-full border-collapse border border-gray-300'>";
echo "<tr>";
for ($i = 0; $i < $columnInfo; $i++) {
    $columnName = $result->columnName($i);
    echo "<th class='px-4 py-2 bg-gray-200'>{$columnName}</th>";
}
echo "</tr>";

// 遍历结果集并输出到表格中
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    foreach ($row as $value) {
        echo "<td class='px-4 py-2'>{$value}</td>";
    }
    // 添加修改按钮
    echo "<td><a href='edit_user.php?id={$row['id']}' class='text-blue-500'>修改</a></td>";
    // 添加删除按钮
    echo "<td><a href='delete_user.php?id={$row['id']}' class='text-red-500'>删除</a></td>";
    echo "</tr>";
}

echo "</table>";
// 关闭数据库连接
$db->close();
?>
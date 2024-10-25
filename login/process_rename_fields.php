<?php
// 连接数据库
$db = new SQLite3('../db/users.db');

$queryResult = $db->query("PRAGMA table_info(users)");
while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
    if ($row['name']!== 'id') {
        $newName = $_POST["new_name_{$row['name']}"];
        if (!empty($newName)) {
            $query = "ALTER TABLE users RENAME COLUMN {$row['name']} TO {$newName}";
            $db->exec($query);
        }
    }
}

$db->close();
header("Location: list_user.php");
?>
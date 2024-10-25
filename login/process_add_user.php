<?php
// 连接数据库
$db = new SQLite3('../db/users.db');

$query = "INSERT INTO users (";
$values = "VALUES (";
$first = true;
$queryResult = $db->query("PRAGMA table_info(users)");
while ($row = $queryResult->fetchArray(SQLITE3_ASSOC)) {
    if ($row['name']!== 'id') {
        if (!$first) {
            $query.= ", ";
            $values.= ", ";
        }
        $query.= $row['name'];
        $values.= "'". $_POST["new_{$row['name']}"]. "'";
        $first = false;
    }
}
$query.= ") ". $values. ")";
$db->exec($query);

$db->close();
header("Location: list_user.php");
?>
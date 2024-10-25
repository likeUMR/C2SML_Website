<?php
// 连接数据库
$db = new SQLite3('../db/users.db');

$newFieldName = $_POST['new_field_name'];
$defaultValue = $_POST['default_value'];

$query = "ALTER TABLE users ADD COLUMN {$newFieldName} TEXT DEFAULT '{$defaultValue}'";
$db->exec($query);

$db->close();
header("Location: list_user.php");
?>
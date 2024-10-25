<?php
session_start();

// 连接数据库
$db = new SQLite3('../db/users.db');

$username = $_POST['username'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $db->query($query);

if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $_SESSION['id'] = $row['id'];
    header("Location: list_user.php");
} else {
    echo "用户名或密码错误。";
}

$db->close();
?>
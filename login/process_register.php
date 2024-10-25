<?php
session_start();

// 连接数据库
$db = new SQLite3('../db/users.db');

// 获取用户提交的数据
$data = [];
foreach ($_POST as $key => $value) {
    $data[$key] = $value;
}

// 处理密码哈希
$data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
unset($data['password']);

// 构建插入语句
$columns = implode(',', array_keys($data));
$placeholders = implode(',', array_fill(0, count($data), '?'));
$stmt = $db->prepare("INSERT INTO users ($columns) VALUES ($placeholders)");

// 绑定参数并执行插入
$values = array_values($data);
foreach ($values as $index => $value) {
    $stmt->bindValue($index + 1, $value);
}
$stmt->execute();

header("Location: login.php");
exit();
?>
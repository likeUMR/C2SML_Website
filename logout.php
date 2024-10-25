<?php
// 启动会话
session_start();

// 清除所有会话变量
$_SESSION = [];

// 如果需要销毁会话，可以使用 session_destroy()
session_destroy();

// 重定向到登录页面
header("Location:" . dirname($_SERVER['PHP_SELF']) . "/login/login.php");
exit(); // 确保在重定向后停止执行当前脚本
?>



session_start();

($_SESSION['user_id']) || ($_SESSION['role'] !== ROLE_SUPER_ADMIN && $_SESSION['role'] !== ROLE_ADMIN)
$_SESSION['user_id'] = $user['id'];

session_destroy();


<head>
    <title>会议登记系统 - 登录</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>


https://s.csiam.org.cn/default.php
<!DOCTYPE html>
<html>

<head>
    <title>会议登记系统 - 注册</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script>
        function toggleTitleField() {
            const roleTeacher = document.getElementById('role_teacher');
            const titleDiv = document.getElementById('titleDiv');
            titleDiv.style.display = roleTeacher.checked? 'block' : 'none';
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">会议登记系统注册</h1>
        <?php
        session_start();

        // 连接数据库
        $db = new SQLite3('../db/users.db');

        // 获取最后一条记录的 id
        $queryLastId = "SELECT id FROM users ORDER BY id DESC LIMIT 1";
        $resultLastId = $db->query($queryLastId);
        $lastId = 0;
        if ($row = $resultLastId->fetchArray(SQLITE3_ASSOC)) {
            $lastId = $row['id'];
        }
        $newId = $lastId + 1;

        // 生成随机验证码
        function generateVerificationCode()
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $codeLength = 6;
            $verificationCode = '';
            for ($i = 0; $i < $codeLength; $i++) {
                $verificationCode.= $characters[rand(0, strlen($characters) - 1)];
            }
            return $verificationCode;
        }

        // 发送邮件
        function sendVerificationEmail($email, $verificationCode)
        {
            $subject = '会议登记系统注册验证码';
            $message = "您的验证码是：$verificationCode。请在 15 分钟内使用该验证码完成注册。";
            $headers = "From: your_email@example.com". "\r\n".
                "Reply-To: your_email@example.com". "\r\n".
                "X-Mailer: PHP/". phpversion();
            mail($email, $subject, $message, $headers);
        }

        // 存储验证码到数据库临时表
        function storeVerificationCode($email, $verificationCode)
        {
            $timestamp = time();
            $db->exec("CREATE TABLE IF NOT EXISTS verification_codes (email TEXT, code TEXT, timestamp INTEGER)");
            $stmt = $db->prepare("INSERT INTO verification_codes (email, code, timestamp) VALUES (?,?,?)");
            $stmt->bindValue(1, $email);
            $stmt->bindValue(2, $verificationCode);
            $stmt->bindValue(3, $timestamp);
            $stmt->execute();
        }

        // 验证验证码
        function verifyVerificationCode($email, $code)
        {
            $query = "SELECT * FROM verification_codes WHERE email =? AND code =?";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $email);
            $stmt->bindValue(2, $code);
            $result = $stmt->execute();
            if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $timestamp = $row['timestamp'];
                $currentTime = time();
                if ($currentTime - $timestamp <= 900) { // 15 分钟 = 900 秒
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 获取用户提交的数据
            $username = $_POST['username'];
            $school = $_POST['school'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $title = isset($_POST['title'])? $_POST['title'] : '';
            $department = isset($_POST['department'])? $_POST['department'] : '';
            $phone = isset($_POST['phone'])? $_POST['phone'] : '';
            $tutor = isset($_POST['tutor'])? $_POST['tutor'] : '';
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $verificationCode = $_POST['verification_code'];

            // 验证密码和确认密码是否一致
            if ($password!== $confirm_password) {
                $error = "两次输入的密码不一致。";
            } else {
                // 确保 role 已被选择
                if (!isset($role) || empty($role)) {
                    $error = "请选择角色。";
                } else {
                    // 验证邮箱验证码
                    if (!verifyVerificationCode($email, $verificationCode)) {
                        $error = "验证码错误或已过期。";
                    } else {
                        // 处理密码哈希
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        // 构建插入语句
                        $stmt = $db->prepare("INSERT INTO users (username, school, email, role, title, department, phone, tutor, password_hash) VALUES (?,?,?,?,?,?,?,?,?)");
                        $stmt->bindValue(1, $username);
                        $stmt->bindValue(2, $school);
                        $stmt->bindValue(3, $email);
                        $stmt->bindValue(4, $role);
                        $stmt->bindValue(5, $title);
                        $stmt->bindValue(6, $department);
                        $stmt->bindValue(7, $phone);
                        $stmt->bindValue(8, $tutor);
                        $stmt->bindValue(9, $password_hash);
                        $stmt->execute();

                        header("Location: login.php");
                        exit();
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['send_verification_code'])) {
            $email = $_GET['email'];
            $verificationCode = generateVerificationCode();
            sendVerificationEmail($email, $verificationCode);
            storeVerificationCode($email, $verificationCode);
            echo "验证码已发送至您的邮箱，请查收。";
            exit();
        }
       ?>
        <?php if ($error):?>
            <p class="text-red-500 mb-4"><?= $error?></p>
        <?php endif;?>
        <form action="" method="post">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="username">
                    username*
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="username" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="school">
                    school*
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="school" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="email">
                    email*
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="email" required>
                <button type="button" onclick="sendVerificationCode()">获取验证码</button>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="verification_code">
                    验证码
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="verification_code" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="role">
                    role*
                </label>
                <div>
                    <input type="radio" id="role_teacher" name="role" value="教师" onchange="toggleTitleField()" required>
                    <label for="role_teacher">教师</label>
                    <input type="radio" id="role_student" name="role" value="学生" onchange="toggleTitleField()" required>
                    <label for="role_student">学生</label>
                    <input type="radio" id="role_other" name="role" value="其它" onchange="toggleTitleField()" required>
                    <label for="role_other">其它</label>
                </div>
            </div>
            <div class="mb-4" id="titleDiv" style="display: none;">
                <label class="block text-gray-700 font-bold mb-2" for="title">
                    title (选填)
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="title">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="department">
                    department (选填)
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="department">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="phone">
                    phone (选填)
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="phone">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="tutor">
                    tutor (选填)
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" name="tutor">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="password">
                    password:
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="password" name="password" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="confirm_password">
                    confirm password：
                </label>
                <input class="appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="password" name="confirm_password" required>
            </div>
            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                注册
            </button>
        </form>
    </div>
    <script>
        function sendVerificationCode() {
            const emailInput = document.querySelector('input[name="email"]');
            const email = emailInput.value;
            if (email) {
                window.location.href = `?send_verification_code=true&email=${email}`;
            } else {
                alert('请先输入邮箱地址。');
            }
        }
    </script>
</body>

</html>
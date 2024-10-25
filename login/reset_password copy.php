<?php
require_once '../utils/verification_utils.php';  // 引入 verification_utils.php
session_start();
$db = new SQLite3('../db/users.db');
$error = '';  // 初始化 $error 变量

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取用户提交的数据
    $username = $_POST['username'];
    $email = $_POST['email'];
    $verificationCode = $_POST['verification_code'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];

    // 验证新密码和确认新密码是否一致
    if ($newPassword !== $confirmNewPassword) {
        $error = "两次输入的新密码不一致。";
    } else {
        // 验证邮箱验证码
        if (!verifyVerificationCode($email, $verificationCode)) {
            $error = "验证码错误或已过期。";
        } else {
            // 根据用户名和邮箱查找用户记录
            $query = "SELECT * FROM users WHERE username = ? AND email = ?";
            $stmt = $db->prepare($query);
            $stmt->bindValue(1, $username);
            $stmt->bindValue(2, $email);
            $result = $stmt->execute();
            if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                // 处理密码哈希
                $password_hash = password_hash($newPassword, PASSWORD_DEFAULT);

                // 更新用户密码
                $updateQuery = "UPDATE users SET password_hash = ? WHERE username = ? AND email = ?";
                $updateStmt = $db->prepare($updateQuery);
                $updateStmt->bindValue(1, $password_hash);
                $updateStmt->bindValue(2, $username);
                $updateStmt->bindValue(3, $email);
                $updateStmt->execute();

                // 弹窗提示密码重置成功
                echo "<script>
                    alert('密码重置成功！');
                    window.location.href='login.php';
                </script>";
                exit();
            } else {
                $error = "用户名和邮箱不匹配。";
            }
        }
    }
}

// 处理发送验证码的请求
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['send_verification_code'])) {
    ob_clean();
    header('Content-Type: application/json');

    $email = $_GET['email'];
    // 检查邮箱是否存在于数据库中
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->bindValue(1, $email);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['count'] == 0) {
        echo json_encode(['status' => 'error', 'message' => '此邮箱未注册，请检查邮箱地址。']);
        exit();
    }

    // 生成并发送验证码
    $verificationCode = generateVerificationCode();
    sendVerificationEmail($email, $verificationCode);
    storeVerificationCode($email, $verificationCode);
    echo json_encode(['status' => 'success', 'message' => '验证码已发送，请查收邮箱。']);
    exit();
}

?>

<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会议登记系统 - 重置密码</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <style>
        .progress-bar {
            transition: width 0.3s ease-in-out;
        }
    </style>
    <script>
        let currentStep = 1;

        function showStep(step) {
            document.querySelectorAll('.step').forEach(stepDiv => {
                stepDiv.style.display = 'none';
            });

            document.getElementById('step' + step).style.display = 'block';

            document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = step === 2 ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = step === 2 ? 'inline-block' : 'none';

            const progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = `${(step / 2) * 100}%`;
        }

        function nextStep() {
            if (!validateStep(currentStep)) return;
            currentStep++;
            showStep(currentStep);
        }

        function prevStep() {
            currentStep--;
            showStep(currentStep);
        }

        function validateStep(step) {
            const inputs = document.querySelectorAll(`#step${step} input[required]`);
            for (let input of inputs) {
                if (input.value.trim() === '') {
                    alert('请填写所有必填项');
                    return false;
                }
            }
            return true;
        }

        function sendVerificationCode() {
            const emailInput = document.querySelector('input[name="email"]');
            const email = emailInput.value;
            const sendBtn = document.querySelector('#sendCodeBtn');
            if (email && !sendBtn.disabled) {
                fetch(`?send_verification_code=true&email=${email}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        startCountdown(sendBtn);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('发送验证码时出错:', error);
                    alert('发送验证码时出错，请重试。');
                });
            } else if (sendBtn.disabled) {
                alert('请等待倒计时结束后再次获取验证码。');
            } else {
                alert('请先输入邮箱地址。');
            }
        }

        function startCountdown(button) {
            let countdown = 60;
            button.disabled = true;
            button.textContent = `${countdown}s后重新获取`;
            button.style.backgroundColor = '#D1D5DB';

            const interval = setInterval(() => {
                countdown--;
                button.textContent = `${countdown}s后重新获取`;

                if (countdown === 0) {
                    clearInterval(interval);
                    button.disabled = false;
                    button.textContent = '获取验证码';
                    button.style.backgroundColor = '#3B82F6';
                }
            }, 1000);
        }

        window.onload = function() {
            showStep(currentStep);
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="w-full bg-gray-200 h-1">
            <div class="bg-blue-600 h-1 progress-bar" style="width: 50%"></div>
        </div>

        <div class="p-10 space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">会议登记系统重置密码</h2>
                <p class="mt-2 text-sm text-gray-600">重置您的账户密码</p>
            </div>

            <?php if (!empty($error)): ?>
                <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="" method="POST">
                <!-- Step 1: 用户信息和验证码 -->
                <div id="step1" class="step">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="username">用户名*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="text" name="username" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="email">邮箱*</label>
                        <div class="flex items-center space-x-2">
                            <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                type="email" name="email" required>
                            <button type="button" id="sendCodeBtn" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 whitespace-nowrap"
                                onclick="sendVerificationCode()">获取验证码</button>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="verification_code">验证码*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="text" name="verification_code" required>
                    </div>
                </div>

                <!-- Step 2: 新密码 -->
                <div id="step2" class="step" style="display: none;">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="new_password">新密码*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="password" name="new_password" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="confirm_new_password">确认新密码*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="password" name="confirm_new_password" required>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between">
                    <button type="button" id="prevBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            onclick="prevStep()" style="display: none;">上一步</button>
                    <button type="button" id="nextBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            onclick="nextStep()">下一步</button>
                    <button type="submit" id="submitBtn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            style="display: none;">重置密码</button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="login.php" class="text-blue-500 hover:text-blue-700">返回登录</a>
            </div>
        </div>
    </div>
</body>

</html>
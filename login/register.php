<!DOCTYPE html>
<?php
        require_once '../utils/verification_utils.php';  // 引入 verification_utils.php
        session_start();
        $db = new SQLite3('../db/users.db');
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 从表单获取数据
            $username = $_POST['username'];
            $school = $_POST['school'];
            $email = $_POST['email'];
            $role = $_POST['role'];
            $title = isset($_POST['title']) ? $_POST['title'] : '';
            $other_type = isset($_POST['other_type']) ? $_POST['other_type'] : '';
            $department = isset($_POST['department']) ? $_POST['department'] : '';
            $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
            $tutor = isset($_POST['tutor']) ? $_POST['tutor'] : '';
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $verificationCode = $_POST['verification_code'];

             // 验证密码和确认密码是否一致
             if ($password !== $confirm_password) {
                $error = "两次输入的密码不一致。";
            } else {
                // 确保 role 已被选择
                if (!isset($role) || empty($role)) {
                    $error = "请选择身份。";
                } else {
                    // 检查姓名和学校组合是否已经存在
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND school = ?");
                    $stmt->bindValue(1, $username);
                    $stmt->bindValue(2, $school);
                    $result = $stmt->execute(); 
                    $row = $result->fetchArray(SQLITE3_ASSOC);

                    if ($row['count'] > 0) {
                        $error = "该姓名和学校组合已存在，请更换。";
                    } else {
                        // 验证邮箱验证码
                        if (!verifyVerificationCode($email, $verificationCode)) {
                            $error = "验证码错误或已过期。";
                        } else {
                            // 处理密码哈希
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);

                            // 构建插入语句
                            $stmt = $db->prepare("INSERT INTO users (username, school, email, role, title, other_type, department, phone, tutor, password_hash) VALUES (?,?,?,?,?,?,?,?,?,?)");
                            $stmt->bindValue(1, $username);
                            $stmt->bindValue(2, $school);
                            $stmt->bindValue(3, $email);
                            $stmt->bindValue(4, $role);
                            $stmt->bindValue(5, $title);
                            $stmt->bindValue(6, $other_type);
                            $stmt->bindValue(7, $department);
                            $stmt->bindValue(8, $phone);
                            $stmt->bindValue(9, $tutor);
                            $stmt->bindValue(10, $password_hash);
                            $stmt->execute();

                            // 弹窗提示注册成功
                            echo "<script>alert('注册成功！');window.location.href='login.php';</script>";
                            // echo "<script>
                            //     alert('注册成功！');
                                
                            //     // 等待 DOM 加载完成后执行
                            //     document.addEventListener('DOMContentLoaded', function() {
                            //         // 创建一个函数来触发彩带效果
                            //         function launchConfetti() {
                            //             confetti({
                            //                 particleCount: 200,
                            //                 startVelocity: 30,
                            //                 spread: 360,
                            //                 ticks: 60,
                            //                 origin: {
                            //                     x: Math.random(),
                            //                     y: Math.random() - 0.2
                            //                 },
                            //                 colors: ['#bb0000', '#ffffff', '#00bb00', '#0000bb', '#ffbb00']
                            //             });
                            //         }

                            //         // 立即触发一次彩带效果
                            //         launchConfetti();

                            //         // 每200毫秒触发一次，总共触发5次
                            //         for (let i = 1; i < 5; i++) {
                            //             setTimeout(launchConfetti, i * 200);
                            //         }

                            //         // 2秒后跳转到登录页面
                            //         setTimeout(function() {
                            //             window.location.href = 'login.php';
                            //         }, 1000);
                            //     });
                            // </script>";
                            exit();
                        }
                    }
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['send_verification_code'])) {
            // 确保在此处理之前没有任何输出
            ob_clean(); // 清除之前的任何输出缓冲
            header('Content-Type: application/json'); // 设置响应类型为JSON

            $email = $_GET['email'];
            // 检查邮箱是否已注册
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
            $stmt->bindValue(1, $email);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);

            if ($row['count'] > 0) {
                echo json_encode(['status' => 'error', 'message' => '此邮箱已被注册，请使用其他邮箱。']);
                exit();
            }

            // 生成并发送验证码
            $verificationCode = generateVerificationCode();
            sendVerificationEmail($email, $verificationCode);  // 使用 verification_utils.php 中的函数
            storeVerificationCode($email, $verificationCode);  // 使用 verification_utils.php 中的函数
            echo json_encode(['status' => 'success', 'message' => '验证码已发送，请查收邮箱。']);
            exit();
        }
?>



<html lang="zh-CN">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会议登记系统 - 注册</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
    <style>
        .progress-bar {
            transition: width 0.3s ease-in-out;
        }
    </style>
    <script>
        let currentStep = 1;

        function showStep(step) {
            // 隐藏所有步骤
            document.querySelectorAll('.step').forEach(stepDiv => {
                stepDiv.style.display = 'none';
            });

            // 显示当前步骤
            document.getElementById('step' + step).style.display = 'block';

            // 更新按钮状态
            document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
            document.getElementById('nextBtn').style.display = step === 3 ? 'none' : 'inline-block';
            document.getElementById('submitBtn').style.display = step === 3 ? 'inline-block' : 'none';

            // 更新进度条
            const progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = `${(step / 3) * 100}%`;
        }

        function nextStep() {
            // 验证当前步骤的必填项
            if (!validateStep(currentStep)) return;

            // 如果是第二步，校验验证码
            if (currentStep === 2) {
                const email = document.querySelector('input[name="email"]').value;
                const verificationCode = document.querySelector('input[name="verification_code"]').value;

                // 使用 fetch 发送 AJAX 请求到服务器
                fetch('verify_code.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        verification_code: verificationCode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const password = document.querySelector('input[name="password"]').value;
                        const confirmPassword = document.querySelector('input[name="confirm_password"]').value;

                        if (password === confirmPassword) {
                            currentStep++;
                            showStep(currentStep);  // 显示下一步的页面
                        } else {
                            alert('两次密码输入不一致，请重新输入。');
                        }
                        // currentStep++;
                        // showStep(currentStep);
                    } else {
                        alert('验证码错误或已过期，请重新输入。');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('请求失败，请稍后再试。');
                });
               

            } else {
                // 如果不是第二步，直接跳转到下一步
                currentStep++;
                showStep(currentStep);  // 显示下一步的页面

            }
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

        function toggleFields() {
            const roleTeacher = document.getElementById('role_teacher');
            const roleOther = document.getElementById('role_other');
            const titleDiv = document.getElementById('titleDiv');
            const titleInput = document.getElementById('titleInput');
            const otherTypeDiv = document.getElementById('otherTypeDiv');
            const otherTypeInput = document.getElementById('otherTypeInput');

            if (roleTeacher.checked) {
                titleDiv.style.display = 'block';
                titleInput.required = true;
                otherTypeDiv.style.display = 'none';
                otherTypeInput.required = false;
                otherTypeInput.value = '';
            } else if (roleOther.checked) {
                titleDiv.style.display = 'none';
                titleInput.required = false;
                titleInput.value = '';
                otherTypeDiv.style.display = 'block';
                otherTypeInput.required = true;
            } else {
                titleDiv.style.display = 'none';
                titleInput.required = false;
                titleInput.value = '';
                otherTypeDiv.style.display = 'none';
                otherTypeInput.required = false;
                otherTypeInput.value = '';
            }
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
                        startCountdown(sendBtn);  // 开始倒计时
                    } else {
                        alert(data.message); // 显示错误信息
                    }
                })
                .catch(error => {
                    console.error('发送验证码时出错:', error);
                    alert('发送验证码时出错，请重试。');
                });
            } else if (sendBtn.disabled) {
                alert('该邮箱已被注册，请使用其他邮箱。');
            } else {
                alert('请先输入邮箱地址。');
            }
        }

        function startCountdown(button) {
            let countdown = 30;  // 倒计时30秒
            button.disabled = true;  // 禁用按钮
            button.textContent = `${countdown}s后重新获取`;  // 显示倒计时
            // 改为gray-300
            button.style.backgroundColor = '#D1D5DB'; // 使用 Tailwind 的 gray-300 颜色

            const interval = setInterval(() => {
                countdown--;
                button.textContent = `${countdown}s后重新获取`;

                if (countdown === 0) {
                    clearInterval(interval);  // 停止倒计时
                    button.disabled = false;  // 重新启用按钮
                    button.textContent = '获取验证码';  // 恢复按钮文本
                    button.style.backgroundColor = '#3B82F6'; // 使用 Tailwind 的 blue-500 颜色
                }
            }, 1000);  // 每秒更新一次
        }

        window.onload = function() {
            showStep(currentStep);  // 初始显示第一步
        }

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.querySelector('input[name="email"]');
            const sendCodeBtn = document.querySelector('#sendCodeBtn');
            const emailValidationMessageDiv = document.createElement('div');
            const emailValidationMessage = document.createElement('p');
            
            // 设置提示信息样式
            emailValidationMessageDiv.appendChild(emailValidationMessage);
            emailValidationMessage.className = 'text-sm mt-1';
            
            // 将提示信息插入到父级 .mb-4 的末尾
            const parentDiv = emailInput.closest('.mb-4');
            parentDiv.appendChild(emailValidationMessageDiv);

            // 实时监听邮箱输入框
            emailInput.addEventListener('blur', function() {
                const email = emailInput.value.trim();
                if (email) {
                    // 发送 AJAX 请求到服务器进行校验
                    fetch('check_email_registed.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            email: email
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            emailValidationMessage.textContent = data.message;
                            emailValidationMessage.style.color = 'green';
                            sendCodeBtn.disabled = false;
                            sendCodeBtn.style.backgroundColor = '#3B82F6'; // 蓝色
                        } else {
                            emailValidationMessage.textContent = data.message;
                            emailValidationMessage.style.color = 'red';
                            sendCodeBtn.disabled = true;
                            sendCodeBtn.style.backgroundColor = '#D1D5DB'; // 灰色
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        emailValidationMessage.textContent = '校验失败，请稍后再试';
                        emailValidationMessage.style.color = 'red';
                    });
                } else {
                    emailValidationMessage.textContent = '';
                    sendCodeBtn.disabled = false;
                    sendCodeBtn.style.backgroundColor = '#3B82F6'; // 蓝色
                }
            });

            // 实时监听验证码输入框
            const verificationInput = document.querySelector('input[name="verification_code"]');
            const verificationMessage = document.createElement('p');
            verificationMessage.className = 'text-sm mt-1';
            verificationInput.parentNode.appendChild(verificationMessage);

            verificationInput.addEventListener('input', function() {
                const email = document.querySelector('input[name="email"]').value;
                const verificationCode = verificationInput.value;

                // 当验证码长度为6时，进行校验
                if (verificationCode.length === 6) {
                    // 使用 fetch 发送 AJAX 请求到服务器
                    fetch('verify_code.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email,
                            verification_code: verificationCode
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            verificationMessage.textContent = '验证码正确';
                            verificationMessage.style.color = 'green';
                        } else {
                            verificationMessage.textContent = '验证码错误或已过期';
                            verificationMessage.style.color = 'red';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        verificationMessage.textContent = '请求失败，请稍后再试。';
                        verificationMessage.style.color = 'red';
                    });
                } else {
                    verificationMessage.textContent = '';  // 清除提示信息
                }
            });

            // 实时监听确认密码输入框
            const passwordInput = document.querySelector('input[name="password"]');
            const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
            const passwordMessage = document.createElement('p');
            passwordMessage.className = 'text-sm mt-1';
            confirmPasswordInput.parentNode.appendChild(passwordMessage);

            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                // 当确认密码输入框不为空时，进行校验
                if (confirmPassword.length > 0) {
                    if (password === confirmPassword) {
                        passwordMessage.textContent = '密码一致';
                        passwordMessage.style.color = 'green';
                    } else {
                        passwordMessage.textContent = '两次输入的密码不一致';
                        passwordMessage.style.color = 'red';
                    }
                } else {
                    passwordMessage.textContent = '';  // 清除提示信息
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.querySelector('input[name="username"]');
            const schoolInput = document.querySelector('input[name="school"]');
            const validationMessage = document.createElement('p');
            validationMessage.className = 'text-sm mt-1';
            schoolInput.parentNode.appendChild(validationMessage);  // 将校验结果显示在学校输入框下方

            // 当姓名或学校输入框失去焦点时，进行校验
            usernameInput.addEventListener('blur', checkUsernameSchool);
            schoolInput.addEventListener('blur', checkUsernameSchool);

            function checkUsernameSchool() {
                const username = usernameInput.value.trim();
                const school = schoolInput.value.trim();

                if (username && school) {
                    // 发送 AJAX 请求到服务器进行校验
                    fetch('check_username_school.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            username: username,
                            school: school
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            validationMessage.textContent = '该用户已被注册';
                            validationMessage.style.color = 'red';
                        } else if (data.error) {
                            validationMessage.textContent = '错误: ' + data.error;
                            validationMessage.style.color = 'red';
                        } else {
                            validationMessage.textContent = '有效用户';
                            validationMessage.style.color = 'green';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        validationMessage.textContent = '校验失败，请稍后再试';
                        validationMessage.style.color = 'red';
                    });
                } else {
                    validationMessage.textContent = '';  // 清除提示信息
                }

            }
        });
    </script>

</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- 进度条移到这里 -->
        <div class="w-full bg-gray-200 h-1">
            <div class="bg-blue-600 h-1 progress-bar" style="width: 33%"></div>
        </div>

        <div class="p-10 space-y-8">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">会议登记系统注册</h2>
                <p class="mt-2 text-sm text-gray-600">创建一个新的账号</p>
            </div>

            <?php if ($error): ?>
                <p class="text-red-500 mb-4"><?= $error ?></p>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="" method="POST">
                <!-- Step 1: 基本信息 -->
                <div id="step1" class="step">
                <!-- 姓名输入 -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="username">姓名*</label>
                    <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        type="text" name="username" required>
                </div>

                <!-- 学校输入 -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2" for="school">学校*</label>
                    <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        type="text" name="school" required>
                    <!-- 校验结果会动态添加到这里 -->
                </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="role">身份*</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" id="role_teacher" name="role" value="教师" onchange="toggleFields()" required>
                                <span class="ml-2 text-gray-700">教师</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" id="role_student" name="role" value="学生" onchange="toggleFields()" required>
                                <span class="ml-2 text-gray-700">学生</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" id="role_other" name="role" value="其它" onchange="toggleFields()" required>
                                <span class="ml-2 text-gray-700">其它</span>
                            </label>
                        </div>
                    </div>
                    <!-- 添加 title 字段 -->
                    <div class="mb-4" id="titleDiv" style="display: none;">
                        <label class="block text-gray-700 font-bold mb-2" for="title">职称*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               type="text" name="title" id="titleInput">
                    </div>
                    <!-- 添加 other_type 字段 -->
                    <div class="mb-4" id="otherTypeDiv" style="display: none;">
                        <label class="block text-gray-700 font-bold mb-2" for="other_type">填写身份*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               type="text" name="other_type" id="otherTypeInput">
                    </div>
                </div>

                <!-- Step 2: 必填信息 -->
                <div id="step2" class="step" style="display: none;">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="email">邮箱*</label>
                        <div class="flex items-center space-x-2">
                            <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   type="text" name="email" required>
                            <button type="button" id="sendCodeBtn"  class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 whitespace-nowrap"
                                   onclick="sendVerificationCode()">获取验证码</button>
                        </div>
                        <!-- 邮箱校验结果动态添加到这里 -->
                    </div>
                    <!-- 验证码输入 -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="verification_code">验证码*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="text" name="verification_code" required>
                        <!-- 验证码校验结果会动态添加到这里 -->
                    </div>

                    <!-- 密码输入 -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="password">密码*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="password" name="password" required>
                    </div>

                    <!-- 确认密码输入 -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="confirm_password">确认密码*</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            type="password" name="confirm_password" required>
                        <!-- 密码一致性校验结果会动态添加到这里 -->
                    </div>
                </div>

                <!-- Step 3: 身份信息 -->
                <div id="step3" class="step" style="display: none;">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="department">部门 (选填)</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               type="text" name="department">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="phone">手机号 (选填)</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               type="text" name="phone">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="tutor">导师 (选填)</label>
                        <input class="appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               type="text" name="tutor">
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between">
                    <button type="button" id="prevBtn" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            onclick="prevStep()">上一步</button>
                    <button type="button" id="nextBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            onclick="nextStep()">下一步</button>
                    <button type="submit" id="submitBtn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            style="display: none;">提交</button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <a href="login.php" class="text-blue-500 hover:text-blue-700">已有账号？登录</a>
            </div>
        </div>
    </div>
</body>

</html>
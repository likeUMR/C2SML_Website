<?php

require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

// 生成随机验证码
function generateVerificationCode() {
    $characters = '0123456789'; 
    $codeLength = 6;
    $verificationCode = '';
    for ($i = 0; $i < $codeLength; $i++) {
        $verificationCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $verificationCode;
}

// 发送验证码邮件
function sendVerificationEmail($email, $verificationCode) {
    global $mail;
    $mail = new PHPMailer();

    // 从 email_config.json 读取配置
    $config = json_decode(file_get_contents(__DIR__ . '/email_config.json'), true);
    $emailConfig = $config['email'];

    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = $emailConfig['smtp_server'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $emailConfig['smtp_port'];
    $mail->CharSet = 'UTF-8';
    $mail->FromName = '会议登记系统';
    $mail->Username = $emailConfig['sender'];
    $mail->Password = $emailConfig['password'];
    $mail->From = $emailConfig['sender'];
    $mail->isHTML(false);
    $mail->addAddress($email);
    $mail->Subject = '会议登记系统注册验证码';
    $mail->Body = "您的验证码是：$verificationCode 。请在 15 分钟内使用该验证码完成注册/邮箱更换。";
    $status = $mail->send();
    return $status;
}

// 存储验证码到数据库
function storeVerificationCode($email, $verificationCode) {
    $db = new SQLite3( __DIR__ . '/../db/verification_codes.db');
    $timestamp = time();
    // 创建表
    $db->exec("CREATE TABLE IF NOT EXISTS verification_codes (email TEXT, code TEXT, timestamp INTEGER)");
    // 插入验证码和时间戳
    $stmt = $db->prepare("INSERT INTO verification_codes (email, code, timestamp) VALUES (?,?,?)");
    $stmt->bindValue(1, $email);
    $stmt->bindValue(2, $verificationCode);
    $stmt->bindValue(3, $timestamp);
    $stmt->execute();
}

// 验证验证码是否正确和是否过期
function verifyVerificationCode($email, $code) {
    $db = new SQLite3( __DIR__ . '/../db/verification_codes.db');
    $query = "SELECT * FROM verification_codes WHERE email = ? AND code = ?";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $email);
    $stmt->bindValue(2, $code);
    $result = $stmt->execute();

    // 判断是否找到匹配的验证码
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $timestamp = $row['timestamp'];
        $currentTime = time();
        if ($currentTime - $timestamp <= 900) { // 验证码有效期 15 分钟
            return true;
        } else {
            return false; // 验证码过期
        }
    } else {
        return false; // 验证码不匹配
    }
}


function hasPermission($userId, $requiredPermissionLevel, $permissionDomain = null) {
    // 连接到用户数据库
    $db = new SQLite3( __DIR__ . '/../db/users.db'); // 确保数据库路径正确

    // 查询用户的权限和权限域
    $stmt = $db->prepare("SELECT permission, permission_domain FROM users WHERE id = ?");
    $stmt->bindValue(1, $userId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    
    // 获取用户的权限信息
    if ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $userPermission = $row['permission'];
        $userPermissionDomain = $row['permission_domain'];

        // 权限等级数组定义
        $permissionLevels = [
            '空' => 0,
            'reporter' => 1,
            'session_holder' => 2,
            'conference_holder' => 3,
            'administrator' => 4,
        ];

        // 判断是否为管理员
        if ($userPermission === 'administrator') {
            return true; // 管理员总是拥有权限
        }

        // 如果用户的权限为空或不在定义中，则没有权限
        if (empty($userPermission) || !array_key_exists($userPermission, $permissionLevels)) {
            return false;
        }

        // 校验用户的权限级别是否高于所需权限级别
        if ($permissionLevels[$userPermission] > $permissionLevels[$requiredPermissionLevel]) {
            // 如果有域名，检查域名是否匹配
            if ($permissionDomain !== null && $userPermissionDomain !== $permissionDomain) {
                return false; // 域名不匹配
            }
            return true; // 权限足够
        }
    }

    // 如果用户未找到或不符合条件，则没有权限
    return false;
}

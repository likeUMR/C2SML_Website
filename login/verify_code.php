<?php

// 获取 POST 请求中的 JSON 数据
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'];
$verificationCode = $data['verification_code'];

require_once '../utils/verification_utils.php';

// 验证验证码
if (verifyVerificationCode($email, $verificationCode)) {
    // 返回 JSON 响应，表示验证成功
    echo json_encode(['success' => true]);
} else {
    // 返回 JSON 响应，表示验证失败
    echo json_encode(['success' => false]);
}



<?php
$response = array('valid' => false, 'message' => '');

if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $db = new SQLite3('../db/users.db');
    // 检查邮箱是否已注册
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->bindValue(1, $email);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row['count'] > 0) {
        $response['valid'] = false;
        $response['message'] = '该邮箱已被注册';
    } else {
        $response['valid'] = true;
        $response['message'] = '有效邮箱';
    }
} else {
    $response['message'] = '请提供邮箱地址';
}

echo json_encode($response);
?>
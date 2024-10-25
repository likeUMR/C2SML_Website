<?php
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 检查 POST 数据是否存在
        if (!isset($_POST['username']) || !isset($_POST['school'])) {
            echo json_encode(['error' => '缺少必要的参数']);
            exit();
        }

        $db = new SQLite3('../db/users.db');
        $username = $_POST['username'];
        $school = $_POST['school'];

        // 查询数据库，检查用户名和学校组合是否存在
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE username = :username AND school = :school");
        if (!$stmt) {
            throw new Exception('SQL语句准备失败: ' . $db->lastErrorMsg());
        }
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':school', $school, SQLITE3_TEXT);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception('SQL查询执行失败: ' . $db->lastErrorMsg());
        }

        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row['count'] > 0) {
            // 如果组合已存在，返回错误信息
            echo json_encode(['exists' => true]);
        } else {
            // 否则返回组合可用
            echo json_encode(['exists' => false]);
        }
    } else {
        echo json_encode(['error' => '无效的请求方法']);
    }
} catch (Exception $e) {
    // 捕获异常并返回详细的错误信息
    echo json_encode(['error' => '服务器内部错误', 'message' => $e->getMessage()]);
}
?>

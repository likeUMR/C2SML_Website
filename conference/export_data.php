<?php
// 连接数据库
$db = new SQLite3('../db/conference_main.db');

// 查询数据库中的所有数据
$query = "SELECT * FROM conference_info";
$result = $db->query($query);

// 设置 CSV 文件头
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="conference_main.csv"');

// 打开输出流
$output = fopen('php://output', 'w');

// 获取列名并写入 CSV 文件头
$columnQuery = "PRAGMA table_info(conference_info)";
$columnResult = $db->query($columnQuery);
$columnNames = array();
while ($columnRow = $columnResult->fetchArray(SQLITE3_ASSOC)) {
    $columnNames[] = $columnRow['name'];
}
fputcsv($output, $columnNames, ',', '"');

// 遍历结果集并写入 CSV 文件
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    fputcsv($output, $row, ',', '"');
}

// 关闭数据库连接
$db->close();
?>
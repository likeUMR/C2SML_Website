<?php
if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
    $csvFile = $_FILES['csv_file']['tmp_name'];

    // 连接数据库
    $db = new SQLite3('../db/users.db');

    // 校验 CSV 文件格式
    if (validateCsv($csvFile)) {
        // 删除原表
        $db->exec('DROP TABLE IF EXISTS users');

        // 获取 CSV 文件内容并解析列名以确定表结构（忽略 ID 列）
        if (($handle = fopen($csvFile, "r"))!== FALSE) {
            $firstRow = fgetcsv($handle);
            $columnNames = array();
            $idColumnExists = false;
            foreach ($firstRow as $columnName) {
                if ($columnName!== 'id') {
                    $columnNames[] = $columnName;
                } else {
                    $idColumnExists = true;
                }
            }

            // 创建表并添加自增 ID 列
            $createTableQuery = "CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT";
            foreach ($columnNames as $columnName) {
                $createTableQuery.= ", {$columnName} TEXT";
            }
            $createTableQuery.= ")";
            $db->exec($createTableQuery);

            // 插入数据，自动生成 ID
            $rowNumber = 1;
            while (($data = fgetcsv($handle))!== FALSE) {
                $values = array();
                for ($i = 0; $i < count($data); $i++) {
                    if ($firstRow[$i]!== 'id') {
                        $values[] = "'".iconv('gbk', 'utf-8', $data[$i])."'";
                    }
                }
                $query = "INSERT INTO users (".implode(',', $columnNames).") VALUES (".implode(',', $values).")";
                $db->exec($query);
                $rowNumber++;
            }
            fclose($handle);
        }
        $db->close();
        header("Location: list_user.php");
    } else {
        echo "上传的 CSV 文件格式不正确。";
    }
} else {
    echo "上传文件失败。";
}

function validateCsv($csvFile) {
    $handle = fopen($csvFile, "r");
    if ($handle === false) {
        return false;
    }
    $firstRow = fgetcsv($handle);
    if ($firstRow === false || count($firstRow) === 0) {
        fclose($handle);
        return false;
    }
    while (($row = fgetcsv($handle))!== false) {
        if (count($row)!== count($firstRow)) {
            fclose($handle);
            return false;
        }
    }
    fclose($handle);
    return true;
}
?>
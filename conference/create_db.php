<?php
// 连接到SQLite数据库
$db = new SQLite3('../db/conference_main.db');

// 创建大会信息表的SQL语句
$query = "
    CREATE TABLE IF NOT EXISTS conference_info (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        conference_name TEXT NOT NULL,
        start_date TEXT NOT NULL,
        end_date TEXT NOT NULL,
        location TEXT NOT NULL,
        description TEXT,
        website_url TEXT,
        sessions TEXT,  -- 分会列表（字符串存储）
        committee_members TEXT -- 大会组委会信息（字符串存储）
    )
";

// 执行SQL创建表
$db->exec($query);

echo "大会信息表已创建";
?>

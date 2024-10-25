<?php
// utils.php

/**
 * 标准化分会列表格式
 * @param string $input 原始分会字符串
 * @return string 处理后的分会列表
 */
function formatSessions($input) {
    // 去除前后空格，按逗号分割
    $sessions = array_map('trim', explode(',', $input));
    // 去除空字符串
    $sessions = array_filter($sessions);
    // 使用逗号和空格连接
    return implode(', ', $sessions);
}

/**
 * 标准化组委会信息格式
 * @param string $input 原始组委会成员字符串
 * @return string 处理后的组委会成员
 */
function formatCommitteeMembers($input) {
    // 去除前后空格，按逗号分割
    $members = array_map('trim', explode(',', $input));
    // 去除空字符串
    $members = array_filter($members);

    $formattedMembers = [];
    foreach ($members as $member) {
        // 确保姓名和学校之间的加号前后没有空格，并去掉多余空格
        $nameSchool = preg_replace('/\s*\+\s*/', '+', $member); // 确保加号前后没有空格
        $nameSchool = preg_replace('/\s+/', ' ', $nameSchool); // 替换多余空格为一个空格
        $nameSchool = trim($nameSchool); // 去除前后空格
        $formattedMembers[] = $nameSchool; // 添加到格式化数组
    }

    // 使用逗号和空格连接
    return implode(', ', $formattedMembers);
}

/**
 * 解析分会字符串为分会数组
 * @param string $input 分会字符串
 * @return array 分会列表数组
 */
function parseSessions($input) {
    // 去除前后空格，按逗号分割并返回数组
    return array_filter(array_map('trim', explode(',', $input)));
}

/**
 * 解析组委会信息字符串为人名+学校组合
 * @param string $input 组委会成员字符串
 * @return array 包含人名+学校组合的数组
 */
function parseCommitteeMembers($input) {
    // 去除前后空格，按逗号分割
    $members = array_map('trim', explode(',', $input));
    // 去除空字符串
    return array_filter($members);
}
?>

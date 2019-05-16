<?php

return [
    //日志对应级别
    'levels' => ['debug' => 1, 'info' => 2, 'warning' => 3, 'error' => 4],
    //日志类型
    'types' => ['debug', 'info', 'warning', 'error', 'undefined', 'db', 'server', 'task', 'monitor', 'statistic', 'request'],
    //stacks内容过滤关键字
    'truncations' => ["github"],
    //日志内容过滤字段
    'sensitive' => ["phone", "password", "pwd", "token", "accessToken"],
    //日志内容正则模式过滤，此模式会有性能问题，不建议使用
    'regexSensitive' => [],
    //'regexSensitive' => ["|1\d{10}|"],
    //日志内容过滤规则
    'sensitiveRule' => ["12:4*4", "11:3*4", "7:2*2", "3:1*1", "2:1*0"],
    //日志记录级别
    'level' => 'info',
    //日志存放路径
    'file' => '',
    //日志内容格式
    'formatter' => 'Json',
];
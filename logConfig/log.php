<?php

return [
    //日志记录级别
    'level' => 'info',
    //stacks内容过滤关键字
    'truncations' => ["github"],
    //日志内容过滤字段
    'sensitive' => ["phone", "password", "pwd", "token", "accessToken"],
    //日志内容正则模式过滤，可能会有性能问题，不建议开启
    'regexSensitive' => [],
    //'regexSensitive' => ["|1\d{10}|"],
    //日志内容过滤规则
    'sensitiveRule' => ["12:4*4", "11:3*4", "7:2*2", "3:1*1", "2:1*0"],
    //日志存放路径
    'file' => '',
    //日志内容格式
    'formatter' => 'Json',
];

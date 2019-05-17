# Slog - A log standard for PHP

## Intro
在程序开发中写日志是一件非常重要，也是很容易被开发同学忽视的地方。日志记录不全，格式不统一给我们后期的搜集、分析和问题查找带来了很大的麻烦。基于以上问题推出一套统一约定的日志格式势在必行,
Slog完全参照<a href="https://github.com/ssgo/standard/blob/master/log.md">ssgo日志标准</a>。

## Directory structure

```
├── CHANGELOG.md           # CHANGELOG
├── README.md              # README
├── config                 # 配置文件            
├── src                
│   └── Slog   
│       ├── Collect        # 日志收集方式
│       ├── Filter         # 日志过滤规则
│       ├── Formatter      # 日志输出格式
│       └── Logger.php     # 日记记录脚本
└── tests                  # 测试脚本

```

## Installation

Install the latest version with

```bash
$ composer require slog/slog
```

## Basic Usage

```php
<?php
require_once __DIR__ . "/vendor/autoload.php";

$config = require_once __DIR__ . "/vendor/slog/slog/config/log.php";
$debug = new Slog\Logger($config);

$result = $debug->dbError([
    'dbType' => 'mysql',
    'dsn' => 'mysql:host=sdfadfadsf.mysql.rds.aliyuncs.com:3306;dbname=temp',
    'callStacks' => ['index.php line 1', 'test.php line 2'],
    'query' => '',
    'args' => '',
    'usedTime' => '',
    'error' => "connect time out",
]);

var_dump($result);
```

## Log Content Standard
参考： <a href="https://github.com/ssgo/standard/blob/master/log.md">ssgo日志标准</a>
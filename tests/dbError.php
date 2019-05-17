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
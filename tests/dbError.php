<?php
require_once __DIR__ . "/vendor/autoload.php";

$config = require_once __DIR__ . "/vendor/ssphp/ssphp/config/log.php";
$debug = new ssphp\Logger($config);

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
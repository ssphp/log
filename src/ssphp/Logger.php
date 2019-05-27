<?php

namespace ssphp;

use ssphp\Filter\Filter;
use ssphp\Standard\Log\AbstractLogger;
use ssphp\Standard\Log\LogLevel;
use ssphp\Standard\Log\LogType;

/**
 * 记录日志
 *
 * @Author   qishaobo
 *
 * @DateTime 2019-05-15
 */
class Logger extends AbstractLogger
{
    /**
     * 配置文件
     *
     * @var array
     */
    private static $config = [];

    public function __construct(array $config = [])
    {
        $this->initConfig($config);
    }

    /**
     * 加载配置文件
     *
     * @param    array      $config 配置
     *
     * @return   null
     */
    private function initConfig(array $config = [])
    {
        $baseConfig = require __DIR__ . '/../../config/log.php';
        if (empty($config)) {
            self::$config = $baseConfig;
            return;
        }

        foreach ($config as $k => $v) {
            if (!isset($baseConfig[$k])) {
                unset($config[$k]);
            }

            if ($k === 'levels' || $k === 'types') {
                unset($config[$k]);
            }
        }

        $config = array_merge($baseConfig, $config);
        self::$config = $config;
    }

    /**
     * 日志内容包含的基本字段
     *
     * @return   array
     */
    private function baseContent($logType)
    {
        return [
            'logTime' => microtime(true),
            'traceId' => '',
            'logType' => $logType,
        ];
    }

    /**
     * 记录日志
     *
     *
     * @param  array  $message 日志内容
     * @param  string $logType 日志类型
     * @param  string $level   日志级别
     *
     * @return array
     */
    public function log(array $message, string $logType = 'info', string $level = 'info')
    {
        //未知类型
        if (!isset(LogType::${$logType})) {
            $logType = 'undefined';
            $message = ['undefined' => json_encode($message, JSON_UNESCAPED_UNICODE)];
        }

        //检查日志级别
        if (!$this->checkLevel($level)) {
            return [
                'code' => '0x000000',
                'data' => '日志级别不用登记',
            ];
        }

        $message = array_merge($this->baseContent($logType), $message);
        $logFields = $this->getFields($logType);

        if (!empty($msg = $this->checkLogFields($message, $logFields))) {
            return [
                'code' => '0x000002',
                'message' => $msg,
            ];
        }

        $fiterObj = new Filter(self::$config);
        $result = $this->writeLog($fiterObj->fiter($message));

        return [
            'code' => '0x000000',
        ];
    }

    /**
     * 检查必须包含字段
     *
     * @param  array  $message
     * @param  array  $logFields
     *
     * @return  string
     */
    public function checkLogFields(array $message, array $logFields)
    {
        if (empty($logFields)) {
            return "";
        }

        foreach ($logFields as $field => $type) {
            if (!isset($message[$field])) {
                return "缺少" . $field . "字段";
            }
            //检查变量类型
            switch ($type) {
                case 'string':
                    $bool = is_string($message[$field]);
                    break;
                case 'float64':
                case 'float32':
                    $bool = is_int($message[$field]) || is_float($message[$field]) ? true : false;
                    break;
                case 'int':
                case 'uint':
                    $bool = is_int($message[$field]);
                    break;
                case 'bool':
                    $bool = is_bool($message[$field]);
                    break;
                default:
                    $bool = true;
                    break;
            }

            if (!$bool) {
                return "字段" . $field . "类型不正确";
            }
        }

        return "";
    }

    /**
     * 查询日志内容包含字段
     *
     * @param    string     $type
     *
     * @return   array
     */
    private function getFields($type)
    {
        static $logFields;

        if (isset($logFields[$type])) {
            return $logFields[$type];
        }

        if (!isset(LogType::${$type})) {
            return $logFields[$type] = [];
        }

        $fields = $this->handleFields($type);

        return $logFields[$type] = $fields;
    }

    /**
     * 递归日志类型字段
     *
     * @param  string $type
     *
     * @return array
     */
    private function handleFields($type)
    {
        if (!isset(LogType::${$type})) {
            return [];
        }

        $fields = LogType::${$type};
        if (empty($fields['extendFields'])) {
            return $fields;
        }

        $extendFields = $fields['extendFields'];
        unset($fields['extendFields']);
        foreach ($extendFields as $field) {
            $fields = call_user_func('array_merge', $fields, $this->handleFields($field));
        }

        return $fields;
    }

    /**
     * 检查日志级别
     *
     * @param    string     $logLevel
     *
     * @return   bool
     */
    private function checkLevel(string $logLevel)
    {
        $settedLevel = isset(self::$config['level']) ? self::$config['level'] : 'info';

        if (!isset(LogLevel::$$settedLevel)) {
            throw new Exception("配置了未知的日志级别");
        }

        if (!isset(LogLevel::$$logLevel)) {
            $logLevel = 'info';
        }

        return LogLevel::$$logLevel >= LogLevel::$$settedLevel;
    }

    /**
     * 记录日志
     *
     * @param    array     $data
     *
     * @return   bool
     */
    private function writeLog(array $data)
    {
        //格式化日志
        if (empty(self::$config['formatter']) || !class_exists('\\ssphp\\Formatter\\' . self::$config['formatter'])) {
            $formatterObj = new \ssphp\Formatter\Json();
        } else {
            $class = '\\ssphp\\Formatter\\' . self::$config['formatter'];
            $formatterObj = new $class();
        }

        $lock = isset(self::$config['lockEx']) && self::$config['lockEx'] ? true : false;
        $file = !empty(self::$config['file']) ? self::$config['file'] : 'log/' . date("Ymd") . '/' . $data['logType'] . '.log';

        //记录日志
        $collectObj = new \ssphp\Collect\File($file, $lock);
        $collectObj->write($formatterObj->format($data));
        $collectObj->close();
        return true;
    }
}

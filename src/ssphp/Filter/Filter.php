<?php

namespace ssphp\Filter;

/**
 * 日志过滤
 *
 * qishaobo
 *
 * 2019-05-16
 */
class Filter
{
    /**
     * 过滤配置
     *
     * @var array
     */
    protected static $filter;

    public function __construct($filter)
    {
        self::$filter = $filter;
    }

    /**
     * 过滤日志
     *
     * @param    array      $data 日志内容
     *
     * @return   array
     */
    public function fiter(array $data)
    {
        if (
            empty(self::$filter['sensitive']) &&
            empty(self::$filter['regexSensitive'])
        ) {
            return $data;
        }

        if (!empty(self::$filter['truncations']) && !empty($data['callStacks'])) {
            $data['callStacks'] = $this->truncations($data['callStacks']);
        }

        $level = isset(self::$filter['recursiveLevel']) ? self::$filter['recursiveLevel'] : 4;
        $this->recursiveFilter($data, $level);

        return $data;
    }

    /**
     * 递归过滤
     *
     * @param  array  &$data 过滤数据
     * @param  integer $level  过滤层级
     *
     * @return
     */
    private function recursiveFilter(&$data, $level, $start = 0)
    {
        if (!is_array($data) && !is_object($data)) {
            return;
        }

        if ($start > $level) {
            return;
        }

        $start++;

        foreach ($data as $k => &$v) {
            //不过滤字段
            if (in_array($k, ['callStacks', 'logTime', 'traceId', 'logType'])) {
                continue;
            }

            //key值过滤
            if (!empty(self::$filter['sensitive'])) {
                //数组、对象
                if (is_array($v) || is_object($v)) {
                    $this->recursiveFilter($v, $level, $start);
                } else if (!is_string($v) && !is_numeric($v)) {
                    continue;
                }

                //字符串
                if (in_array($k, self::$filter['sensitive'])) {
                    $v = $this->filterValue($v);
                    continue;
                }
            }

            //value正则过滤
            if (!empty(self::$filter['regexSensitive'])) {
                foreach (self::$filter['regexSensitive'] as $pattern) {
                    preg_match($pattern, $v, $matches);
                    if (!empty($matches)) {
                        $data[$k] = $this->filterValue($v);
                        continue;
                    }
                }
            }
        }
    }

    /**
     * 过滤日志上下文
     *
     * @param    array     $callStacks
     *
     * @return   array
     */
    protected function truncations(array $callStacks)
    {
        foreach ($callStacks as $k => $v) {
            $callStacks[$k] = str_replace(self::$filter['truncations'], '', $v);
        }

        return $callStacks;
    }

    /**
     * 过滤字符串
     *
     * @param    string     $str
     *
     * @return   string
     */
    private function filterValue(string $str)
    {
        if (empty($str)) {
            return "";
        }

        $sensitiveRule = $this->sensitiveRule();
        if (empty($sensitiveRule)) {
            return $str;
        }

        $len = strlen($str);
        foreach ($sensitiveRule as $k => $v) {
            if ($len >= $k) {
                return $this->substr($str, $len, $v);
            }
        }

        return '****';
    }

    /**
     * 获取替换条件
     *
     * @return   array
     */
    private function sensitiveRule()
    {
        static $sensitiveRule;

        if (!empty($sensitiveRule)) {
            return $sensitiveRule;
        }

        if (empty(self::$filter['sensitiveRule'])) {
            $rules = self::$filter['sensitiveRule'];
        } else {
            $rules = ["12:4*4", "11:3*4", "7:2*2", "3:1*1", "2:1*0"];
        }

        foreach ($rules as $rule) {
            if (strpos($rule, ":") === false || strpos($rule, "*") === false) {
                continue;
            }

            list($k, $v) = explode(":", $rule);
            $sensitiveRule[$k] = explode("*", $v);
        }

        krsort($sensitiveRule);
        return $sensitiveRule;
    }

    /**
     * 截取字符串
     *
     * @param    string     $str
     * @param    int     $len
     * @param    array     $rule
     *
     * @return   string
     */
    private function substr(string $str, $len, $rule)
    {
        $mid = $len - $rule[0] - $rule[1];
        if ($mid < 1) {
            return str_pad('', $rule[0] + $rule[1], '*');
        }

        $content = substr($str, 0, $rule[0]) . str_pad('', $mid, '*');
        if ($rule[1] > 0) {
            return substr($str, 0, $rule[0]) . str_pad('', $mid, '*') . substr($str, -$rule[1]);
        }

        return substr($str, 0, $rule[0]) . str_pad('', $mid, '*');
    }

}

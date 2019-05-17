<?php

namespace ssphp\Formatter;

interface FormatterInterface
{
    /**
     * 格式化数据
     *
     * @param    array      $content 
     *
     * @return   string
     *
     * @Author   齐少博
     *
     * @DateTime 2019-05-14
     */
    public function format(array $content);
}

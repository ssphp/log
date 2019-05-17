<?php

namespace ssphp\Collect;

/**
 * 日志记录接口
 * 
 * @Author   齐少博
 *
 * @DateTime 2019-05-16
 */
interface CollectInterface
{
    /**
     * 日志内容
     *
     * @return   array
     */
    public function write(string $record);

    /**
     * 关闭连接
     *
     * @return   null
     */
    public function close();
}

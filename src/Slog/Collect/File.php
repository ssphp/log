<?php

namespace Slog\Collect;

/**
 * 记录日志
 *
 * @Author   齐少博
 *
 * @DateTime 2019-05-15
 */
class File implements CollectInterface
{
    protected $dirCreated = false;

    public function __construct(string $file, $useLocking = false)
    {
        $this->file = $file;
        $this->useLocking = $useLocking;
    }

    /**
     * 记录日志
     *
     * @param    string     $record  日志内容
     *
     * @return   null
     */
    public function write(string $record)
    {
        if (null === $this->file || '' === $this->file) {
            throw new \Exception('日志路径为空！');
        }
        $this->createDir();
        $this->errorMessage = null;
        set_error_handler(array($this, 'errorHandler'));
        $this->stream = fopen($this->file, 'a');
        restore_error_handler();

        if (!is_resource($this->stream)) {
            $this->stream = null;
            throw new \Exception('文件打开失败: ' . $this->errorMessage);
        }

        if ($this->useLocking) {
            flock($this->stream, LOCK_EX);
        }

        $this->writeStream($this->stream, $record);

        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
    }

    /**
     * 关闭日志
     *
     * @return   null
     */
    public function close()
    {
        if ($this->file && is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * 写日志
     *
     * @param    object     $stream
     * @param    string     $record
     *
     * @return   null
     */
    protected function writeStream($stream, string $record)
    {
        fwrite($stream, $record);
    }

    /**
     * 异常处理
     *
     * @param    string     $code
     * @param    string     $msg
     *
     * @return
     */
    private function errorHandler($code, $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }

    /**
     * 获取文件目录
     *
     * @param    string     $file
     *
     * @return   string
     */
    private function getDir($file)
    {
        $pos = strpos($file, '://');
        if ($pos === false) {
            return dirname($file);
        }

        if ('file://' === substr($file, 0, 7)) {
            return dirname(substr($file, 7));
        }

        return "";
    }

    /**
     * 创建文件目录
     *
     * @return   null
     */
    private function createDir()
    {
        if ($this->dirCreated) {
            return;
        }

        $dir = $this->getDir($this->file);
        if (null !== $dir && !is_dir($dir)) {
            $this->errorMessage = null;
            set_error_handler(array($this, 'errorHandler'));
            $status = mkdir($dir, 0777, true);
            restore_error_handler();

            if (false === $status) {
                throw new \Exception('创建目录失败: ' . $this->errorMessage);
            }
        }
        $this->dirCreated = true;
    }
}

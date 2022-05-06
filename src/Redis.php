<?php

namespace Yng\Redis;

/**
 * @method get($key) 字符串获取
 * @method set($key, $value, int $timeout = null) 字符串设置
 * @method hGet($key, $hashKey)  哈希获取
 * @method hSet($key, $hashKey, $value) 哈希设置
 * @method append($key, $value) 追加
 * @method flushAll() 清空DB
 * Class Redis
 *
 * @package Yng
 */
class Redis
{

    /**
     * 读操作的指令
     */
    protected const READ = ['get', 'hGet', 'exists', 'hExists', 'getRange', 'ttl', 'mGet', 'key', 'strlen'];

    /**
     * 写操作的指令
     */
    protected const WRITE = ['set', 'hSet', 'getset', 'del', 'decr', 'decrBy', 'hDel', 'setnx', 'setex', 'mset', 'msetnx', 'expire', 'rename', 'persist', 'incr', 'incrBy', 'append', 'setRange'];

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * 初始化
     * Redis constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->connector = new Connector($config);
    }

    /**
     * 读指令
     *
     * @param $operate
     *
     * @return bool
     */
    protected function isRead($operate): bool
    {
        return in_array($operate, static::READ);
    }

    /**
     * 写指令
     *
     * @param $operate
     *
     * @return bool
     */
    protected function isWrite($operate): bool
    {
        return in_array($operate, static::WRITE);
    }

    public function __call($method, $arguments)
    {
        if (!$this->isRead($method) && !$this->isWrite($method)) {
            throw new \Exception("暂时不支持{$method}，建议使用handle(\$isRead)方法指定读写方式！");
        }
        //TODO 其他命令
        return $this->handle($this->isRead($method))->{$method}(...$arguments);
    }

    /**
     * @param \Closure $multi
     * @param int      $mode
     *
     * @return mixed
     * @throws \Exception
     */
    public function multi(\Closure $multi, int $mode = \Redis::MULTI)
    {
        $handle = $this->handle(false);
        $handle->multi($mode);
        try {
            $result = $multi($handle);
            $handle->exec();
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 监听一个值
     *
     * @param string $key
     */
    public function watch(string $key)
    {
        $this->handle(false)->watch($key);
    }

    /**
     * 取消监听
     *
     * @param string $key
     */
    public function unwatch(string $key)
    {
        $this->handle(false)->unwatch($key);
    }

    /**
     * redis句柄
     *
     * @param bool $isRead
     *
     * @return mixed|\Redis
     */
    public function handle(bool $isRead = true)
    {
        return $this->connector->getConnection($isRead);
    }

    public static function __new(\Yng\Config\Repository $config)
    {
        return new static($config->get('redis'));
    }

}

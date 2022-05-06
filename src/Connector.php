<?php

namespace Yng\Redis;

/**
 * Class Connector
 * @package Yng\Redis
 */
class Connector
{
    /**
     * 配置
     * @var array
     */
    protected $config;

    /**
     * 连接池
     * @var array
     */
    protected $connectionPool = [];

    /**
     * 初始化配置
     * Connector constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * 连接池中存在连接
     * @param bool $isRead
     * @return bool
     */
    protected function hasConnection(bool $isRead = true)
    {
        return isset($this->connectionPool[(bool)$isRead]);
    }

    /**
     * 获取连接池中的连接，如果没有则建立连接
     * @param bool $isRead
     * @return mixed|\Redis
     */
    public function getConnection(bool $isRead = true)
    {
        $key = (int)$isRead;
        if ($this->hasConnection($isRead)) {
            return $this->connectionPool[$key];
        }
        $this->connectionPool[$key] = $this->connect($isRead);
        if ($this->hasOnlyOne()) {
            $this->connectionPool[!$key] = $this->connectionPool[$key];
        }
        return $this->connectionPool[$key];
    }

    /**
     * 建立连接
     * @param string $type
     * @return \Redis
     */
    protected function connect(bool $isRead = true): \Redis
    {
        $redis = new \Redis();
        [$host, $port, $auth] = $this->withConfig($isRead);
        $redis->connect($host, $port);
        !empty($auth) && $redis->auth($auth);
        return $redis;
    }

    /**
     * 只有一台
     * @return bool
     */
    public function hasOnlyOne(): bool
    {
        return is_string($this->config['host']) || 1 === count($this->config['host']);
    }

    /**
     * redis配置
     * @param bool $isRead
     * @return array
     * @throws \Exception
     */
    protected function withConfig(bool $isRead = true): array
    {
        $type = $isRead ? 'slave' : 'master';
        try {
            $id   = $this->config[$type];
            $id   = is_array($id) ? array_rand(array_flip($id)) : (int)$id;
            $host = is_array($this->config['host']) ? $this->config['host'][$id] : $this->config['host'];
            $port = is_array($this->config['port']) ? $this->config['port'][$id] : $this->config['port'];
            $auth = is_array($this->config['auth']) ? $this->config['auth'][$id] : $this->config['auth'];
            return [$host, $port, $auth];
        } catch (\Exception $e) {
            throw new \Exception("Redis配置文件不规范! ( {$e->getMessage()} )");
        }
    }

}

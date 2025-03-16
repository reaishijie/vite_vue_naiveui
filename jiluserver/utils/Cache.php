<?php
class Cache {
    private static $instance = null;
    private $redis;

    private function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set($key, $value, $ttl = 3600) {
        return $this->redis->setex($key, $ttl, serialize($value));
    }

    public function get($key) {
        $value = $this->redis->get($key);
        return $value ? unserialize($value) : null;
    }
} 
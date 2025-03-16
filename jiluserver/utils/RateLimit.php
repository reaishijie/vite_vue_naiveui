<?php
class RateLimit {
    public static function check($key, $limit = 60, $period = 60) {
        $cache = Cache::getInstance();
        $current = $cache->incr($key);
        if ($current === 1) {
            $cache->expire($key, $period);
        }
        return $current <= $limit;
    }
} 
<?php
class Config {
    private static $config = null;

    public static function load() {
        if (self::$config === null) {
            $env = getenv('APP_ENV') ?: 'development';
            self::$config = require __DIR__ . "/../config/{$env}.php";
        }
        return self::$config;
    }
} 
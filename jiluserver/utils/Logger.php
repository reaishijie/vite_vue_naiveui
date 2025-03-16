<?php
class Logger {
    public static function info($message, $context = []) {
        error_log(sprintf("[INFO] %s %s", 
            $message, 
            !empty($context) ? json_encode($context) : ''
        ));
    }

    public static function error($message, $context = []) {
        error_log(sprintf("[ERROR] %s %s", 
            $message, 
            !empty($context) ? json_encode($context) : ''
        ));
    }
} 
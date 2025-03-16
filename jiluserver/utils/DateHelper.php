<?php
class DateHelper {
    public static function formatDateTime($date) {
        return date('Y-m-d H:i:s', strtotime($date));
    }

    public static function isValidDate($date) {
        return (bool)strtotime($date);
    }
} 
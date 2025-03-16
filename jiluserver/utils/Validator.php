<?php
class Validator {
    public static function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && !isset($data[$field])) {
                $errors[$field] = '字段不能为空';
            }
            // 可以添加更多验证规则
        }
        return $errors;
    }
} 
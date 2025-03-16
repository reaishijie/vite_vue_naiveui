<?php
class JWT {
    private static function getKey() {
        $config = require __DIR__ . '/../config/config.php';
        return $config['jwt_secret'] ?? 'your_secret_key';
    }

    private static function getExpireTime() {
        $config = require __DIR__ . '/../config/config.php';
        return $config['jwt_expire'] ?? 7200; // 默认2小时
    }

    public static function generate($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($data, [
            'exp' => time() + self::getExpireTime(),
            'iat' => time()
        ]));

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::getKey(), true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public static function verify($token) {
        try {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) != 3) {
                error_log("JWT验证失败: token格式不正确");
                return false;
            }

            $payload = json_decode(base64_decode($tokenParts[1]), true);
            if (!$payload) {
                error_log("JWT验证失败: payload解码失败");
                return false;
            }
            
            // 检查是否存在exp字段
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                error_log("JWT验证失败: token已过期");
                return false;
            }

            // 验证签名
            $base64Header = $tokenParts[0];
            $base64Payload = $tokenParts[1];
            $signature = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[2]));
            
            $expectedSignature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::getKey(), true);
            if (!hash_equals($expectedSignature, $signature)) {
                error_log("JWT验证失败: 签名无效");
                return false;
            }

            return $payload;
        } catch (Exception $e) {
            error_log("JWT验证异常: " . $e->getMessage());
            return false;
        }
    }
}
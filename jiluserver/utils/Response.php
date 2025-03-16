<?php
class Response {
    public static function json($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null, $message = '操作成功') {
        echo json_encode([
            'code' => 200,
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function error($message = '操作失败', $code = 400) {
        http_response_code($code);
        echo json_encode([
            'code' => $code,
            'message' => $message
        ]);
    }
}
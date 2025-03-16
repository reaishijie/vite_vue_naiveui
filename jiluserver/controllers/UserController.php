<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JWT.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    private function checkAuth() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['message' => '未登录']);
            exit;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $payload = JWT::verify($token);

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['message' => '登录已过期']);
            exit;
        }

        return $payload;
    }

    public function getProfile() {
        $user = $this->checkAuth();

        try {
            $userInfo = $this->userModel->findByUsername($user['username']);
            unset($userInfo['password']); // 移除敏感信息

            echo json_encode([
                'code' => 200,
                'data' => $userInfo
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => '获取用户信息失败']);
        }
    }

    public function updateProfile() {
        $user = $this->checkAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        // 只允许更新特定字段
        $allowedFields = ['email', 'avatar'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            http_response_code(400);
            echo json_encode(['message' => '没有可更新的数据']);
            return;
        }

        try {
            $this->userModel->updateProfile($user['user_id'], $updateData);
            echo json_encode([
                'code' => 200,
                'message' => '更新成功'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => '更新失败']);
        }
    }
}
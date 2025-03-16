<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JWT.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode(['message' => '用户名和密码不能为空']);
            return;
        }

        $user = $this->userModel->findByUsername($data['username']);

        if ($user && password_verify($data['password'], $user['password'])) {
            // 检查用户是否是管理员
            $isAdmin = $user['role'] === 'admin';
            
            // 生成 JWT token
            $token = JWT::generate([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $isAdmin ? 'admin' : 'user'
            ]);

            echo json_encode([
                'code' => 200,
                'message' => '登录成功',
                'data' => [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role' => $isAdmin ? 'admin' : 'user',
                    'token' => $token
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => '用户名或密码错误']);
        }
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['username']) || !isset($data['password']) || !isset($data['email'])) {
            http_response_code(400);
            echo json_encode(['message' => '请填写完整信息']);
            return;
        }

        // 检查用户名是否已存在
        if ($this->userModel->findByUsername($data['username'])) {
            http_response_code(400);
            echo json_encode(['message' => '用户名已存在']);
            return;
        }

        try {
            $this->userModel->create($data['username'], $data['password'], $data['email']);
            echo json_encode([
                'code' => 200,
                'message' => '注册成功'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => '注册失败']);
        }
    }
}
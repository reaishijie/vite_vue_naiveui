<?php
error_reporting(E_ALL);
//启用所有类型的错误、警告和通知的报告。E_ALL 是一个常量，代表所有错误类型。
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$config = require 'config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 获取请求路径
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uriParts = array_values(array_filter(explode('/', $uri)));
    
    // 如果没有路径参数，返回默认响应
    if (empty($uriParts)) {
        echo json_encode([
            'code' => 200,
            'message' => 'API服务正常',
            'data' => [
                'uri' => $uriParts,
                'method' => $_SERVER['REQUEST_METHOD'],
                'db_connected' => true,
                'endpoints' => [
                    '/auth/login' => 'POST - 用户登录',
                    '/auth/register' => 'POST - 用户注册',
                    '/records' => 'GET - 获取记录列表',
                    '/records' => 'POST - 创建新记录',
                    '/records/{id}' => 'PUT - 更新记录',
                    '/records/{id}' => 'DELETE - 删除记录'
                ]
            ]
        ]);
        exit;
    }
    
    // API 路由处理
    switch ($uriParts[0]) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $authController = new AuthController();
            
            if (isset($uriParts[1])) {
                if ($uriParts[1] === 'login') {
                    $authController->login();
                } elseif ($uriParts[1] === 'register') {
                    $authController->register();
                } else {
                    throw new Exception('无效的认证路径');
                }
            } else {
                throw new Exception('缺少认证方法');
            }
            break;
            
        case 'records':
            require_once __DIR__ . '/controllers/RecordController.php';
            $recordController = new RecordController();
            
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'GET':
                    $recordController->getList();
                    break;
                case 'POST':
                    $recordController->create();
                    break;
                case 'PUT':
                    $recordController->update();
                    break;
                case 'DELETE':
                    $recordController->delete();
                    break;
                default:
                    throw new Exception('不支持的请求方法');
            }
            break;
            
        case 'logs':
            require_once __DIR__ . '/controllers/RecordController.php';
            $recordController = new RecordController();
            
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $recordController->getLogs();
            } else {
                throw new Exception('不支持的请求方法');
            }
            break;
            
        default:
            echo json_encode([
                'code' => 404,
                'message' => '接口不存在',
                'path' => $uriParts[0]
            ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'code' => 500,
        'message' => '服务器错误',
        'error' => $e->getMessage()
    ]);
}

// API 处理函数
function handleLogin($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['message' => '用户名和密码不能为空']);
        return;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$data['username']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($data['password'], $user['password'])) {
        // 生成 JWT token
        require_once __DIR__ . '/utils/JWT.php';
        $token = JWT::generate([
            'user_id' => $user['id'],
            'username' => $user['username']
        ]);
        
        echo json_encode([
            'code' => 200,
            'message' => '登录成功',
            'data' => [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'token' => $token  // 添加 token 到响应中
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['message' => '用户名或密码错误']);
    }
}

function handleRegister($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password']) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['message' => '请填写完整信息']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['email']
        ]);
        
        echo json_encode([
            'code' => 200,
            'message' => '注册成功'
        ]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['message' => '用户名已存在']);
    }
}

function getRecords($pdo) {
    try {
        $userId = $_GET['user_id'] ?? null;
        
        $sql = "SELECT * FROM records";
        $params = [];
        
        if ($userId) {
            $sql .= " WHERE user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY record_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'code' => 200,
            'message' => '获取成功',
            'data' => $records
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'message' => '获取记录失败',
            'error' => $e->getMessage()
        ]);
    }
}

function createRecord($pdo) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['user_id']) || !isset($data['content']) || !isset($data['record_date'])) {
            throw new Exception('缺少必要参数');
        }
        
        $stmt = $pdo->prepare("INSERT INTO records (user_id, content, record_date) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['content'],
            $data['record_date']
        ]);
        
        echo json_encode([
            'code' => 200,
            'message' => '创建成功',
            'data' => [
                'id' => $pdo->lastInsertId()
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '创建记录失败',
            'error' => $e->getMessage()
        ]);
    }
}

function updateRecord($pdo) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id']) || !isset($data['content']) || !isset($data['record_date'])) {
            throw new Exception('缺少必要参数');
        }
        
        $stmt = $pdo->prepare("UPDATE records SET content = ?, record_date = ? WHERE id = ?");
        $stmt->execute([
            $data['content'],
            $data['record_date'],
            $data['id']
        ]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('记录不存在');
        }
        
        echo json_encode([
            'code' => 200,
            'message' => '更新成功'
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '更新记录失败',
            'error' => $e->getMessage()
        ]);
    }
}

function deleteRecord($pdo) {
    try {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            throw new Exception('缺少记录ID');
        }
        
        $stmt = $pdo->prepare("DELETE FROM records WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('记录不存在');
        }
        
        echo json_encode([
            'code' => 200,
            'message' => '删除成功'
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '删除记录失败',
            'error' => $e->getMessage()
        ]);
    }
}
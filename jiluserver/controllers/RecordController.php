<?php
require_once __DIR__ . '/../models/Record.php';
require_once __DIR__ . '/../models/OperationLog.php';
require_once __DIR__ . '/../utils/JWT.php';

class RecordController {
    private $recordModel;
    private $logModel;

    public function __construct() {
        $this->recordModel = new Record();
        $this->logModel = new OperationLog();
    }

    private function checkAuth($requireAdmin = false) {
        $headers = getallheaders();
        error_log("请求头: " . json_encode($headers));
        
        // 查找 Authorization 头（不区分大小写）
        $authHeader = null;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $authHeader = $value;
                break;
            }
        }
        
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['message' => '未登录']);
            exit;
        }

        // 检查 Bearer 前缀
        if (strpos($authHeader, 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode(['message' => 'Authorization 格式错误，应为 Bearer token']);
            exit;
        }

        $token = substr($authHeader, 7); // 移除 "Bearer " 前缀
        error_log("Token: " . $token);
        
        $payload = JWT::verify($token);
        error_log("验证结果: " . ($payload ? json_encode($payload) : "验证失败"));

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['message' => '登录已过期']);
            exit;
        }
        
        // 检查是否需要管理员权限
        if ($requireAdmin && (!isset($payload['role']) || $payload['role'] !== 'admin')) {
            http_response_code(403);
            echo json_encode(['message' => '无权访问']);
            exit;
        }

        return $payload;
    }

    public function getList() {
        $user = $this->checkAuth();
        error_log("当前用户信息: " . json_encode($user));

        try {
            if (isset($user['role']) && $user['role'] === 'admin') {
                error_log("管理员访问，返回所有记录");
                $records = $this->recordModel->getAll();
            } else {
                error_log("普通用户访问，只返回用户ID {$user['user_id']} 的记录");
                $records = $this->recordModel->getByUserId($user['user_id']);
            }
            
            error_log("返回记录数量: " . count($records));
            echo json_encode([
                'code' => 200,
                'data' => $records,
                'debug' => [
                    'user_role' => $user['role'] ?? 'user',
                    'record_count' => count($records)
                ]
            ]);
        } catch (Exception $e) {
            error_log("获取记录失败: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => '获取记录失败']);
        }
    }

    public function create() {
        $user = $this->checkAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['content'])) {
            http_response_code(400);
            echo json_encode(['message' => '请填写完整信息']);
            return;
        }

        try {
            $recordDate = isset($data['record_date']) ? $data['record_date'] : date('Y-m-d H:i:s');
            
            // create 方法现在直接返回新记录的 ID
            $recordId = $this->recordModel->create($user['user_id'], $data['content'], $recordDate);
            
            if ($recordId) {
                // 记录操作日志
                $this->logModel->create(
                    $user['user_id'],
                    $user['username'],
                    'create',
                    'record',
                    $recordId,
                    $data['content']
                );

                echo json_encode([
                    'code' => 200,
                    'message' => '添加成功',
                    'data' => [
                        'id' => $recordId
                    ]
                ]);
            } else {
                throw new Exception('创建记录失败');
            }
        } catch (Exception $e) {
            error_log("添加失败: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => '添加失败']);
        }
    }

    public function update() {
        $user = $this->checkAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['content'])) {
            http_response_code(400);
            echo json_encode(['message' => '请填写完整信息']);
            return;
        }

        try {
            $record = $this->recordModel->getById($data['id']);
            if (!$record) {
                http_response_code(404);
                echo json_encode(['message' => '记录不存在']);
                return;
            }

            if (!isset($user['role']) || $user['role'] !== 'admin') {
                if ($record['user_id'] != $user['user_id']) {
                    http_response_code(403);
                    echo json_encode(['message' => '无权操作此记录']);
                    return;
                }
            }

            $recordDate = isset($data['record_date']) ? $data['record_date'] : date('Y-m-d H:i:s');
            
            $this->recordModel->update($data['id'], $data['content'], $recordDate);

            // 记录操作日志
            $this->logModel->create(
                $user['user_id'],
                $user['username'],
                'update',
                'record',
                $data['id'],
                $data['content']
            );

            echo json_encode([
                'code' => 200,
                'message' => '更新成功'
            ]);
        } catch (Exception $e) {
            error_log("更新失败: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => '更新失败']);
        }
    }

    public function delete() {
        $user = $this->checkAuth();
        
        // 尝试从 URL 参数获取 id
        $id = $_GET['id'] ?? null;
        
        // 如果 URL 参数中没有 id，尝试从请求体获取
        if (!$id) {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(['message' => '参数错误：缺少记录ID']);
            return;
        }

        try {
            $record = $this->recordModel->getById($id);
            if (!$record) {
                http_response_code(404);
                echo json_encode(['message' => '记录不存在']);
                return;
            }

            if (!isset($user['role']) || $user['role'] !== 'admin') {
                if ($record['user_id'] != $user['user_id']) {
                    http_response_code(403);
                    echo json_encode(['message' => '无权操作此记录']);
                    return;
                }
            }

            $this->recordModel->delete($id);

            // 记录操作日志
            $this->logModel->create(
                $user['user_id'],
                $user['username'],
                'delete',
                'record',
                $id,
                json_encode($record)  // 记录被删除的内容
            );

            echo json_encode([
                'code' => 200,
                'message' => '删除成功'
            ]);
        } catch (Exception $e) {
            error_log("删除失败: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => '删除失败']);
        }
    }

    // 添加一个新方法用于获取所有记录
    public function getAllRecords() {
        // 需要管理员权限
        $user = $this->checkAuth(true);

        try {
            error_log("开始获取所有记录");
            $records = $this->recordModel->getAll();
            error_log("获取到的记录数量: " . count($records));
            echo json_encode([
                'code' => 200,
                'data' => $records,
                'debug' => [
                    'user' => $user,
                    'recordCount' => count($records)
                ]
            ]);
        } catch (Exception $e) {
            error_log("获取记录失败: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => '获取记录失败']);
        }
    }

    // 添加获取日志列表的方法
    public function getLogs() {
        $user = $this->checkAuth(true);  // 只有管理员可以查看日志
        
        try {
            $filters = [];
            if (isset($_GET['user_id'])) {
                $filters['user_id'] = $_GET['user_id'];
            }
            if (isset($_GET['action'])) {
                $filters['action'] = $_GET['action'];
            }

            $logs = $this->logModel->getList($filters);
            echo json_encode([
                'code' => 200,
                'data' => $logs
            ]);
        } catch (Exception $e) {
            error_log("获取日志失败: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => '获取日志失败']);
        }
    }
}
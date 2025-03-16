# 记录系统技术实现文档

## 1. 核心功能实现

### 1.1 用户认证模块


#### 1.1.1 JWT 认证实现

````markdown:new.md
```php
class JWT {
    private static function getKey() {
        $config = require __DIR__ . '/../config/config.php';
        return $config['jwt_secret'];
    }

    public static function generate($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($data, [
            'exp' => time() + 7200,  // 2小时过期
            'iat' => time()
        ]));

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);
  
        $signature = hash_hmac('sha256', 
            $base64Header . "." . $base64Payload, 
            self::getKey(), 
            true
        );

        return $base64Header . "." . $base64Payload . "." . self::base64UrlEncode($signature);
    }

    public static function verify($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(self::base64UrlDecode($parts[1]), true);
        if (!$payload || $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}
```

#### 1.1.2 认证控制器
```php
class AuthController {
    private $userModel;

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
  
        // 验证用户
        $user = $this->userModel->findByUsername($data['username']);
        if ($user && password_verify($data['password'], $user['password'])) {
            // 生成 token
            $token = JWT::generate([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role']
            ]);
      
            Response::success([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'token' => $token
            ]);
        }
    }
}
```

### 1.2 记录管理模块

#### 1.2.1 记录模型
```php
class Record {
    private $db;

    public function getByUserId($userId) {
        $stmt = $this->db->prepare(
            "SELECT * FROM records WHERE user_id = ? 
             ORDER BY record_date DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAll() {
        $stmt = $this->db->prepare(
            "SELECT r.*, u.username 
             FROM records r 
             JOIN users u ON r.user_id = u.id 
             ORDER BY r.record_date DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($userId, $content, $recordDate) {
        try {
            $this->beginTransaction();
      
            $stmt = $this->db->prepare(
                "INSERT INTO records (user_id, content, record_date) 
                 VALUES (?, ?, ?)"
            );
            $result = $stmt->execute([$userId, $content, $recordDate]);
      
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
```

#### 1.2.2 记录控制器
```php
class RecordController {
    private $recordModel;

    public function getList() {
        $user = $this->checkAuth();
  
        try {
            // 根据用户角色返回不同数据
            if (isset($user['role']) && $user['role'] === 'admin') {
                $records = $this->recordModel->getAll();
            } else {
                $records = $this->recordModel->getByUserId($user['user_id']);
            }
      
            Response::success($records);
        } catch (Exception $e) {
            Logger::error("获取记录失败", ['error' => $e->getMessage()]);
            Response::error('获取记录失败');
        }
    }

    public function create() {
        $user = $this->checkAuth();
        $data = json_decode(file_get_contents('php://input'), true);

        // 验证数据
        $errors = Validator::validate($data, [
            'content' => ['required' => true]
        ]);

        if (!empty($errors)) {
            Response::error('参数错误', 400);
            return;
        }

        try {
            $recordDate = isset($data['record_date']) 
                ? $data['record_date'] 
                : date('Y-m-d H:i:s');

            $this->recordModel->create(
                $user['user_id'],
                Security::sanitizeInput($data['content']),
                $recordDate
            );
      
            Response::success(null, '添加成功');
        } catch (Exception $e) {
            Logger::error("创建记录失败", ['error' => $e->getMessage()]);
            Response::error('添加失败');
        }
    }
}
```

## 2. 安全实现

### 2.1 权限控制
```php
private function checkAuth($requireAdmin = false) {
    $headers = getallheaders();
    $authHeader = null;
  
    // 获取认证头
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = $value;
            break;
        }
    }
  
    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
        Response::error('未登录', 401);
        exit;
    }

    $token = substr($authHeader, 7);
    $payload = JWT::verify($token);
  
    if (!$payload) {
        Response::error('登录已过期', 401);
        exit;
    }

    // 检查管理员权限
    if ($requireAdmin && (!isset($payload['role']) || $payload['role'] !== 'admin')) {
        Response::error('无权访问', 403);
        exit;
    }

    return $payload;
}
```

### 2.2 XSS 防护
```php
class Security {
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}
```

## 3. 数据库设计

### 3.1 表结构
```sql
-- 用户表
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 记录表
CREATE TABLE records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    record_date DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 3.2 索引优化
```sql
-- 用户表索引
CREATE INDEX idx_username ON users(username);

-- 记录表索引
CREATE INDEX idx_user_date ON records(user_id, record_date);
```

## 4. API 接口

### 4.1 用户认证
```
POST /auth/login
Content-Type: application/json

Request:
{
    "username": "admin",
    "password": "admin123"
}

Response:
{
    "code": 200,
    "message": "登录成功",
    "data": {
        "user_id": 1,
        "username": "admin",
        "role": "admin",
        "token": "eyJhbGciOiJIUzI1NiJ9..."
    }
}
```

### 4.2 记录管理
```
GET /records
Authorization: Bearer {token}

Response:
{
    "code": 200,
    "data": [
        {
            "id": 1,
            "user_id": 2,
            "content": "测试内容",
            "record_date": "2024-03-07 14:00:00",
            "created_at": "2024-03-07 14:00:00",
            "username": "demo123456"
        }
    ]
}
```

## 5. 错误处理

### 5.1 统一响应格式
```php
class Response {
    public static function success($data = null, $message = '操作成功') {
        echo json_encode([
            'code' => 200,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    }

    public static function error($message = '操作失败', $code = 400) {
        http_response_code($code);
        echo json_encode([
            'code' => $code,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
    }
}
```

### 5.2 错误日志
```php
class Logger {
    public static function error($message, $context = []) {
        error_log(sprintf(
            "[ERROR] %s %s",
            $message,
            json_encode($context, JSON_UNESCAPED_UNICODE)
        ));
    }
}
```

## 6. 性能优化

### 6.1 数据库连接池
使用单例模式管理数据库连接，避免重复创建连接。

### 6.2 查询优化
1. 使用适当的索引
2. 避免 SELECT *
3. 使用预处理语句
4. 实现数据库事务

### 6.3 缓存策略
可以考虑使用 Redis 缓存热点数据：
1. 用户信息缓存
2. 记录列表缓存
3. 统计数据缓存

## 7. 部署说明

### 7.1 环境要求
- PHP >= 7.4
- MySQL >= 5.7
- Redis >= 4.0（可选）

### 7.2 配置说明
1. 复制并修改配置文件
2. 设置数据库连接信息
3. 配置 JWT 密钥
4. 设置日志路径
5. 配置跨域允许域名

### 7.3 安装步骤
1. 克隆代码
2. 安装依赖
3. 初始化数据库
4. 配置 Web 服务器
````

这个版本的文档更加注重技术实现细节，包括：

1. 完整的代码示例
2. 核心功能实现
3. 安全措施
4. 数据库优化
5. API 接口说明
6. 错误处理机制
7. 性能优化建议

需要我为某个部分提供更详细的说明吗？


# 记录系统技术实现文档

## 1. 系统架构

### 1.1 目录结构

```markdown:new.md
project/
├── config/                 # 配置文件
│   ├── config.php         # 系统配置
│   └── database.php       # 数据库配置
├── controllers/           # 控制器
│   ├── AuthController.php # 认证控制器
│   └── RecordController.php # 记录控制器
├── models/               # 模型
│   ├── User.php         # 用户模型
│   └── Record.php       # 记录模型
├── utils/               # 工具类
│   ├── JWT.php         # JWT工具
│   ├── Database.php    # 数据库连接
│   ├── Response.php    # 响应处理
│   ├── Validator.php   # 数据验证
│   ├── Logger.php      # 日志处理
│   └── Security.php    # 安全处理
└── index.php           # 入口文件
```


## 2. 核心模块实现

### 2.1 数据库连接（Database.php）

```
```php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        $this->conn = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
            $config['username'],
            $config['password']
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
}
```

### 2.2 JWT认证实现（JWT.php）

```php
class JWT {
    private static function getKey() {
        $config = require __DIR__ . '/../config/config.php';
        return $config['jwt_secret'];
    }

    public static function generate($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(array_merge($data, [
            'exp' => time() + self::getExpireTime(),
            'iat' => time()
        ]));

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);
        $signature = hash_hmac('sha256', 
            $base64Header . "." . $base64Payload, 
            self::getKey(), 
            true
        );

        return $base64Header . "." . $base64Payload . "." . self::base64UrlEncode($signature);
    }
}
```

### 2.3 记录控制器（RecordController.php）

```php
class RecordController {
    private $recordModel;

    public function __construct() {
        $this->recordModel = new Record();
    }

    public function getList() {
        $user = $this->checkAuth();
      
        try {
            // 根据用户角色返回不同数据
            if (isset($user['role']) && $user['role'] === 'admin') {
                $records = $this->recordModel->getAll();
            } else {
                $records = $this->recordModel->getByUserId($user['user_id']);
            }
          
            Response::success($records);
        } catch (Exception $e) {
            Logger::error("获取记录失败", ['error' => $e->getMessage()]);
            Response::error('获取记录失败');
        }
    }
}
```

## 3. 数据模型实现

### 3.1 记录模型（Record.php）

```php
class Record {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $content, $recordDate) {
        try {
            $this->beginTransaction();
          
            $stmt = $this->db->prepare(
                "INSERT INTO records (user_id, content, record_date) 
                 VALUES (?, ?, ?)"
            );
            $result = $stmt->execute([$userId, $content, $recordDate]);
          
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
```

### 3.2 用户模型（User.php）

```php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE username = ?"
        );
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}
```

## 4. 工具类实现

### 4.1 响应处理（Response.php）

```php
class Response {
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
```

### 4.2 数据验证（Validator.php）

```php
class Validator {
    public static function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            if (isset($rule['required']) && $rule['required'] && !isset($data[$field])) {
                $errors[$field] = '字段不能为空';
            }
        }
        return $errors;
    }
}
```

## 5. 安全实现

### 5.1 认证中间件

```php
private function checkAuth($requireAdmin = false) {
    $headers = getallheaders();
    $authHeader = null;
  
    // 获取认证头
    foreach ($headers as $key => $value) {
        if (strtolower($key) === 'authorization') {
            $authHeader = $value;
            break;
        }
    }
  
    if (!$authHeader) {
        Response::error('未登录', 401);
        exit;
    }

    // 验证token
    $token = substr($authHeader, 7);
    $payload = JWT::verify($token);
  
    if (!$payload) {
        Response::error('登录已过期', 401);
        exit;
    }

    // 检查管理员权限
    if ($requireAdmin && (!isset($payload['role']) || $payload['role'] !== 'admin')) {
        Response::error('无权访问', 403);
        exit;
    }

    return $payload;
}
```

### 5.2 输入安全处理

```php
class Security {
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}
```

## 6. 性能优化

### 6.1 数据库连接池

- 使用单例模式管理数据库连接
- 实现数据库事务支持

### 6.2 缓存实现

```php
class Cache {
    private static $instance = null;
    private $redis;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get($key) {
        return $this->redis->get($key);
    }

    public function set($key, $value, $ttl = 3600) {
        return $this->redis->setex($key, $ttl, $value);
    }
}
```

## 7. 错误处理

### 7.1 日志记录

```php
class Logger {
    public static function error($message, $context = []) {
        error_log(sprintf(
            "[ERROR] %s %s",
            $message,
            json_encode($context)
        ));
    }
}
```

### 7.2 异常处理

```php
try {
    // 业务逻辑
} catch (PDOException $e) {
    Logger::error("数据库错误", ['error' => $e->getMessage()]);
    Response::error('服务器错误', 500);
} catch (Exception $e) {
    Logger::error("系统错误", ['error' => $e->getMessage()]);
    Response::error('服务器错误', 500);
}
```

## 8. 配置管理

### 8.1 系统配置（config.php）

```php
return [
    'jwt_secret' => 'your_secret_key_here',
    'jwt_expire' => 7200,
    'upload_path' => __DIR__ . '/../uploads/',
    'allowed_origins' => [
        'http://localhost:3000'
    ]
];
```

### 8.2 数据库配置（database.php）

```php
return [
    'host' => getenv('DB_HOST'),
    'port' => getenv('DB_PORT'),
    'dbname' => getenv('DB_NAME'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASS'),
    'charset' => 'utf8mb4'
];
```

## 9. 部署说明

### 9.1 环境要求

- PHP >= 7.4
- MySQL >= 5.7
- Redis >= 4.0

### 9.2 必要的PHP扩展

- PDO
- JSON
- Redis
- mbstring

### 9.3 安装步骤

1. 克隆代码
2. 配置环境变量
3. 初始化数据库
4. 配置Web服务器

## 10. 测试

### 10.1 API测试

使用 Apifox 或 Postman 进行接口测试：

```bash
# 登录测试
curl -X POST http://localhost:3000/auth/login \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"admin123"}'

# 获取记录列表
curl -X GET http://localhost:3000/records \
     -H "Authorization: Bearer {token}"
```

```

这个技术文档更详细地展示了系统的实现细节，包括：
1. 完整的代码示例
2. 详细的实现逻辑
3. 各个模块的具体功能
4. 安全性考虑
5. 性能优化方案
6. 部署和测试说明

需要我为某个部分提供更多细节吗？
```

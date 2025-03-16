<!DOCTYPE html>
<html>
<head>
    <title>API 文档</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .endpoint {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .method {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            color: white;
            font-weight: bold;
        }
        .get { background-color: #61affe; }
        .post { background-color: #49cc90; }
        .put { background-color: #fca130; }
        .delete { background-color: #f93e3e; }
        .response {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
        }
        pre {
            margin: 0;
            white-space: pre-wrap;
        }
        h2 { color: #333; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>API 文档</h1>
    
    <div class="endpoint">
        <h2>用户注册</h2>
        <span class="method post">POST</span> /auth/register
        
        <h3>请求参数</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>username</td>
                <td>string</td>
                <td>是</td>
                <td>用户名</td>
            </tr>
            <tr>
                <td>password</td>
                <td>string</td>
                <td>是</td>
                <td>密码</td>
            </tr>
            <tr>
                <td>email</td>
                <td>string</td>
                <td>是</td>
                <td>邮箱</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "message": "注册成功"
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h2>用户登录</h2>
        <span class="method post">POST</span> /auth/login
        
        <h3>请求参数</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>username</td>
                <td>string</td>
                <td>是</td>
                <td>用户名</td>
            </tr>
            <tr>
                <td>password</td>
                <td>string</td>
                <td>是</td>
                <td>密码</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "message": "登录成功",
    "data": {
        "user_id": 1,
        "username": "testuser",
        "email": "test@example.com",
        "token": "eyJhbGciOiJIUzI1NiJ9..."  // JWT token，用于后续请求的认证
    }
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h2>获取记录列表</h2>
        <span class="method get">GET</span> /records
        
        <h3>请求参数</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>user_id</td>
                <td>integer</td>
                <td>否</td>
                <td>用户ID，不传则获取所有记录</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "message": "获取成功",
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "content": "记录内容",
            "record_date": "2024-03-20",
            "created_at": "2024-03-20 10:00:00"
        }
    ]
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h2>创建记录</h2>
        <span class="method post">POST</span> /records
        
        <h3>请求参数</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>content</td>
                <td>string</td>
                <td>是</td>
                <td>记录内容</td>
            </tr>
            <tr>
                <td>record_date</td>
                <td>datetime</td>
                <td>否</td>
                <td>记录日期时间，格式：YYYY-MM-DD HH:MM:SS，不提供则使用当前时间</td>
            </tr>
        </table>

        <h3>请求头</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>Authorization</td>
                <td>是</td>
                <td>格式为 "Bearer {jwt_token}"，其中 jwt_token 是登录接口返回的 token</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "message": "创建成功",
    "data": {
        "id": 1
    }
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h2>更新记录</h2>
        <span class="method put">PUT</span> /records
        
        <h3>请求参数</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>id</td>
                <td>integer</td>
                <td>是</td>
                <td>记录ID</td>
            </tr>
            <tr>
                <td>content</td>
                <td>string</td>
                <td>是</td>
                <td>记录内容</td>
            </tr>
            <tr>
                <td>record_date</td>
                <td>datetime</td>
                <td>否</td>
                <td>记录日期时间，格式：YYYY-MM-DD HH:MM:SS，不提供则使用当前时间</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "message": "更新成功"
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h2>删除记录</h2>
        <span class="method delete">DELETE</span> /records
        
        <h3>请求参数</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>id</td>
                <td>integer</td>
                <td>是</td>
                <td>记录ID</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "message": "删除成功"
}
            </pre>
        </div>
    </div>

    <div class="endpoint">
        <h2>获取操作日志</h2>
        <span class="method get">GET</span> /logs
        
        <h3>接口说明</h3>
        <p>获取系统操作日志，需要管理员权限</p>

        <h3>请求参数（Query Parameters）</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>类型</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>user_id</td>
                <td>integer</td>
                <td>否</td>
                <td>按用户ID筛选日志</td>
            </tr>
            <tr>
                <td>action</td>
                <td>string</td>
                <td>否</td>
                <td>按操作类型筛选（create/update/delete）</td>
            </tr>
        </table>

        <h3>请求头</h3>
        <table>
            <tr>
                <th>参数名</th>
                <th>必填</th>
                <th>说明</th>
            </tr>
            <tr>
                <td>Authorization</td>
                <td>是</td>
                <td>格式为 "Bearer {jwt_token}"，需要管理员token</td>
            </tr>
        </table>

        <h3>响应示例</h3>
        <div class="response">
            <pre>
{
    "code": 200,
    "data": [
        {
            "id": 1,
            "user_id": 2,
            "username": "demo123456",
            "action": "create",
            "target_type": "record",
            "target_id": 5,
            "content": "这是一条记录内容",
            "created_at": "2024-03-07 14:30:00"
        },
        {
            "id": 2,
            "user_id": 1,
            "username": "admin",
            "action": "delete",
            "target_type": "record",
            "target_id": 3,
            "content": "{\"id\":3,\"user_id\":2,\"content\":\"被删除的记录内容\",\"record_date\":\"2024-03-07\"}",
            "created_at": "2024-03-07 14:25:00"
        }
    ]
}
            </pre>
        </div>

        <h3>错误响应</h3>
        <div class="response">
            <pre>
// 未登录
{
    "code": 401,
    "message": "未登录"
}

// 无权限（非管理员）
{
    "code": 403,
    "message": "无权访问"
}

// 服务器错误
{
    "code": 500,
    "message": "获取日志失败"
}
            </pre>
        </div>

        <h3>使用示例</h3>
        <pre>
// 获取所有日志
GET /logs

// 获取指定用户的日志
GET /logs?user_id=1

// 获取指定操作类型的日志
GET /logs?action=create

// 组合查询
GET /logs?user_id=1&action=delete
        </pre>
    </div>

</body>
</html>
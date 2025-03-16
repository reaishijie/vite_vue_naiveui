<?php
require_once __DIR__ . '/../utils/Database.php';

class OperationLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $username, $action, $targetType, $targetId, $content = null) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO operation_logs (user_id, username, action, target_type, target_id, content) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            return $stmt->execute([$userId, $username, $action, $targetType, $targetId, $content]);
        } catch (Exception $e) {
            error_log("记录日志失败: " . $e->getMessage());
            // 日志记录失败不应影响主要业务
            return false;
        }
    }

    public function getList($filters = []) {
        $sql = "SELECT * FROM operation_logs WHERE 1=1";
        $params = [];

        if (isset($filters['user_id'])) {
            $sql .= " AND user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (isset($filters['action'])) {
            $sql .= " AND action = ?";
            $params[] = $filters['action'];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
} 
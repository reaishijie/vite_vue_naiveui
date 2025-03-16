<?php
require_once __DIR__ . '/../utils/Database.php';

class Record {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $content, $recordDate) {
        try {
            $stmt = $this->db->prepare("INSERT INTO records (user_id, content, record_date) VALUES (?, ?, ?)");
            $result = $stmt->execute([$userId, $content, $recordDate]);
            
            if ($result) {
                return $this->db->lastInsertId();  // 直接返回新插入的ID
            }
            return false;
        } catch (PDOException $e) {
            error_log("创建记录失败: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM records WHERE user_id = ? ORDER BY record_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function update($id, $content, $recordDate) {
        $stmt = $this->db->prepare("UPDATE records SET content = ?, record_date = ? WHERE id = ?");
        return $stmt->execute([$content, $recordDate, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM records WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        try {
            $sql = "SELECT r.*, u.username FROM records r JOIN users u ON r.user_id = u.id ORDER BY r.record_date DESC";
            error_log("执行SQL: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetchAll();
            error_log("SQL结果: " . json_encode([
                'count' => count($result),
                'first_record' => $result[0] ?? null
            ]));
            
            return $result;
        } catch (PDOException $e) {
            error_log("数据库错误: " . $e->getMessage());
            throw $e;
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT r.*, u.username FROM records r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.id = ?";
            error_log("执行SQL: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            $result = $stmt->fetch();
            error_log("SQL结果: " . json_encode($result));
            
            return $result;
        } catch (PDOException $e) {
            error_log("数据库错误: " . $e->getMessage());
            throw $e;
        }
    }

    public function beginTransaction() {
        $this->db->beginTransaction();
    }

    public function commit() {
        $this->db->commit();
    }

    public function rollback() {
        $this->db->rollBack();
    }
}
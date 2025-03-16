<?php
require_once __DIR__ . '/../utils/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($username, $password, $email) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashedPassword, $email]);
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function updateProfile($userId, $data) {
        $sql = "UPDATE users SET ";
        $params = [];
        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $sql .= "$key = ?, ";
                $params[] = $value;
            }
        }
        $sql = rtrim($sql, ", ") . " WHERE id = ?";
        $params[] = $userId;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
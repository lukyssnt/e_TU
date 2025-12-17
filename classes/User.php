<?php
require_once __DIR__ . '/../config/database.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all users
     */
    public function getAll()
    {
        if ($this->db === null)
            return [];
        $stmt = $this->db->query("SELECT id, username, full_name, role, permissions, created_at FROM users ORDER BY full_name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get user by ID
     */
    public function getById($id)
    {
        if ($this->db === null)
            return null;
        $stmt = $this->db->prepare("SELECT id, username, full_name, role, permissions FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create user
     */
    public function create($data)
    {
        if ($this->db === null)
            return false;

        // Check if username exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$data['username']]);
        if ($stmt->fetchColumn() > 0) {
            return 'Username sudah digunakan!';
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO users (username, password, full_name, role, permissions)
            VALUES (?, ?, ?, ?, ?)
        ");

        if (
            $stmt->execute([
                $data['username'],
                $passwordHash,
                $data['full_name'],
                $data['role'],
                $data['permissions']
            ])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Update user
     */
    public function update($id, $data)
    {
        if ($this->db === null)
            return false;

        // Check if username exists for other user
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $id]);
        if ($stmt->fetchColumn() > 0) {
            return 'Username sudah digunakan!';
        }

        $sql = "UPDATE users SET username = ?, full_name = ?, role = ?, permissions = ?";
        $params = [
            $data['username'],
            $data['full_name'],
            $data['role'],
            $data['permissions']
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        if ($this->db === null)
            return false;

        // Prevent deleting self (check session if possible, but here just basic check)
        // Also prevent deleting the last admin if needed, but for now simple delete

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get stats
     */
    public function getStats()
    {
        if ($this->db === null)
            return ['total' => 0, 'admin' => 0];

        $stats = [];
        $stats['total'] = $this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['admin'] = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'Administrator'")->fetchColumn();

        return $stats;
    }
    /**
     * Update user profile (Self Edit)
     * Securely updates only allowed fields
     */
    public function updateProfile($id, $data)
    {
        if ($this->db === null)
            return false;

        // Check if username exists for other user
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$data['username'], $id]);
        if ($stmt->fetchColumn() > 0) {
            return 'Username sudah digunakan!';
        }

        $sql = "UPDATE users SET username = ?, full_name = ?";
        $params = [
            $data['username'],
            $data['full_name']
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $sql .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}

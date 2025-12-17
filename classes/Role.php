<?php
require_once __DIR__ . '/../config/database.php';

class Role
{
    private $db;
    private $table = 'roles';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all roles
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY role_name ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get role by ID
     */
    public function getById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get role by Name
     */
    public function getByName($name)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE role_name = ?");
            $stmt->execute([$name]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create new role
     */
    public function create($data)
    {
        try {
            $query = "INSERT INTO {$this->table} (role_name, permissions, description) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);

            // Ensure permissions is JSON
            $permissions = is_array($data['permissions']) ? json_encode($data['permissions']) : $data['permissions'];

            return $stmt->execute([
                $data['role_name'],
                $permissions,
                $data['description']
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error creating role: " . $e->getMessage());
        }
    }

    /**
     * Update role
     */
    public function update($id, $data)
    {
        try {
            $query = "UPDATE {$this->table} SET role_name = ?, permissions = ?, description = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);

            // Ensure permissions is JSON
            $permissions = is_array($data['permissions']) ? json_encode($data['permissions']) : $data['permissions'];

            return $stmt->execute([
                $data['role_name'],
                $permissions,
                $data['description'],
                $id
            ]);
        } catch (PDOException $e) {
            throw new Exception("Error updating role: " . $e->getMessage());
        }
    }

    /**
     * Delete role
     */
    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new Exception("Error deleting role: " . $e->getMessage());
        }
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table}");
        return $stmt->fetchColumn();
    }
}

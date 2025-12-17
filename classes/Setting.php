<?php
require_once __DIR__ . '/../config/database.php';

class Setting
{
    private $db;
    private $table = 'settings';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all settings
     */
    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get setting by key
     */
    public function get($key, $default = null)
    {
        try {
            $stmt = $this->db->prepare("SELECT setting_value FROM {$this->table} WHERE setting_key = ?");
            $stmt->execute([$key]);
            $result = $stmt->fetch();
            return $result ? $result['setting_value'] : $default;
        } catch (PDOException $e) {
            return $default;
        }
    }

    /**
     * Update setting
     */
    public function update($key, $value)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET setting_value = ? WHERE setting_key = ?");
            return $stmt->execute([$value, $key]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update multiple settings
     */
    public function updateBatch($data)
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("UPDATE {$this->table} SET setting_value = ? WHERE setting_key = ?");

            foreach ($data as $key => $value) {
                // Skip if key doesn't exist in settings table (optional check)
                $stmt->execute([$value, $key]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
}

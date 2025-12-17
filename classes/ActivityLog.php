<?php
require_once __DIR__ . '/../config/database.php';

class ActivityLog
{
    private $db;
    private $table = 'activity_logs';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Log an activity
     */
    public function log($userId, $action, $module, $description = null)
    {
        try {
            $query = "INSERT INTO {$this->table} (user_id, action, module, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);

            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

            return $stmt->execute([
                $userId,
                $action,
                $module,
                $description,
                $ip,
                $userAgent
            ]);
        } catch (PDOException $e) {
            // Silently fail for logs to not disrupt main flow
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all logs with user info
     */
    public function getAll($limit = 100)
    {
        try {
            $query = "SELECT l.*, u.full_name, u.username 
                      FROM {$this->table} l 
                      LEFT JOIN users u ON l.user_id = u.id 
                      ORDER BY l.created_at DESC 
                      LIMIT " . (int) $limit;

            $stmt = $this->db->query($query);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get logs by user
     */
    public function getByUser($userId, $limit = 50)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC LIMIT " . (int) $limit;
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
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

    /**
     * Get recent count (this month)
     */
    public function getMonthCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM {$this->table} WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        return $stmt->fetchColumn();
    }
}

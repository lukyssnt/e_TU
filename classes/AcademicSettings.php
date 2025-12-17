<?php
/**
 * Academic Settings Class
 * Manages academic year, semester, and related settings
 */

require_once __DIR__ . '/../config/database.php';

class AcademicSettings
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get setting value by key
     */
    public function get($key)
    {
        $stmt = $this->db->prepare("SELECT setting_value, setting_type FROM academic_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result)
            return null;

        // Convert based on type
        switch ($result['setting_type']) {
            case 'number':
                return (int) $result['setting_value'];
            case 'boolean':
                return (bool) $result['setting_value'];
            default:
                return $result['setting_value'];
        }
    }

    /**
     * Set setting value
     */
    public function set($key, $value)
    {
        $stmt = $this->db->prepare("UPDATE academic_settings SET setting_value = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $key]);
    }

    /**
     * Get all settings
     */
    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM academic_settings ORDER BY setting_key");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get current academic year (e.g., "2024/2025")
     */
    public function getCurrentAcademicYear()
    {
        return $this->get('current_academic_year') ?? date('Y') . '/' . (date('Y') + 1);
    }

    /**
     * Get current semester (1 or 2)
     */
    public function getCurrentSemester()
    {
        return $this->get('semester') ?? 1;
    }

    /**
     * Check if should show previous year debts
     */
    public function shouldShowPreviousDebts()
    {
        return (bool) $this->get('show_previous_debts');
    }

    /**
     * Get academic year start date
     */
    public function getAcademicYearStart()
    {
        return $this->get('academic_year_start');
    }

    /**
     * Get academic year end date
     */
    public function getAcademicYearEnd()
    {
        return $this->get('academic_year_end');
    }

    /**
     * Generate academic year options for select dropdown
     * Now fetches from tahun_ajaran table instead of hardcoded
     */
    public function getAcademicYearOptions()
    {
        try {
            $stmt = $this->db->query("
                SELECT tahun_ajaran 
                FROM tahun_ajaran 
                ORDER BY tahun_ajaran DESC
            ");
            $years = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // If we got data, return it
            if (!empty($years)) {
                return $years;
            }
        } catch (PDOException $e) {
            // Table doesn't exist yet or query failed - use fallback
        }

        // Fallback: Generate hardcoded years
        $currentYear = (int) date('Y');
        $years = [];
        for ($i = 5; $i >= -2; $i--) {
            $year = $currentYear - $i;
            $nextYear = $year + 1;
            $years[] = "$year/$nextYear";
        }

        return $years;
    }
}

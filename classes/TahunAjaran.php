<?php
/**
 * Tahun Ajaran (Academic Year) Management Class
 * Handles CRUD operations with validation for academic year management
 */

require_once __DIR__ . '/../config/database.php';

class TahunAjaran
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all academic years ordered by year DESC
     */
    public function getAll()
    {
        $stmt = $this->db->query("
            SELECT * FROM tahun_ajaran 
            ORDER BY tahun_ajaran DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active academic year
     */
    public function getActive()
    {
        $stmt = $this->db->query("
            SELECT * FROM tahun_ajaran 
            WHERE is_active = 1 
            LIMIT 1
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get academic year by ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM tahun_ajaran 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create new academic year
     */
    public function create($tahunAjaran, $tanggalMulai, $tanggalAkhir)
    {
        // Validation: Check format YYYY/YYYY+1
        if (!$this->validateYearFormat($tahunAjaran)) {
            throw new Exception("Format tahun ajaran harus YYYY/YYYY (contoh: 2024/2025)");
        }

        // Check if already exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tahun_ajaran WHERE tahun_ajaran = ?");
        $stmt->execute([$tahunAjaran]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Tahun ajaran $tahunAjaran sudah ada");
        }

        // Insert
        $stmt = $this->db->prepare("
            INSERT INTO tahun_ajaran (tahun_ajaran, is_active, tanggal_mulai, tanggal_akhir)
            VALUES (?, 0, ?, ?)
        ");

        return $stmt->execute([$tahunAjaran, $tanggalMulai, $tanggalAkhir]);
    }

    /**
     * Set academic year as active (deactivate others + sync settings)
     */
    public function setActive($id)
    {
        $this->db->beginTransaction();

        try {
            // 1. Deactivate all
            $this->db->exec("UPDATE tahun_ajaran SET is_active = 0");

            // 2. Activate target
            $stmt = $this->db->prepare("UPDATE tahun_ajaran SET is_active = 1 WHERE id = ?");
            $stmt->execute([$id]);

            // 3. Sync to academic_settings
            $year = $this->getById($id);
            if ($year) {
                require_once __DIR__ . '/AcademicSettings.php';
                $academicSettings = new AcademicSettings();
                $academicSettings->set('current_academic_year', $year['tahun_ajaran']);
                $academicSettings->set('academic_year_start', $year['tanggal_mulai']);
                $academicSettings->set('academic_year_end', $year['tanggal_akhir']);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete academic year (with validation)
     */
    public function delete($id)
    {
        // Check if can delete
        $check = $this->canDelete($id);
        if (!$check['can_delete']) {
            throw new Exception($check['reason']);
        }

        $stmt = $this->db->prepare("DELETE FROM tahun_ajaran WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Check if academic year can be deleted
     */
    public function canDelete($id)
    {
        $year = $this->getById($id);

        if (!$year) {
            return ['can_delete' => false, 'reason' => 'Tahun ajaran tidak ditemukan'];
        }

        // Check 1: Is active?
        if ($year['is_active']) {
            return ['can_delete' => false, 'reason' => 'Tidak bisa menghapus tahun ajaran yang sedang aktif'];
        }

        // Check 2: Used in kelas?
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM kelas WHERE tahun_ajaran = ?
        ");
        $stmt->execute([$year['tahun_ajaran']]);
        $kelasCount = $stmt->fetchColumn();

        if ($kelasCount > 0) {
            return ['can_delete' => false, 'reason' => "Masih ada $kelasCount kelas yang menggunakan tahun ajaran ini"];
        }

        // Check 3: Used in siswa (based on tahun_masuk matching year range)
        // Extract years from "2024/2025" format
        $years = explode('/', $year['tahun_ajaran']);
        if (count($years) == 2) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM siswa 
                WHERE tahun_masuk IN (?, ?) AND status = 'Aktif'
            ");
            $stmt->execute([$years[0], $years[1]]);
            $siswaCount = $stmt->fetchColumn();

            if ($siswaCount > 0) {
                return ['can_delete' => false, 'reason' => "Masih ada $siswaCount siswa aktif dari tahun ajaran ini"];
            }
        }

        return ['can_delete' => true];
    }

    /**
     * Get usage statistics for academic year
     */
    public function getUsageStats($tahunAjaran)
    {
        // Count kelas
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM kelas WHERE tahun_ajaran = ?");
        $stmt->execute([$tahunAjaran]);
        $kelasCount = $stmt->fetchColumn();

        // Count siswa based on tahun_masuk
        $years = explode('/', $tahunAjaran);
        $siswaCount = 0;
        if (count($years) == 2) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM siswa 
                WHERE tahun_masuk IN (?, ?) AND status = 'Aktif'
            ");
            $stmt->execute([$years[0], $years[1]]);
            $siswaCount = $stmt->fetchColumn();
        }

        return [
            'kelas' => $kelasCount,
            'siswa' => $siswaCount
        ];
    }

    /**
     * Validate year format YYYY/YYYY
     */
    private function validateYearFormat($tahunAjaran)
    {
        // Format: 2024/2025
        if (!preg_match('/^\d{4}\/\d{4}$/', $tahunAjaran)) {
            return false;
        }

        // Check if second year = first year + 1
        $years = explode('/', $tahunAjaran);
        if (count($years) != 2) {
            return false;
        }

        $year1 = (int) $years[0];
        $year2 = (int) $years[1];

        return ($year2 == $year1 + 1);
    }

    /**
     * Get all years for dropdown (ordered DESC)
     */
    public function getAllForDropdown()
    {
        $stmt = $this->db->query("
            SELECT tahun_ajaran FROM tahun_ajaran 
            ORDER BY tahun_ajaran DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

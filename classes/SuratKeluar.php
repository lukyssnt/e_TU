<?php
require_once __DIR__ . '/../config/database.php';

class SuratKeluar
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all surat keluar
     */
    public function getAll($limit = null, $offset = 0)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT sk.*, u.username as created_by_name, t.nama_template
                FROM surat_keluar sk
                LEFT JOIN users u ON sk.created_by = u.id
                LEFT JOIN template_surat t ON sk.template_id = t.id
                ORDER BY sk.tanggal_surat DESC, sk.id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get by ID
     */
    public function getById($id)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT sk.*, u.username as created_by_name, t.nama_template
            FROM surat_keluar sk
            LEFT JOIN users u ON sk.created_by = u.id
            LEFT JOIN template_surat t ON sk.template_id = t.id
            WHERE sk.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new surat keluar
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO surat_keluar 
            (nomor_surat, tanggal_surat, tujuan, perihal, template_id, file_surat, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nomor_surat'],
            $data['tanggal_surat'],
            $data['tujuan'],
            $data['perihal'],
            $data['template_id'] ?? null,
            $data['file_surat'] ?? null,
            $data['created_by'] ?? null
        ]);
    }

    /**
     * Update surat keluar
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE surat_keluar SET 
            nomor_surat = ?, 
            tanggal_surat = ?, 
            tujuan = ?,
            perihal = ?,
            template_id = ?,
            file_surat = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nomor_surat'],
            $data['tanggal_surat'],
            $data['tujuan'],
            $data['perihal'],
            $data['template_id'] ?? null,
            $data['file_surat'] ?? null,
            $id
        ]);
    }

    /**
     * Delete surat keluar
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        // Get file path first
        $surat = $this->getById($id);
        if ($surat && $surat['file_surat']) {
            $filePath = __DIR__ . '/../uploads/surat-keluar/' . $surat['file_surat'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM surat_keluar WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search surat keluar
     */
    public function search($keyword)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT sk.*, u.username as created_by_name 
            FROM surat_keluar sk
            LEFT JOIN users u ON sk.created_by = u.id
            WHERE sk.nomor_surat LIKE ? 
            OR sk.tujuan LIKE ? 
            OR sk.perihal LIKE ?
            ORDER BY sk.tanggal_surat DESC
        ");

        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM surat_keluar");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get count this month
     */
    public function getCountThisMonth()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("
            SELECT COUNT(*) as total FROM surat_keluar 
            WHERE MONTH(tanggal_surat) = MONTH(CURRENT_DATE()) 
            AND YEAR(tanggal_surat) = YEAR(CURRENT_DATE())
        ");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get surat by month
     */
    public function getByMonth($bulan, $tahun)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT sk.*, u.username as created_by_name 
            FROM surat_keluar sk
            LEFT JOIN users u ON sk.created_by = u.id
            WHERE MONTH(sk.tanggal_surat) = ? AND YEAR(sk.tanggal_surat) = ?
            ORDER BY sk.tanggal_surat DESC
        ");
        $stmt->execute([$bulan, $tahun]);
        return $stmt->fetchAll();
    }

    /**
     * Generate nomor surat
     */
    public function generateNomorSurat($kodeSurat = 'SK')
    {
        if ($this->db === null) {
            return $kodeSurat . '/001/' . date('m') . '/' . date('Y');
        }

        $stmt = $this->db->query("
            SELECT COUNT(*) as total FROM surat_keluar 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $result = $stmt->fetch();
        $urutan = ($result['total'] ?? 0) + 1;

        return sprintf(
            '%03d/%s/TU/%s/%s',
            $urutan,
            $kodeSurat,
            date('m'),
            date('Y')
        );
    }
}

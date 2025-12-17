<?php
require_once __DIR__ . '/../config/database.php';

class Arsip
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all arsip with filter
     */
    public function getAll($jenis = null, $tahun = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT a.*, u.username as created_by_name 
                FROM arsip_digital a
                LEFT JOIN users u ON a.created_by = u.id
                WHERE 1=1";

        $params = [];
        if ($jenis) {
            $sql .= " AND a.jenis = ?";
            $params[] = $jenis;
        }
        if ($tahun) {
            $sql .= " AND YEAR(a.tanggal_arsip) = ?";
            $params[] = $tahun;
        }

        $sql .= " ORDER BY a.tanggal_arsip DESC, a.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
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

        $stmt = $this->db->prepare("SELECT * FROM arsip_digital WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create arsip
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO arsip_digital 
            (kode_arsip, judul, deskripsi, jenis, kategori, tanggal_arsip, file_arsip, lokasi_fisik, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['kode_arsip'],
            $data['judul'],
            $data['deskripsi'] ?? null,
            $data['jenis'],
            $data['kategori'] ?? null,
            $data['tanggal_arsip'],
            $data['file_arsip'] ?? null,
            $data['lokasi_fisik'] ?? null,
            $data['created_by'] ?? null
        ]);
    }

    /**
     * Update arsip
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE arsip_digital SET 
            kode_arsip = ?,
            judul = ?,
            deskripsi = ?,
            jenis = ?,
            kategori = ?,
            tanggal_arsip = ?,
            file_arsip = ?,
            lokasi_fisik = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['kode_arsip'],
            $data['judul'],
            $data['deskripsi'] ?? null,
            $data['jenis'],
            $data['kategori'] ?? null,
            $data['tanggal_arsip'],
            $data['file_arsip'] ?? null,
            $data['lokasi_fisik'] ?? null,
            $id
        ]);
    }

    /**
     * Delete arsip
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        // Get file path first
        $arsip = $this->getById($id);
        if ($arsip && $arsip['file_arsip']) {
            $filePath = __DIR__ . '/../uploads/arsip/' . $arsip['file_arsip'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM arsip_digital WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search arsip
     */
    public function search($keyword)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM arsip_digital 
            WHERE kode_arsip LIKE ? 
            OR judul LIKE ? 
            OR deskripsi LIKE ?
            OR kategori LIKE ?
            ORDER BY tanggal_arsip DESC
        ");

        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Get counts by jenis
     */
    public function getCountByJenis()
    {
        if ($this->db === null) {
            return ['Surat Masuk' => 0, 'Surat Keluar' => 0, 'Dokumen' => 0, 'Lainnya' => 0];
        }

        $stmt = $this->db->query("
            SELECT jenis, COUNT(*) as total 
            FROM arsip_digital 
            GROUP BY jenis
        ");

        $counts = ['Surat Masuk' => 0, 'Surat Keluar' => 0, 'Dokumen' => 0, 'Lainnya' => 0];
        while ($row = $stmt->fetch()) {
            $counts[$row['jenis']] = $row['total'];
        }

        return $counts;
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM arsip_digital");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get available years
     */
    public function getAvailableYears()
    {
        if ($this->db === null) {
            return [date('Y')];
        }

        $stmt = $this->db->query("
            SELECT DISTINCT YEAR(tanggal_arsip) as tahun 
            FROM arsip_digital 
            ORDER BY tahun DESC
        ");

        $years = [];
        while ($row = $stmt->fetch()) {
            $years[] = $row['tahun'];
        }

        return empty($years) ? [date('Y')] : $years;
    }

    /**
     * Generate kode arsip
     */
    public function generateKode($jenis = 'DOK')
    {
        if ($this->db === null) {
            return $jenis . '-' . date('Ymd') . '-001';
        }

        $prefix = [
            'Surat Masuk' => 'SM',
            'Surat Keluar' => 'SK',
            'Dokumen' => 'DOK',
            'Lainnya' => 'ARS'
        ][$jenis] ?? 'ARS';

        $stmt = $this->db->query("
            SELECT COUNT(*) as total FROM arsip_digital 
            WHERE DATE(created_at) = CURDATE()
        ");
        $result = $stmt->fetch();
        $urutan = ($result['total'] ?? 0) + 1;

        return sprintf('%s-%s-%03d', $prefix, date('Ymd'), $urutan);
    }
}

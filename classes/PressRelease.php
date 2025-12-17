<?php
require_once __DIR__ . '/../config/database.php';

class PressRelease
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all press releases
     */
    public function getAll($status = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT * FROM press_release WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

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

        $stmt = $this->db->prepare("SELECT * FROM press_release WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create press release
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO press_release 
            (judul, ringkasan, isi, kategori, tanggal_rilis, penulis, gambar, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['judul'],
            $data['ringkasan'] ?? null,
            $data['isi'],
            $data['kategori'] ?? 'Umum',
            $data['tanggal_rilis'],
            $data['penulis'] ?? null,
            $data['gambar'] ?? null,
            $data['status'] ?? 'Draft'
        ]);
    }

    /**
     * Update press release
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE press_release SET 
            judul = ?, 
            ringkasan = ?, 
            isi = ?, 
            kategori = ?,
            tanggal_rilis = ?,
            penulis = ?,
            gambar = ?,
            status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['judul'],
            $data['ringkasan'] ?? null,
            $data['isi'],
            $data['kategori'],
            $data['tanggal_rilis'],
            $data['penulis'] ?? null,
            $data['gambar'] ?? null,
            $data['status'],
            $id
        ]);
    }

    /**
     * Update status only
     */
    public function updateStatus($id, $status)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE press_release SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Delete press release
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        // Delete image if exists
        $pr = $this->getById($id);
        if ($pr && $pr['gambar']) {
            $filePath = __DIR__ . '/../' . $pr['gambar'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM press_release WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get status counts
     */
    public function getStatusCounts()
    {
        if ($this->db === null) {
            return ['Draft' => 0, 'Dipublikasi' => 0, 'Diarsipkan' => 0];
        }

        $stmt = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM press_release 
            GROUP BY status
        ");

        $counts = ['Draft' => 0, 'Dipublikasi' => 0, 'Diarsipkan' => 0];
        while ($row = $stmt->fetch()) {
            $counts[$row['status']] = $row['total'];
        }

        return $counts;
    }

    /**
     * Get published releases
     */
    public function getPublished($limit = 10)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM press_release 
            WHERE status = 'Dipublikasi'
            ORDER BY tanggal_rilis DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}

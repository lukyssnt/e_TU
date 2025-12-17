<?php
require_once __DIR__ . '/../config/database.php';

class Cuti
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all cuti requests
     */
    public function getAll()
    {
        if ($this->db === null)
            return [];

        $stmt = $this->db->query("
            SELECT c.*, p.nama_lengkap, p.nip, p.foto, j.nama_jabatan
            FROM cuti c
            JOIN pegawai p ON c.pegawai_id = p.id
            LEFT JOIN jabatan j ON p.jabatan_id = j.id
            ORDER BY c.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get cuti by ID
     */
    public function getById($id)
    {
        if ($this->db === null)
            return null;

        $stmt = $this->db->prepare("
            SELECT c.*, p.nama_lengkap, p.nip, j.nama_jabatan
            FROM cuti c
            JOIN pegawai p ON c.pegawai_id = p.id
            LEFT JOIN jabatan j ON p.jabatan_id = j.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new cuti request
     */
    public function create($data)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            INSERT INTO cuti (pegawai_id, jenis_cuti, tanggal_mulai, tanggal_selesai, jumlah_hari, keterangan, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'Menunggu', NOW())
        ");

        return $stmt->execute([
            $data['pegawai_id'],
            $data['jenis_cuti'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['jumlah_hari'],
            $data['keterangan']
        ]);
    }

    /**
     * Update cuti request
     */
    public function update($id, $data)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            UPDATE cuti SET 
            jenis_cuti = ?, tanggal_mulai = ?, tanggal_selesai = ?, 
            jumlah_hari = ?, keterangan = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['jenis_cuti'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai'],
            $data['jumlah_hari'],
            $data['keterangan'],
            $id
        ]);
    }

    /**
     * Update status (Approve/Reject)
     */
    public function updateStatus($id, $status, $userId = null, $alasan = null)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            UPDATE cuti SET 
            status = ?, disetujui_oleh = ?, tanggal_persetujuan = NOW(), alasan_penolakan = ?
            WHERE id = ?
        ");

        return $stmt->execute([$status, $userId, $alasan, $id]);
    }

    /**
     * Delete cuti request
     */
    public function delete($id)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("DELETE FROM cuti WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get stats
     */
    public function getStats()
    {
        if ($this->db === null)
            return ['menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0];

        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'Disetujui' THEN 1 ELSE 0 END) as disetujui,
                SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as ditolak
            FROM cuti
        ");
        return $stmt->fetch();
    }
}

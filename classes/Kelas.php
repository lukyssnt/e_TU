<?php
require_once __DIR__ . '/../config/database.php';

class Kelas
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all kelas, optionally filtered by academic year
     */
    public function getAll($tahunAjaran = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT * FROM kelas";
        $params = [];

        if ($tahunAjaran) {
            $sql .= " WHERE tahun_ajaran = ?";
            $params[] = $tahunAjaran;
        }

        $sql .= " ORDER BY nama_kelas ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get kelas by ID
     */
    public function getById($id)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM kelas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new kelas
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO kelas (nama_kelas, wali_kelas, tahun_ajaran, keterangan)
            VALUES (?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nama_kelas'],
            $data['wali_kelas'] ?? null,
            $data['tahun_ajaran'] ?? null,
            $data['keterangan'] ?? null
        ]);
    }

    /**
     * Update kelas
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE kelas SET 
            nama_kelas = ?, 
            wali_kelas = ?, 
            tahun_ajaran = ?, 
            keterangan = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nama_kelas'],
            $data['wali_kelas'] ?? null,
            $data['tahun_ajaran'] ?? null,
            $data['keterangan'] ?? null,
            $id
        ]);
    }

    /**
     * Delete kelas
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM kelas WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM kelas");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}

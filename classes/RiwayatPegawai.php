<?php
require_once __DIR__ . '/../config/database.php';

class RiwayatPegawai
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all history by pegawai ID
     */
    public function getByPegawai($pegawaiId)
    {
        if ($this->db === null)
            return [];

        $stmt = $this->db->prepare("
            SELECT * FROM riwayat_pegawai 
            WHERE pegawai_id = ? 
            ORDER BY tahun_mulai DESC, created_at DESC
        ");
        $stmt->execute([$pegawaiId]);
        return $stmt->fetchAll();
    }

    /**
     * Get history by ID
     */
    public function getById($id)
    {
        if ($this->db === null)
            return null;

        $stmt = $this->db->prepare("SELECT * FROM riwayat_pegawai WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new history record
     */
    public function create($data)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            INSERT INTO riwayat_pegawai 
            (pegawai_id, jenis_riwayat, judul, instansi_lokasi, tahun_mulai, tahun_selesai, nomor_sk, tanggal_sk, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['pegawai_id'],
            $data['jenis_riwayat'],
            $data['judul'],
            $data['instansi_lokasi'] ?? null,
            $data['tahun_mulai'] ?? null,
            $data['tahun_selesai'] ?? null,
            $data['nomor_sk'] ?? null,
            $data['tanggal_sk'] ?? null,
            $data['keterangan'] ?? null
        ]);
    }

    /**
     * Update history record
     */
    public function update($id, $data)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            UPDATE riwayat_pegawai SET 
            jenis_riwayat = ?, judul = ?, instansi_lokasi = ?, 
            tahun_mulai = ?, tahun_selesai = ?, nomor_sk = ?, 
            tanggal_sk = ?, keterangan = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['jenis_riwayat'],
            $data['judul'],
            $data['instansi_lokasi'] ?? null,
            $data['tahun_mulai'] ?? null,
            $data['tahun_selesai'] ?? null,
            $data['nomor_sk'] ?? null,
            $data['tanggal_sk'] ?? null,
            $data['keterangan'] ?? null,
            $id
        ]);
    }

    /**
     * Delete history record
     */
    public function delete($id)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("DELETE FROM riwayat_pegawai WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

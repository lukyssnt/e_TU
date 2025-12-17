<?php
require_once __DIR__ . '/../config/database.php';

class UKS
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all medicines
     */
    public function getAllObat()
    {
        if ($this->db === null)
            return [];
        $stmt = $this->db->query("SELECT * FROM obat ORDER BY nama_obat ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get medicine by ID
     */
    public function getObatById($id)
    {
        if ($this->db === null)
            return null;
        $stmt = $this->db->prepare("SELECT * FROM obat WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create medicine
     */
    public function createObat($data)
    {
        if ($this->db === null)
            return false;
        $stmt = $this->db->prepare("
            INSERT INTO obat (nama_obat, jenis, stok, satuan, keterangan)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['nama_obat'],
            $data['jenis'],
            $data['stok'],
            $data['satuan'],
            $data['keterangan'] ?? null
        ]);
    }

    /**
     * Update medicine
     */
    public function updateObat($id, $data)
    {
        if ($this->db === null)
            return false;
        $stmt = $this->db->prepare("
            UPDATE obat SET 
            nama_obat = ?, jenis = ?, stok = ?, satuan = ?, keterangan = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nama_obat'],
            $data['jenis'],
            $data['stok'],
            $data['satuan'],
            $data['keterangan'] ?? null,
            $id
        ]);
    }

    /**
     * Delete medicine
     */
    public function deleteObat($id)
    {
        if ($this->db === null)
            return false;
        $stmt = $this->db->prepare("DELETE FROM obat WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all patient records
     */
    public function getAllCatatan()
    {
        if ($this->db === null)
            return [];
        $stmt = $this->db->query("
            SELECT c.*, s.nama_lengkap as nama_siswa, s.nisn 
            FROM catatan_kesehatan c
            JOIN siswa s ON c.siswa_id = s.id
            ORDER BY c.tanggal_periksa DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Create patient record
     */
    public function createCatatan($data)
    {
        if ($this->db === null)
            return false;

        try {
            $this->db->beginTransaction();

            // Create record
            $stmt = $this->db->prepare("
                INSERT INTO catatan_kesehatan (siswa_id, tanggal_periksa, keluhan, diagnosa, tindakan, obat_diberikan)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['siswa_id'],
                $data['tanggal_periksa'],
                $data['keluhan'],
                $data['diagnosa'],
                $data['tindakan'],
                $data['obat_diberikan'] ?? null
            ]);

            // Decrease medicine stock if given
            if (!empty($data['obat_id'])) {
                $obatId = $data['obat_id'];
                $jumlah = (int) ($data['jumlah_obat'] ?? 1);

                if ($jumlah < 1)
                    $jumlah = 1;

                // Check stock
                $stmt = $this->db->prepare("SELECT stok, nama_obat FROM obat WHERE id = ?");
                $stmt->execute([$obatId]);
                $obat = $stmt->fetch();

                if (!$obat) {
                    throw new Exception("Obat tidak ditemukan");
                }

                if ($obat['stok'] < $jumlah) {
                    throw new Exception("Stok {$obat['nama_obat']} tidak mencukupi (Sisa: {$obat['stok']})");
                }

                $this->db->prepare("UPDATE obat SET stok = stok - ? WHERE id = ?")
                    ->execute([$jumlah, $obatId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            // Store error message in session or return it (simplified here)
            return false;
        }
    }

    /**
     * Get stats
     */
    public function getStats()
    {
        if ($this->db === null)
            return ['total_obat' => 0, 'pasien_bulan_ini' => 0];

        $stats = [];
        $stats['total_obat'] = $this->db->query("SELECT COUNT(*) FROM obat")->fetchColumn();
        $stats['pasien_bulan_ini'] = $this->db->query("
            SELECT COUNT(*) FROM catatan_kesehatan 
            WHERE MONTH(tanggal_periksa) = MONTH(CURRENT_DATE()) 
            AND YEAR(tanggal_periksa) = YEAR(CURRENT_DATE())
        ")->fetchColumn();

        return $stats;
    }
}

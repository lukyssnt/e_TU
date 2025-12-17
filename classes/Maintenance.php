<?php
require_once __DIR__ . '/../config/database.php';

class Maintenance
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        if ($this->db === null)
            return [];

        $stmt = $this->db->query("
            SELECT m.*, a.kode_aset, a.nama_barang, a.kategori, a.lokasi
            FROM maintenance m
            LEFT JOIN aset a ON m.aset_id = a.id
            ORDER BY m.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        if ($this->db === null)
            return null;

        $stmt = $this->db->prepare("
            SELECT m.*, a.kode_aset, a.nama_barang, a.kategori, a.lokasi
            FROM maintenance m
            LEFT JOIN aset a ON m.aset_id = a.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByAsetId($aset_id)
    {
        if ($this->db === null)
            return [];

        $stmt = $this->db->prepare("
            SELECT * FROM maintenance 
            WHERE aset_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$aset_id]);
        return $stmt->fetchAll();
    }

    public function getByStatus($status)
    {
        if ($this->db === null)
            return [];

        $stmt = $this->db->prepare("
            SELECT m.*, a.kode_aset, a.nama_barang
            FROM maintenance m
            LEFT JOIN aset a ON m.aset_id = a.id
            WHERE m.status = ?
            ORDER BY m.tanggal_maintenance DESC
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        if ($this->db === null)
            return false;

        // Generate kode maintenance
        $kode = $this->generateKodeMaintenance();

        $stmt = $this->db->prepare("
            INSERT INTO maintenance 
            (aset_id, kode_maintenance, tanggal_maintenance, jenis_maintenance, deskripsi, biaya, teknisi, status, keterangan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['aset_id'],
            $kode,
            $data['tanggal_maintenance'],
            $data['jenis_maintenance'],
            $data['deskripsi'] ?? null,
            $data['biaya'] ?? 0,
            $data['teknisi'] ?? null,
            $data['status'] ?? 'Proses',
            $data['keterangan'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            UPDATE maintenance SET 
            tanggal_maintenance = ?, jenis_maintenance = ?, deskripsi = ?, 
            biaya = ?, teknisi = ?, status = ?, keterangan = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['tanggal_maintenance'],
            $data['jenis_maintenance'],
            $data['deskripsi'],
            $data['biaya'],
            $data['teknisi'],
            $data['status'],
            $data['keterangan'],
            $id
        ]);
    }

    public function updateStatus($id, $status)
    {
        if ($this->db === null)
            return false;

        $tanggal_selesai = ($status === 'Selesai') ? date('Y-m-d') : null;

        $stmt = $this->db->prepare("
            UPDATE maintenance SET 
            status = ?, tanggal_selesai = ?
            WHERE id = ?
        ");

        return $stmt->execute([$status, $tanggal_selesai, $id]);
    }

    public function delete($id)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("DELETE FROM maintenance WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTotalBiaya()
    {
        if ($this->db === null)
            return 0;

        $stmt = $this->db->query("SELECT SUM(biaya) as total FROM maintenance");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getCountByMonth($month = null, $year = null)
    {
        if ($this->db === null)
            return 0;

        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM maintenance 
            WHERE MONTH(tanggal_maintenance) = ? AND YEAR(tanggal_maintenance) = ?
        ");
        $stmt->execute([$month, $year]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    private function generateKodeMaintenance()
    {
        $year = date('Y');
        $month = date('m');

        // Get last number for this month
        $stmt = $this->db->prepare("
            SELECT kode_maintenance 
            FROM maintenance 
            WHERE kode_maintenance LIKE ? 
            ORDER BY kode_maintenance DESC 
            LIMIT 1
        ");
        $stmt->execute(["MNT-$year$month-%"]);
        $last = $stmt->fetch();

        if ($last) {
            $lastNumber = (int) substr($last['kode_maintenance'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "MNT-$year$month-" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}

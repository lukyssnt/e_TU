<?php
require_once __DIR__ . '/../config/database.php';

class Aset
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all assets
    public function getAll()
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->query("SELECT * FROM aset ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get by ID
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM aset WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create Asset
    public function create($data)
    {
        $sql = "INSERT INTO aset (kode_aset, nama_barang, kategori, lokasi, kondisi, dapat_dipinjam, tanggal_perolehan, nilai_perolehan, keterangan) 
                VALUES (:kode, :nama, :kategori, :lokasi, :kondisi, :dapat_dipinjam, :tanggal, :nilai, :keterangan)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':kode' => $data['kode_aset'],
            ':nama' => $data['nama_barang'],
            ':kategori' => $data['kategori'],
            ':lokasi' => $data['lokasi'],
            ':kondisi' => $data['kondisi'],
            ':dapat_dipinjam' => $data['dapat_dipinjam'] ?? 'Ya',
            ':tanggal' => $data['tanggal_perolehan'],
            ':nilai' => $data['nilai_perolehan'],
            ':keterangan' => $data['keterangan']
        ]);
    }

    // Update Asset
    public function update($id, $data)
    {
        $sql = "UPDATE aset SET 
                kode_aset = :kode, 
                nama_barang = :nama, 
                kategori = :kategori, 
                lokasi = :lokasi, 
                kondisi = :kondisi, 
                dapat_dipinjam = :dapat_dipinjam,
                tanggal_perolehan = :tanggal, 
                nilai_perolehan = :nilai, 
                keterangan = :keterangan 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':kode' => $data['kode_aset'],
            ':nama' => $data['nama_barang'],
            ':kategori' => $data['kategori'],
            ':lokasi' => $data['lokasi'],
            ':kondisi' => $data['kondisi'],
            ':dapat_dipinjam' => $data['dapat_dipinjam'] ?? 'Ya',
            ':tanggal' => $data['tanggal_perolehan'],
            ':nilai' => $data['nilai_perolehan'],
            ':keterangan' => $data['keterangan'],
            ':id' => $id
        ]);
    }

    // Delete Asset
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM aset WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get Stats
    public function getStats()
    {
        $stats = [
            'total_items' => 0,
            'total_value' => 0,
            'dipinjam' => 0,
            'rusak' => 0
        ];

        // Total items
        $stmt = $this->db->query("SELECT COUNT(*) FROM aset");
        $stats['total_items'] = $stmt->fetchColumn();

        // Total value
        $stmt = $this->db->query("SELECT SUM(nilai_perolehan) FROM aset");
        $stats['total_value'] = $stmt->fetchColumn() ?: 0;

        // Rusak
        $stmt = $this->db->query("SELECT COUNT(*) FROM aset WHERE kondisi != 'Baik'");
        $stats['rusak'] = $stmt->fetchColumn();

        // Dipinjam (from peminjaman_aset)
        $stmt = $this->db->query("SELECT COUNT(*) FROM peminjaman_aset WHERE status = 'Dipinjam'");
        $stats['dipinjam'] = $stmt->fetchColumn();

        return $stats;
    }

    // --- Peminjaman Methods ---

    public function getAllPeminjaman()
    {
        $sql = "SELECT p.*, a.nama_barang, a.kode_aset 
                FROM peminjaman_aset p
                JOIN aset a ON p.aset_id = a.id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createPeminjaman($data)
    {
        $sql = "INSERT INTO peminjaman_aset (aset_id, nama_peminjam, no_hp, tanggal_pinjam, tanggal_kembali, keperluan, status) 
                VALUES (:aset, :nama, :hp, :tgl_pinjam, :tgl_kembali, :keperluan, 'Dipinjam')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aset' => $data['aset_id'],
            ':nama' => $data['nama_peminjam'],
            ':hp' => $data['no_hp'],
            ':tgl_pinjam' => $data['tanggal_pinjam'],
            ':tgl_kembali' => $data['tanggal_kembali'],
            ':keperluan' => $data['keperluan']
        ]);
    }

    public function returnAset($id, $tglKembali)
    {
        $sql = "UPDATE peminjaman_aset SET status = 'Dikembalikan', tanggal_dikembalikan = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tglKembali, $id]);
    }

    // Check if asset is available for borrowing
    public function isAvailableForBorrowing($id)
    {
        $aset = $this->getById($id);
        if (!$aset) {
            return ['available' => false, 'reason' => 'Aset tidak ditemukan'];
        }

        // Check if allowed to be borrowed
        if ($aset['dapat_dipinjam'] === 'Tidak') {
            return ['available' => false, 'reason' => 'Aset ini tidak boleh dipinjam'];
        }

        // Check if currently borrowed
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM peminjaman_aset WHERE aset_id = ? AND status = 'Dipinjam'");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return ['available' => false, 'reason' => 'Aset sedang dipinjam'];
        }

        // Check if in maintenance
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM maintenance WHERE aset_id = ? AND status IN ('Proses', 'Pending')");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return ['available' => false, 'reason' => 'Aset sedang dalam maintenance'];
        }

        return ['available' => true, 'reason' => 'Tersedia'];
    }

    // Get all assets available for borrowing
    public function getAvailableForBorrowing()
    {
        $sql = "SELECT a.* 
                FROM aset a
                WHERE a.dapat_dipinjam = 'Ya'
                AND a.id NOT IN (
                    SELECT aset_id FROM peminjaman_aset WHERE status = 'Dipinjam'
                )
                AND a.id NOT IN (
                    SELECT aset_id FROM maintenance WHERE status IN ('Proses', 'Pending')
                )
                ORDER BY a.nama_barang ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

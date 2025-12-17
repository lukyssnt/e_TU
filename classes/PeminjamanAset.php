<?php
require_once __DIR__ . '/../config/database.php';

class PeminjamanAset
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT p.*, a.nama_barang, a.kode_aset, a.kategori 
                FROM peminjaman_aset p
                JOIN aset a ON p.aset_id = a.id
                ORDER BY p.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        try {
            $this->db->beginTransaction();

            // Check asset availability
            require_once __DIR__ . '/Aset.php';
            $aset = new Aset();
            $availability = $aset->isAvailableForBorrowing($data['aset_id']);

            if (!$availability['available']) {
                throw new Exception($availability['reason']);
            }

            $sql = "INSERT INTO peminjaman_aset (aset_id, nama_peminjam, no_hp, tanggal_pinjam, tanggal_kembali, keperluan, status) 
                    VALUES (:aset, :nama, :hp, :tgl_pinjam, :tgl_kembali, :keperluan, 'Dipinjam')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aset' => $data['aset_id'],
                ':nama' => $data['nama_peminjam'],
                ':hp' => $data['no_hp'],
                ':tgl_pinjam' => $data['tanggal_pinjam'],
                ':tgl_kembali' => $data['tanggal_kembali'],
                ':keperluan' => $data['keperluan']
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e; // Re-throw to handle in controller
        }
    }

    public function kembalikan($id, $tglKembali)
    {
        $sql = "UPDATE peminjaman_aset SET status = 'Dikembalikan', tanggal_dikembalikan = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tglKembali, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM peminjaman_aset WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getStatusCounts()
    {
        $counts = [
            'Dipinjam' => 0,
            'Dikembalikan' => 0
        ];

        $stmt = $this->db->query("SELECT status, COUNT(*) as count FROM peminjaman_aset GROUP BY status");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = $row['count'];
            }
        }

        return $counts;
    }
}

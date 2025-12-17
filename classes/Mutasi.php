<?php
require_once __DIR__ . '/../config/database.php';

class Mutasi
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all mutasi
     */
    public function getAll($jenis = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT m.*, s.nama_lengkap, s.nisn, k.nama_kelas 
                FROM mutasi_siswa m
                LEFT JOIN siswa s ON m.siswa_id = s.id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE 1=1";

        $params = [];
        if ($jenis) {
            $sql .= " AND m.jenis_mutasi = ?";
            $params[] = $jenis;
        }

        $sql .= " ORDER BY m.tanggal_mutasi DESC";

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

        $stmt = $this->db->prepare("
            SELECT m.*, s.nama_lengkap, s.nisn 
            FROM mutasi_siswa m
            LEFT JOIN siswa s ON m.siswa_id = s.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create mutasi
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Insert mutasi record
            $stmt = $this->db->prepare("
                INSERT INTO mutasi_siswa 
                (siswa_id, jenis_mutasi, tanggal_mutasi, alasan, sekolah_asal, sekolah_tujuan, keterangan)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['siswa_id'],
                $data['jenis_mutasi'],
                $data['tanggal_mutasi'],
                $data['alasan'],
                $data['sekolah_asal'] ?? null,
                $data['sekolah_tujuan'] ?? null,
                $data['keterangan'] ?? null
            ]);

            // Update siswa status
            if ($data['jenis_mutasi'] === 'Keluar') {
                $status = 'Mutasi Keluar';
                $this->db->prepare("UPDATE siswa SET status = ? WHERE id = ?")->execute([$status, $data['siswa_id']]);
            } elseif ($data['jenis_mutasi'] === 'Masuk') {
                // Untuk mutasi masuk, biasanya siswa baru dibuat dulu dengan status 'Aktif' 
                // atau bisa juga status khusus jika diperlukan
                // Disini kita asumsikan siswa sudah ada (baru diinput)
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete mutasi
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Get mutasi data first to revert status
            $mutasi = $this->getById($id);

            if ($mutasi && $mutasi['jenis_mutasi'] === 'Keluar') {
                // Revert status siswa ke Aktif
                $this->db->prepare("UPDATE siswa SET status = 'Aktif' WHERE id = ?")->execute([$mutasi['siswa_id']]);
            }

            // Delete record
            $stmt = $this->db->prepare("DELETE FROM mutasi_siswa WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get stats
     */
    public function getStats()
    {
        if ($this->db === null) {
            return ['Masuk' => 0, 'Keluar' => 0];
        }

        $stmt = $this->db->query("
            SELECT jenis_mutasi, COUNT(*) as total 
            FROM mutasi_siswa 
            GROUP BY jenis_mutasi
        ");

        $stats = ['Masuk' => 0, 'Keluar' => 0];
        while ($row = $stmt->fetch()) {
            $stats[$row['jenis_mutasi']] = $row['total'];
        }

        return $stats;
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM mutasi_siswa");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}

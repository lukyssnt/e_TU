<?php
require_once __DIR__ . '/../config/database.php';

class Agenda
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all agenda
     */
    public function getAll($bulan = null, $tahun = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT * FROM agenda WHERE 1=1";
        $params = [];

        if ($bulan && $tahun) {
            $sql .= " AND MONTH(tanggal_mulai) = ? AND YEAR(tanggal_mulai) = ?";
            $params[] = $bulan;
            $params[] = $tahun;
        } elseif ($tahun) {
            $sql .= " AND YEAR(tanggal_mulai) = ?";
            $params[] = $tahun;
        }

        $sql .= " ORDER BY tanggal_mulai DESC";

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

        $stmt = $this->db->prepare("SELECT * FROM agenda WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create agenda
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO agenda 
            (judul, deskripsi, tanggal_mulai, tanggal_selesai, waktu_mulai, waktu_selesai, 
             lokasi, penanggungjawab, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['judul'],
            $data['deskripsi'] ?? null,
            $data['tanggal_mulai'],
            $data['tanggal_selesai'] ?? $data['tanggal_mulai'],
            $data['waktu_mulai'] ?? null,
            $data['waktu_selesai'] ?? null,
            $data['lokasi'] ?? null,
            $data['penanggungjawab'] ?? null,
            $data['status'] ?? 'Akan Datang'
        ]);
    }

    /**
     * Update agenda
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE agenda SET 
            judul = ?, 
            deskripsi = ?, 
            tanggal_mulai = ?, 
            tanggal_selesai = ?,
            waktu_mulai = ?,
            waktu_selesai = ?,
            lokasi = ?, 
            penanggungjawab = ?,
            status = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['judul'],
            $data['deskripsi'] ?? null,
            $data['tanggal_mulai'],
            $data['tanggal_selesai'] ?? $data['tanggal_mulai'],
            $data['waktu_mulai'] ?? null,
            $data['waktu_selesai'] ?? null,
            $data['lokasi'] ?? null,
            $data['penanggungjawab'] ?? null,
            $data['status'],
            $id
        ]);
    }

    /**
     * Delete agenda
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM agenda WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get agenda bulan ini
     */
    public function getBulanIni()
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->query("
            SELECT * FROM agenda 
            WHERE MONTH(tanggal_mulai) = MONTH(CURRENT_DATE()) 
            AND YEAR(tanggal_mulai) = YEAR(CURRENT_DATE())
            ORDER BY tanggal_mulai ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming agenda
     */
    public function getUpcoming($limit = 5)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM agenda 
            WHERE tanggal_mulai >= CURRENT_DATE()
            ORDER BY tanggal_mulai ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get status counts
     */
    public function getStatusCounts()
    {
        if ($this->db === null) {
            return ['Akan Datang' => 0, 'Berlangsung' => 0, 'Selesai' => 0];
        }

        $stmt = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM agenda 
            GROUP BY status
        ");

        $counts = ['Akan Datang' => 0, 'Berlangsung' => 0, 'Selesai' => 0];
        while ($row = $stmt->fetch()) {
            $counts[$row['status']] = $row['total'];
        }

        return $counts;
    }

    /**
     * Update status otomatis berdasarkan tanggal
     */
    public function updateStatusOtomatis()
    {
        if ($this->db === null) {
            return;
        }

        // Set ke Berlangsung jika tanggal hari ini dalam range
        $this->db->query("
            UPDATE agenda SET status = 'Berlangsung'
            WHERE CURRENT_DATE() BETWEEN tanggal_mulai AND tanggal_selesai
            AND status != 'Selesai'
        ");

        // Set ke Selesai jika sudah lewat
        $this->db->query("
            UPDATE agenda SET status = 'Selesai'
            WHERE tanggal_selesai < CURRENT_DATE()
            AND status != 'Selesai'
        ");
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

class Disposisi
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all disposisi
     */
    public function getAll($status = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT d.*, 
                sm.nomor_surat, sm.perihal as surat_perihal, sm.pengirim, sm.tanggal_terima,
                p1.nama_lengkap as dari_nama, j1.nama_jabatan as dari_jabatan,
                p2.nama_lengkap as kepada_nama, j2.nama_jabatan as kepada_jabatan
                FROM disposisi d
                LEFT JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
                LEFT JOIN pegawai p1 ON d.dari_pegawai_id = p1.id
                LEFT JOIN jabatan j1 ON p1.jabatan_id = j1.id
                LEFT JOIN pegawai p2 ON d.kepada_pegawai_id = p2.id
                LEFT JOIN jabatan j2 ON p2.jabatan_id = j2.id
                WHERE 1=1";

        $params = [];
        if ($status) {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY d.created_at DESC";

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
            SELECT d.*, 
            sm.nomor_surat, sm.perihal as surat_perihal, sm.pengirim, sm.tanggal_terima,
            p1.nama_lengkap as dari_nama, j1.nama_jabatan as dari_jabatan,
            p2.nama_lengkap as kepada_nama, j2.nama_jabatan as kepada_jabatan
            FROM disposisi d
            LEFT JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
            LEFT JOIN pegawai p1 ON d.dari_pegawai_id = p1.id
            LEFT JOIN jabatan j1 ON p1.jabatan_id = j1.id
            LEFT JOIN pegawai p2 ON d.kepada_pegawai_id = p2.id
            LEFT JOIN jabatan j2 ON p2.jabatan_id = j2.id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get by surat masuk
     */
    public function getBySuratMasuk($suratMasukId)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT d.*, 
            p1.nama_lengkap as dari_nama, j1.nama_jabatan as dari_jabatan,
            p2.nama_lengkap as kepada_nama, j2.nama_jabatan as kepada_jabatan
            FROM disposisi d
            LEFT JOIN pegawai p1 ON d.dari_pegawai_id = p1.id
            LEFT JOIN jabatan j1 ON p1.jabatan_id = j1.id
            LEFT JOIN pegawai p2 ON d.kepada_pegawai_id = p2.id
            LEFT JOIN jabatan j2 ON p2.jabatan_id = j2.id
            WHERE d.surat_masuk_id = ?
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$suratMasukId]);
        return $stmt->fetchAll();
    }

    /**
     * Get disposisi untuk pegawai tertentu
     */
    public function getByPegawai($pegawaiId, $status = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT d.*, 
                sm.nomor_surat, sm.perihal as surat_perihal, sm.pengirim, sm.tanggal_terima,
                p1.nama_lengkap as dari_nama, j1.nama_jabatan as dari_jabatan
                FROM disposisi d
                LEFT JOIN surat_masuk sm ON d.surat_masuk_id = sm.id
                LEFT JOIN pegawai p1 ON d.dari_pegawai_id = p1.id
                LEFT JOIN jabatan j1 ON p1.jabatan_id = j1.id
                WHERE d.kepada_pegawai_id = ?";

        $params = [$pegawaiId];
        if ($status) {
            $sql .= " AND d.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY d.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create disposisi
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO disposisi 
            (surat_masuk_id, dari_pegawai_id, kepada_pegawai_id, instruksi, deadline, status, catatan)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $result = $stmt->execute([
            $data['surat_masuk_id'],
            $data['dari_pegawai_id'] ?? null,
            $data['kepada_pegawai_id'],
            $data['instruksi'],
            $data['deadline'] ?? null,
            $data['status'] ?? 'Pending',
            $data['catatan'] ?? null
        ]);

        // Update status surat masuk
        if ($result) {
            $this->db->prepare("UPDATE surat_masuk SET status = 'Sudah Disposisi' WHERE id = ?")->execute([$data['surat_masuk_id']]);
        }

        return $result;
    }

    /**
     * Update disposisi
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE disposisi SET 
            kepada_pegawai_id = ?,
            instruksi = ?,
            deadline = ?,
            status = ?,
            catatan = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['kepada_pegawai_id'],
            $data['instruksi'],
            $data['deadline'] ?? null,
            $data['status'],
            $data['catatan'] ?? null,
            $id
        ]);
    }

    /**
     * Update status
     */
    public function updateStatus($id, $status, $catatan = null)
    {
        if ($this->db === null) {
            return false;
        }

        $sql = "UPDATE disposisi SET status = ?";
        $params = [$status];

        if ($catatan !== null) {
            $sql .= ", catatan = ?";
            $params[] = $catatan;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete disposisi
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM disposisi WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get status counts
     */
    public function getStatusCounts()
    {
        if ($this->db === null) {
            return ['Pending' => 0, 'Proses' => 0, 'Selesai' => 0];
        }

        $stmt = $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM disposisi 
            GROUP BY status
        ");

        $counts = ['Pending' => 0, 'Proses' => 0, 'Selesai' => 0];
        while ($row = $stmt->fetch()) {
            $counts[$row['status']] = $row['total'];
        }

        return $counts;
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM disposisi");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get surat masuk yang belum didisposisi
     */
    public function getSuratBelumDisposisi()
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->query("
            SELECT * FROM surat_masuk 
            WHERE status = 'Belum Disposisi'
            ORDER BY tanggal_terima DESC
        ");
        return $stmt->fetchAll();
    }
}

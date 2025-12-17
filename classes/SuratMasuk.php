<?php
require_once __DIR__ . '/../config/database.php';

class SuratMasuk
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all surat masuk
     */
    public function getAll($limit = null, $offset = 0)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT sm.*, u.username as created_by_name 
                FROM surat_masuk sm
                LEFT JOIN users u ON sm.created_by = u.id
                ORDER BY sm.tanggal_terima DESC, sm.id DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get surat masuk by ID
     */
    public function getById($id)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT sm.*, u.username as created_by_name 
            FROM surat_masuk sm
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE sm.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new surat masuk
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO surat_masuk 
            (nomor_surat, tanggal_terima, pengirim, perihal, sifat_surat, file_surat, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nomor_surat'],
            $data['tanggal_terima'],
            $data['pengirim'],
            $data['perihal'],
            $data['sifat_surat'],
            $data['file_surat'] ?? null,
            $data['created_by']
        ]);
    }

    /**
     * Update surat masuk
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $sql = "UPDATE surat_masuk SET 
                nomor_surat = ?, 
                tanggal_terima = ?, 
                pengirim = ?, 
                perihal = ?, 
                sifat_surat = ?";

        $params = [
            $data['nomor_surat'],
            $data['tanggal_terima'],
            $data['pengirim'],
            $data['perihal'],
            $data['sifat_surat']
        ];

        if (isset($data['file_surat'])) {
            $sql .= ", file_surat = ?";
            $params[] = $data['file_surat'];
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete surat masuk
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        // Get file path before delete
        $surat = $this->getById($id);

        $stmt = $this->db->prepare("DELETE FROM surat_masuk WHERE id = ?");
        $result = $stmt->execute([$id]);

        // Delete file if exists
        if ($result && $surat && $surat['file_surat']) {
            $filePath = __DIR__ . '/../' . $surat['file_surat'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return $result;
    }

    /**
     * Update status
     */
    public function updateStatus($id, $status)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE surat_masuk SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Search surat masuk
     */
    public function search($keyword)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT sm.*, u.username as created_by_name 
            FROM surat_masuk sm
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE sm.nomor_surat LIKE ? 
            OR sm.pengirim LIKE ? 
            OR sm.perihal LIKE ?
            ORDER BY sm.tanggal_terima DESC
        ");

        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM surat_masuk");
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Get by status
     */
    public function getByStatus($status)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT sm.*, u.username as created_by_name 
            FROM surat_masuk sm
            LEFT JOIN users u ON sm.created_by = u.id
            WHERE sm.status = ?
            ORDER BY sm.tanggal_terima DESC
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
}

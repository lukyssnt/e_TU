<?php
require_once __DIR__ . '/../config/database.php';

class Dokumentasi
{
    private $db;
    private $maxFiles = 3;
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all dokumentasi
     */
    public function getAll()
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->query("
            SELECT d.*, a.judul as agenda_judul, a.tanggal_mulai
            FROM dokumentasi d
            LEFT JOIN agenda a ON d.agenda_id = a.id
            ORDER BY d.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get by agenda ID
     */
    public function getByAgenda($agendaId)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM dokumentasi 
            WHERE agenda_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$agendaId]);
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

        $stmt = $this->db->prepare("SELECT * FROM dokumentasi WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Count dokumentasi for an agenda
     */
    public function countByAgenda($agendaId)
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM dokumentasi WHERE agenda_id = ?");
        $stmt->execute([$agendaId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Upload dokumentasi
     */
    public function upload($agendaId, $files, $keterangan = null)
    {
        if ($this->db === null) {
            return ['success' => false, 'message' => 'Database tidak tersedia'];
        }

        // Check existing count
        $existingCount = $this->countByAgenda($agendaId);
        $remaining = $this->maxFiles - $existingCount;

        if ($remaining <= 0) {
            return ['success' => false, 'message' => 'Maksimal ' . $this->maxFiles . ' foto per agenda'];
        }

        $uploaded = 0;
        $errors = [];

        // Handle multiple files
        $fileCount = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < min($fileCount, $remaining); $i++) {
            $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $fileSize = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $fileError = is_array($files['error']) ? $files['error'][$i] : $files['error'];

            if ($fileError !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate size
            if ($fileSize > $this->maxSize) {
                $errors[] = "$fileName melebihi batas 5MB";
                continue;
            }

            // Validate type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = "$fileName bukan file gambar yang valid";
                continue;
            }

            // Generate unique filename
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newName = 'dok_' . time() . '_' . $i . '_' . uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/dokumentasi/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uploadPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, $uploadPath)) {
                // Save to database
                $stmt = $this->db->prepare("
                    INSERT INTO dokumentasi (agenda_id, file_path, keterangan)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([
                    $agendaId,
                    'uploads/dokumentasi/' . $newName,
                    $keterangan
                ]);
                $uploaded++;
            }
        }

        if ($uploaded > 0) {
            return ['success' => true, 'message' => "$uploaded foto berhasil diupload", 'errors' => $errors];
        } else {
            return ['success' => false, 'message' => 'Gagal upload foto', 'errors' => $errors];
        }
    }

    /**
     * Delete dokumentasi
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        // Get file path first
        $doc = $this->getById($id);
        if ($doc && $doc['file_path']) {
            $filePath = __DIR__ . '/../' . $doc['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM dokumentasi WHERE id = ?");
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

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM dokumentasi");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}

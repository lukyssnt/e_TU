<?php
require_once __DIR__ . '/../config/database.php';

class DokumenPegawai
{
    private $db;

    // Daftar jenis dokumen yang bisa diupload untuk pegawai
    public static $jenisDoc = [
        'ktp' => 'KTP (Kartu Tanda Penduduk)',
        'kk' => 'Kartu Keluarga (KK)',
        'npwp' => 'NPWP',
        'sk_pengangkatan' => 'SK Pengangkatan',
        'ijazah_terakhir' => 'Ijazah Terakhir',
        'transkrip_nilai' => 'Transkrip Nilai',
        'sertifikat_pelatihan' => 'Sertifikat Pelatihan/Workshop',
        'sk_mengajar' => 'SK Pembagian Tugas Mengajar',
        'foto' => 'Pas Foto',
        'lainnya' => 'Dokumen Lainnya'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all documents by pegawai ID
     */
    public function getByPegawai($pegawaiId)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM dokumen_pegawai WHERE pegawai_id = ? ORDER BY jenis_dokumen ASC
        ");
        $stmt->execute([$pegawaiId]);
        return $stmt->fetchAll();
    }

    /**
     * Get document by ID
     */
    public function getById($id)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM dokumen_pegawai WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Check if document type exists for pegawai
     */
    public function exists($pegawaiId, $jenis)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT id FROM dokumen_pegawai WHERE pegawai_id = ? AND jenis_dokumen = ?
        ");
        $stmt->execute([$pegawaiId, $jenis]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Upload document
     */
    public function upload($pegawaiId, $jenis, $file, $namaDokumen = null)
    {
        if ($this->db === null) {
            return ['success' => false, 'message' => 'Database tidak tersedia'];
        }

        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Hanya JPG, PNG, PDF'];
        }

        // Max 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
        }

        // Create upload directory
        $uploadDir = __DIR__ . '/../uploads/dokumen-pegawai/' . $pegawaiId . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $jenis . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;

        // Move file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => false, 'message' => 'Gagal mengupload file'];
        }

        // For 'lainnya', allow multiple uploads (don't delete old one)
        if ($jenis !== 'lainnya') {
            // Delete old file if exists (for non-lainnya types)
            $existing = $this->getByJenis($pegawaiId, $jenis);
            if ($existing) {
                $oldFile = __DIR__ . '/../uploads/dokumen-pegawai/' . $pegawaiId . '/' . $existing['nama_file'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
                // Update record
                $stmt = $this->db->prepare("
                    UPDATE dokumen_pegawai SET nama_file = ?, updated_at = NOW() WHERE id = ?
                ");
                $stmt->execute([$filename, $existing['id']]);
                return ['success' => true, 'message' => 'Dokumen berhasil diupload', 'filename' => $filename];
            }
        }

        // Insert new record (for lainnya or first-time upload)
        $stmt = $this->db->prepare("
            INSERT INTO dokumen_pegawai (pegawai_id, jenis_dokumen, nama_dokumen, nama_file, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$pegawaiId, $jenis, $namaDokumen, $filename]);

        return ['success' => true, 'message' => 'Dokumen berhasil diupload', 'filename' => $filename];
    }

    /**
     * Get document by jenis
     */
    public function getByJenis($pegawaiId, $jenis)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM dokumen_pegawai WHERE pegawai_id = ? AND jenis_dokumen = ?
        ");
        $stmt->execute([$pegawaiId, $jenis]);
        return $stmt->fetch();
    }

    /**
     * Delete document
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $doc = $this->getById($id);
        if ($doc) {
            $filepath = __DIR__ . '/../uploads/dokumen-pegawai/' . $doc['pegawai_id'] . '/' . $doc['nama_file'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM dokumen_pegawai WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get document count by pegawai
     */
    public function getCountByPegawai($pegawaiId)
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM dokumen_pegawai WHERE pegawai_id = ?");
        $stmt->execute([$pegawaiId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get kelengkapan dokumen status
     */
    public function getKelengkapan($pegawaiId)
    {
        $docs = $this->getByPegawai($pegawaiId);
        $uploaded = array_column($docs, 'jenis_dokumen');

        // Dokumen wajib
        $required = ['ktp', 'kk', 'sk_pengangkatan', 'ijazah_terakhir', 'foto'];
        $complete = 0;
        foreach ($required as $req) {
            if (in_array($req, $uploaded)) {
                $complete++;
            }
        }

        return [
            'total' => count($docs),
            'complete' => $complete,
            'required' => count($required),
            'percentage' => count($required) > 0 ? round(($complete / count($required)) * 100) : 0
        ];
    }
}

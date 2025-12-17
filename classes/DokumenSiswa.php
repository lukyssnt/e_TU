<?php
require_once __DIR__ . '/../config/database.php';

class DokumenSiswa
{
    private $db;

    // Daftar jenis dokumen yang bisa diupload
    public static $jenisDoc = [
        'kk' => 'Kartu Keluarga (KK)',
        'akte' => 'Akte Kelahiran',
        'ijazah_sd' => 'Ijazah SD/MI',
        'ijazah_smp' => 'Ijazah SMP/MTs',
        'ktp_ayah' => 'KTP Ayah',
        'ktp_ibu' => 'KTP Ibu',
        'skl' => 'Surat Keterangan Lulus (SKL)',
        'kip' => 'Kartu Indonesia Pintar (KIP)',
        'kis' => 'Kartu Indonesia Sehat (KIS)',
        'pkh' => 'Program Keluarga Harapan (PKH)',
        'foto' => 'Pas Foto',
        'lainnya' => 'Dokumen Lainnya'
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all documents by siswa ID
     */
    public function getBySiswa($siswaId)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT * FROM dokumen_siswa WHERE siswa_id = ? ORDER BY jenis_dokumen ASC
        ");
        $stmt->execute([$siswaId]);
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

        $stmt = $this->db->prepare("SELECT * FROM dokumen_siswa WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Check if document type exists for siswa
     */
    public function exists($siswaId, $jenis)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            SELECT id FROM dokumen_siswa WHERE siswa_id = ? AND jenis_dokumen = ?
        ");
        $stmt->execute([$siswaId, $jenis]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Upload document
     */
    public function upload($siswaId, $jenis, $file, $namaDokumen = null)
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
        $uploadDir = __DIR__ . '/../uploads/dokumen-siswa/' . $siswaId . '/';
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
            $existing = $this->getByJenis($siswaId, $jenis);
            if ($existing) {
                $oldFile = __DIR__ . '/../uploads/dokumen-siswa/' . $siswaId . '/' . $existing['nama_file'];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
                // Update record
                $stmt = $this->db->prepare("
                    UPDATE dokumen_siswa SET nama_file = ?, updated_at = NOW() WHERE id = ?
                ");
                $stmt->execute([$filename, $existing['id']]);
                return ['success' => true, 'message' => 'Dokumen berhasil diupload', 'filename' => $filename];
            }
        }

        // Insert new record (for lainnya or first-time upload)
        $stmt = $this->db->prepare("
            INSERT INTO dokumen_siswa (siswa_id, jenis_dokumen, nama_dokumen, nama_file, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$siswaId, $jenis, $namaDokumen, $filename]);

        return ['success' => true, 'message' => 'Dokumen berhasil diupload', 'filename' => $filename];
    }

    /**
     * Get document by jenis
     */
    public function getByJenis($siswaId, $jenis)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM dokumen_siswa WHERE siswa_id = ? AND jenis_dokumen = ?
        ");
        $stmt->execute([$siswaId, $jenis]);
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
            $filepath = __DIR__ . '/../uploads/dokumen-siswa/' . $doc['siswa_id'] . '/' . $doc['nama_file'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }

        $stmt = $this->db->prepare("DELETE FROM dokumen_siswa WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get document count by siswa
     */
    public function getCountBySiswa($siswaId)
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM dokumen_siswa WHERE siswa_id = ?");
        $stmt->execute([$siswaId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get kelengkapan dokumen status
     */
    public function getKelengkapan($siswaId)
    {
        $docs = $this->getBySiswa($siswaId);
        $uploaded = array_column($docs, 'jenis_dokumen');

        $required = ['kk', 'akte', 'ijazah_sd', 'ktp_ayah', 'ktp_ibu', 'foto'];
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
            'percentage' => round(($complete / count($required)) * 100)
        ];
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

class Portal
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- Content Management ---
    public function getAllContent()
    {
        $stmt = $this->db->query("SELECT * FROM landing_content");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $content = [];
        foreach ($rows as $row) {
            $content[$row['section_key']] = $row;
        }
        return $content;
    }

    public function updateContent($key, $value)
    {
        $stmt = $this->db->prepare("UPDATE landing_content SET content_value = ? WHERE section_key = ?");
        return $stmt->execute([$value, $key]);
    }

    public function uploadImage($file, $targetDir = 'uploads/portal/')
    {
        // Create dir if not exists
        if (!file_exists(__DIR__ . '/../' . $targetDir)) {
            mkdir(__DIR__ . '/../' . $targetDir, 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueName = uniqid() . '.' . $extension;
        $targetFile = $targetDir . $uniqueName;
        $absolutePath = __DIR__ . '/../' . $targetFile;

        // Check image file type
        $check = getimagesize($file['tmp_name']);
        if ($check === false)
            return false;

        // Upload
        if (move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return $targetFile; // Return relative path
        }
        return false;
    }

    // --- Buku Tamu ---
    public function getAllBukuTamu()
    {
        $stmt = $this->db->query("SELECT * FROM buku_tamu ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBukuTamu($data)
    {
        $stmt = $this->db->prepare("INSERT INTO buku_tamu (nama, email, instansi, keperluan) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['nama'], $data['email'], $data['instansi'], $data['keperluan']]);
    }

    public function deleteBukuTamu($id)
    {
        $stmt = $this->db->prepare("DELETE FROM buku_tamu WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Pelayanan Alumni ---
    public function getAllAlumniRequests()
    {
        $stmt = $this->db->query("SELECT * FROM pelayanan_alumni ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAlumniRequest($data)
    {
        $stmt = $this->db->prepare("INSERT INTO pelayanan_alumni (nama, tahun_lulus, nisn, no_hp, jenis_layanan, deskripsi) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['nama'],
            $data['tahun_lulus'],
            $data['nisn'],
            $data['no_hp'],
            $data['jenis_layanan'],
            $data['deskripsi']
        ]);
    }

    public function updateAlumniRequestStatus($id, $status, $keterangan = null)
    {
        $stmt = $this->db->prepare("UPDATE pelayanan_alumni SET status = ?, keterangan_admin = ? WHERE id = ?");
        return $stmt->execute([$status, $keterangan, $id]);
    }

    public function deleteAlumniRequest($id)
    {
        $stmt = $this->db->prepare("DELETE FROM pelayanan_alumni WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

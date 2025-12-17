<?php
require_once __DIR__ . '/../config/database.php';

class Pegawai
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all pegawai
    public function getAll()
    {
        try {
            $sql = "SELECT p.*, j.nama_jabatan 
                    FROM pegawai p
                    LEFT JOIN jabatan j ON p.jabatan_id = j.id
                    ORDER BY p.nama_lengkap ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Pegawai::getAll() Error: " . $e->getMessage());
            return [];
        }
    }

    // Get single pegawai by ID
    public function getById($id)
    {
        $sql = "SELECT * FROM pegawai WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new pegawai
    public function create($data)
    {
        try {
            $sql = "INSERT INTO pegawai (nip, nama_lengkap, jabatan_id, status_kepegawaian, golongan, email, no_hp, alamat, foto) 
                    VALUES (:nip, :nama, :jabatan, :status, :golongan, :email, :hp, :alamat, :foto)";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':nip' => $data['nip'],
                ':nama' => $data['nama_lengkap'],
                ':jabatan' => $data['jabatan_id'],
                ':status' => $data['status_kepegawaian'],
                ':golongan' => $data['golongan'],
                ':email' => $data['email'],
                ':hp' => $data['no_hp'],
                ':alamat' => $data['alamat'],
                ':foto' => $data['foto'] ?? null
            ]);

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Pegawai::create() failed: " . print_r($errorInfo, true));
                throw new Exception("Gagal menyimpan data: " . ($errorInfo[2] ?? 'Unknown error'));
            }

            return true;
        } catch (PDOException $e) {
            error_log("Pegawai::create() PDO Error: " . $e->getMessage());
            error_log("Data: " . print_r($data, true));
            throw new Exception("Database error: " . $e->getMessage());
        }
    }

    // Update pegawai
    public function update($id, $data)
    {
        try {
            $sql = "UPDATE pegawai SET 
                    nip = :nip, 
                    nama_lengkap = :nama, 
                    jabatan_id = :jabatan, 
                    status_kepegawaian = :status, 
                    golongan = :golongan, 
                    email = :email, 
                    no_hp = :hp, 
                    alamat = :alamat";

            $params = [
                ':nip' => $data['nip'],
                ':nama' => $data['nama_lengkap'],
                ':jabatan' => $data['jabatan_id'],
                ':status' => $data['status_kepegawaian'],
                ':golongan' => $data['golongan'],
                ':email' => $data['email'],
                ':hp' => $data['no_hp'],
                ':alamat' => $data['alamat'],
                ':id' => $id
            ];

            if (isset($data['foto'])) {
                $sql .= ", foto = :foto";
                $params[':foto'] = $data['foto'];
            }

            $sql .= " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Delete pegawai
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM pegawai WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get statistics
    public function getStats()
    {
        $stats = [
            'total' => 0,
            'pns' => 0,
            'honorer' => 0,
            'pppk' => 0
        ];

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM pegawai");
        $stats['total'] = $stmt->fetchColumn();

        $stmt = $this->db->query("SELECT status_kepegawaian, COUNT(*) as count FROM pegawai GROUP BY status_kepegawaian");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if ($row['status_kepegawaian'] == 'PNS')
                $stats['pns'] = $row['count'];
            if ($row['status_kepegawaian'] == 'Honorer')
                $stats['honorer'] = $row['count'];
            if ($row['status_kepegawaian'] == 'PPPK')
                $stats['pppk'] = $row['count'];
        }

        return $stats;
    }

    // Get all jabatans
    public function getAllJabatan()
    {
        $stmt = $this->db->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

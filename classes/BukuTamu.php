<?php
require_once __DIR__ . '/../config/database.php';

class BukuTamu
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Insert new visitor
    public function create($data)
    {
        $sql = "INSERT INTO buku_tamu (nama, email, instansi, keperluan, no_hp, tanggal_berkunjung) 
                VALUES (:nama, :email, :instansi, :keperluan, :no_hp, NOW())";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nama' => $data['nama'],
            ':email' => $data['email'] ?? null,
            ':instansi' => $data['instansi'] ?? '-',
            ':keperluan' => $data['keperluan'],
            ':no_hp' => $data['no_hp']
        ]);
    }

    // Get all visitors (for admin)
    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM buku_tamu ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
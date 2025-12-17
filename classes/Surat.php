<?php
require_once __DIR__ . '/../config/database.php';

class Surat
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- Stats ---
    public function getStats()
    {
        $stats = [
            'masuk_bulan_ini' => 0,
            'keluar_bulan_ini' => 0,
            'disposisi_pending' => 0,
            'total_arsip' => 0
        ];

        // Surat Masuk Bulan Ini
        $stmt = $this->db->query("SELECT COUNT(*) FROM surat_masuk WHERE MONTH(tanggal_terima) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_terima) = YEAR(CURRENT_DATE())");
        $stats['masuk_bulan_ini'] = $stmt->fetchColumn();

        // Surat Keluar Bulan Ini
        $stmt = $this->db->query("SELECT COUNT(*) FROM surat_keluar WHERE MONTH(tanggal_surat) = MONTH(CURRENT_DATE()) AND YEAR(tanggal_surat) = YEAR(CURRENT_DATE())");
        $stats['keluar_bulan_ini'] = $stmt->fetchColumn();

        // Disposisi Pending
        $stmt = $this->db->query("SELECT COUNT(*) FROM disposisi WHERE status = 'Pending'");
        $stats['disposisi_pending'] = $stmt->fetchColumn();

        // Example Total Arsip (sum of both)
        $stmt1 = $this->db->query("SELECT COUNT(*) FROM surat_masuk");
        $stmt2 = $this->db->query("SELECT COUNT(*) FROM surat_keluar");
        $stats['total_arsip'] = $stmt1->fetchColumn() + $stmt2->fetchColumn();

        return $stats;
    }

    // --- Surat Masuk ---
    public function getSuratMasuk()
    {
        $sql = "SELECT * FROM surat_masuk ORDER BY tanggal_terima DESC, created_at DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSuratMasuk($data)
    {
        $sql = "INSERT INTO surat_masuk (nomor_surat, tanggal_terima, pengirim, perihal, sifat_surat, file_surat, status, created_by) 
                VALUES (:no, :tgl, :pengirim, :perihal, :sifat, :file, 'Belum Disposisi', :user)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':no' => $data['nomor_surat'],
            ':tgl' => $data['tanggal_terima'],
            ':pengirim' => $data['pengirim'],
            ':perihal' => $data['perihal'],
            ':sifat' => $data['sifat_surat'],
            ':file' => $data['file_surat'],
            ':user' => $_SESSION['user_id'] ?? 1
        ]);
    }

    public function updateSuratMasuk($id, $data)
    {
        $sql = "UPDATE surat_masuk SET nomor_surat = :no, tanggal_terima = :tgl, pengirim = :pengirim, perihal = :perihal, sifat_surat = :sifat";
        
        $params = [
            ':no' => $data['nomor_surat'],
            ':tgl' => $data['tanggal_terima'],
            ':pengirim' => $data['pengirim'],
            ':perihal' => $data['perihal'],
            ':sifat' => $data['sifat_surat'],
            ':id' => $id
        ];

        if (isset($data['file_surat'])) {
            $sql .= ", file_surat = :file";
            $params[':file'] = $data['file_surat'];
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteSuratMasuk($id)
    {
        // Should also delete file if exists (handled in controller)
        $stmt = $this->db->prepare("DELETE FROM surat_masuk WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Surat Keluar ---
    public function getSuratKeluar()
    {
        $sql = "SELECT sk.*, ts.nama_template 
                FROM surat_keluar sk
                LEFT JOIN template_surat ts ON sk.template_id = ts.id
                ORDER BY sk.tanggal_surat DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createSuratKeluar($data)
    {
        $sql = "INSERT INTO surat_keluar (nomor_surat, tanggal_surat, tujuan, perihal, template_id, file_surat, created_by) 
                VALUES (:no, :tgl, :tujuan, :perihal, :template, :file, :user)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':no' => $data['nomor_surat'],
            ':tgl' => $data['tanggal_surat'],
            ':tujuan' => $data['tujuan'],
            ':perihal' => $data['perihal'],
            ':template' => $data['template_id'] ?? null,
            ':file' => $data['file_surat'],
            ':user' => $_SESSION['user_id'] ?? 1
        ]);
    }

    public function updateSuratKeluar($id, $data)
    {
        $sql = "UPDATE surat_keluar SET nomor_surat = :no, tanggal_surat = :tgl, tujuan = :tujuan, perihal = :perihal, template_id = :template";
        
        $params = [
            ':no' => $data['nomor_surat'],
            ':tgl' => $data['tanggal_surat'],
            ':tujuan' => $data['tujuan'],
            ':perihal' => $data['perihal'],
            ':template' => $data['template_id'] ?? null,
            ':id' => $id
        ];

        if (isset($data['file_surat'])) {
            $sql .= ", file_surat = :file";
            $params[':file'] = $data['file_surat'];
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteSuratKeluar($id)
    {
        $stmt = $this->db->prepare("DELETE FROM surat_keluar WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

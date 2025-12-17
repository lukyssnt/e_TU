<?php
require_once __DIR__ . '/../config/database.php';

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getStats()
    {
        if ($this->db === null) {
            return [
                'pegawai' => 0,
                'siswa' => 0,
                'surat_masuk' => 0,
                'aset' => 0
            ];
        }

        $stats = [];

        // Total Pegawai
        $stats['pegawai'] = $this->db->query("SELECT COUNT(*) FROM pegawai")->fetchColumn();

        // Total Siswa
        $stats['siswa'] = $this->db->query("SELECT COUNT(*) FROM siswa WHERE status = 'Aktif'")->fetchColumn();

        // Surat Masuk (Minggu ini)
        $stats['surat_masuk'] = $this->db->query("SELECT COUNT(*) FROM surat_masuk WHERE YEARWEEK(tanggal_terima, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();

        // Total Aset
        $stats['aset'] = $this->db->query("SELECT COUNT(*) FROM aset")->fetchColumn();

        return $stats;
    }

    public function getUpcomingEvents($limit = 3)
    {
        if ($this->db === null)
            return [];

        $stmt = $this->db->prepare("
            SELECT judul as title, tanggal_mulai as date, waktu_mulai as time 
            FROM agenda 
            WHERE tanggal_mulai >= CURDATE() 
            ORDER BY tanggal_mulai ASC, waktu_mulai ASC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentActivities($limit = 5)
    {
        // Since we don't have a dedicated activity log yet, 
        // we'll combine recent Surat Masuk and Surat Keluar as a proxy
        if ($this->db === null)
            return [];

        $activities = [];

        // Recent Surat Masuk
        $stmt = $this->db->prepare("
            SELECT 'Surat Masuk' as type, perihal as action, created_at as time, 'info' as color_type 
            FROM surat_masuk 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $suratMasuk = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent Surat Keluar
        $stmt = $this->db->prepare("
            SELECT 'Surat Keluar' as type, perihal as action, created_at as time, 'success' as color_type 
            FROM surat_keluar 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        $suratKeluar = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Merge and sort
        $activities = array_merge($suratMasuk, $suratKeluar);
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, $limit);
    }

    public function getMonthlyStats($months = 6)
    {
        if ($this->db === null) return [];

        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthName = date('M', strtotime("-$i months"));
            
            // Count surat masuk
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM surat_masuk WHERE DATE_FORMAT(tanggal_terima, '%Y-%m') = ?");
            $stmt->execute([$month]);
            $masuk = $stmt->fetchColumn();
            
            // Count surat keluar
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM surat_keluar WHERE DATE_FORMAT(tanggal_surat, '%Y-%m') = ?");
            $stmt->execute([$month]);
            $keluar = $stmt->fetchColumn();
            
            $data[] = [
                'month' => $monthName,
                'masuk' => (int)$masuk,
                'keluar' => (int)$keluar
            ];
        }
        
        return $data;
    }

    public function getUserActivities($limit = 4)
    {
        if ($this->db === null) return [];

        // Get recent users who have been active
        // Since we don't have activity log, we'll show all users with their roles
        $stmt = $this->db->prepare("
            SELECT full_name, role, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProgramProgress()
    {
        if ($this->db === null) return 0;

        // Calculate progress based on completed agenda vs total agenda
        $total = $this->db->query("SELECT COUNT(*) FROM agenda")->fetchColumn();
        if ($total == 0) return 0;
        
        $completed = $this->db->query("SELECT COUNT(*) FROM agenda WHERE status = 'Selesai'")->fetchColumn();
        
        return round(($completed / $total) * 100);
    }
}

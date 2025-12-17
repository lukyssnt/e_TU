<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/TransaksiKas.php'; // Integrate with Cash Book

class Pembayaran
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all bills with Student & Class info
    public function getAllTagihan($kelasId = null)
    {
        $sql = "SELECT t.*, s.nama_lengkap, s.nisn, k.nama_kelas 
                FROM tagihan_siswa t
                JOIN siswa s ON t.siswa_id = s.id
                LEFT JOIN kelas k ON s.kelas_id = k.id";

        if ($kelasId) {
            $sql .= " WHERE s.kelas_id = :kelas_id";
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if ($kelasId) {
            $stmt->bindParam(':kelas_id', $kelasId);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create a new bill with optional initial payment
    public function createTagihan($data)
    {
        try {
            $this->db->beginTransaction();

            $total = (float) $data['total_tagihan'];
            $terbayar = (float) ($data['terbayar'] ?? 0);
            $sisa = $total - $terbayar;
            $status = ($sisa <= 0 && $total > 0) ? 'Lunas' : 'Belum Lunas';
            $tanggalInput = $data['tanggal_input'] ?? date('Y-m-d');

            // Insert Tagihan with explicit created_at
            $sql = "INSERT INTO tagihan_siswa (siswa_id, judul_tagihan, total_tagihan, terbayar, sisa_tagihan, keterangan, status, created_at) 
                    VALUES (:siswa_id, :judul, :total, :terbayar, :sisa, :ket, :status, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':siswa_id' => $data['siswa_id'],
                ':judul' => $data['judul_tagihan'],
                ':total' => $total,
                ':terbayar' => $terbayar,
                ':sisa' => $sisa,
                ':ket' => $data['keterangan'] ?? null,
                ':status' => $status,
                ':created_at' => $tanggalInput . ' ' . date('H:i:s') // Set timestamp using input date
            ]);

            $tagihanId = $this->db->lastInsertId();

            // Insert Log if terbayar > 0
            if ($terbayar > 0) {
                // 1. Log Pembayaran Siswa
                $stmtLog = $this->db->prepare("INSERT INTO pembayaran_log (tagihan_id, tanggal_bayar, jumlah_bayar, keterangan) VALUES (?, ?, ?, ?)");
                $stmtLog->execute([$tagihanId, $tanggalInput, $terbayar, 'Pembayaran Awal / Cicilan Pertama']);
                $logId = $this->db->lastInsertId();

                // 2. Auto-Input ke Transaksi Kas (Masuk)
                $kas = new TransaksiKas();
                $kasData = [
                    'tanggal' => $tanggalInput,
                    'jenis_transaksi' => 'Masuk',
                    'kategori' => 'SPP', // Default category for student payments
                    'keterangan' => 'Pembayaran Siswa: ' . $data['judul_tagihan'],
                    'nominal' => $terbayar,
                    'created_by' => $_SESSION['user_id'] ?? 1,
                    'ref_type' => 'pembayaran_log',
                    'ref_id' => $logId
                ];
                $kas->create($kasData);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Create bills for ALL students in a class with optional initial payment
    public function createTagihanBulk($kelasId, $judul, $total, $keterangan = null, $terbayar = 0, $tanggalInput = null)
    {
        try {
            $this->db->beginTransaction();

            // Get students in class
            $stmtSiswa = $this->db->prepare("SELECT id FROM siswa WHERE kelas_id = ?");
            $stmtSiswa->execute([$kelasId]);
            $students = $stmtSiswa->fetchAll(PDO::FETCH_COLUMN);

            $total = (float) $total;
            $terbayar = (float) $terbayar;
            $sisa = $total - $terbayar;
            $status = ($sisa <= 0 && $total > 0) ? 'Lunas' : 'Belum Lunas';
            $tanggalInput = $tanggalInput ?? date('Y-m-d');

            $sql = "INSERT INTO tagihan_siswa (siswa_id, judul_tagihan, total_tagihan, terbayar, sisa_tagihan, keterangan, status, created_at) 
                    VALUES (:siswa_id, :judul, :total, :terbayar, :sisa, :ket, :status, :created_at)";
            $stmt = $this->db->prepare($sql);

            // Prepare log statement
            if ($terbayar > 0) {
                $stmtLog = $this->db->prepare("INSERT INTO pembayaran_log (tagihan_id, tanggal_bayar, jumlah_bayar, keterangan) VALUES (?, ?, ?, ?)");
                $kas = new TransaksiKas(); // Instantiate Kas once
            }

            foreach ($students as $siswaId) {
                $stmt->execute([
                    ':siswa_id' => $siswaId,
                    ':judul' => $judul,
                    ':total' => $total,
                    ':terbayar' => $terbayar,
                    ':sisa' => $sisa,
                    ':ket' => $keterangan,
                    ':status' => $status,
                    ':created_at' => $tanggalInput . ' ' . date('H:i:s')
                ]);

                $tagihanId = $this->db->lastInsertId();

                if ($terbayar > 0) {
                    $stmtLog->execute([$tagihanId, $tanggalInput, $terbayar, 'Pembayaran Awal / Cicilan Pertama']);
                    $logId = $this->db->lastInsertId();

                    $kas->create([
                        'tanggal' => $tanggalInput,
                        'jenis_transaksi' => 'Masuk',
                        'kategori' => 'SPP',
                        'keterangan' => 'Pembayaran Siswa (Bulk): ' . $judul . ' - Siswa ID: ' . $siswaId,
                        'nominal' => $terbayar,
                        'created_by' => $_SESSION['user_id'] ?? 1,
                        'ref_type' => 'pembayaran_log',
                        'ref_id' => $logId
                    ]);
                }
            }

            $this->db->commit();
            return count($students);
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Add Payment
    public function addPembayaran($tagihanId, $jumlah, $tanggal, $keterangan = null)
    {
        try {
            $this->db->beginTransaction();

            // 1. Get current bill info
            $stmtCheck = $this->db->prepare("SELECT * FROM tagihan_siswa WHERE id = ? FOR UPDATE");
            $stmtCheck->execute([$tagihanId]);
            $bill = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$bill)
                throw new Exception("Tagihan not found");

            // 2. Validate amount
            if ($jumlah > $bill['sisa_tagihan']) {
                throw new Exception("Jumlah pembayaran melebihi sisa tagihan!");
            }

            // 3. Insert Log
            $stmtLog = $this->db->prepare("INSERT INTO pembayaran_log (tagihan_id, tanggal_bayar, jumlah_bayar, keterangan) VALUES (?, ?, ?, ?)");
            $stmtLog->execute([$tagihanId, $tanggal, $jumlah, $keterangan]);
            $logId = $this->db->lastInsertId();

            // 4. Update Bill
            $newTerbayar = $bill['terbayar'] + $jumlah;
            $newSisa = $bill['sisa_tagihan'] - $jumlah;
            $newStatus = ($newSisa <= 0) ? 'Lunas' : 'Belum Lunas';

            $stmtUpdate = $this->db->prepare("UPDATE tagihan_siswa SET terbayar = ?, sisa_tagihan = ?, status = ? WHERE id = ?");
            $stmtUpdate->execute([$newTerbayar, $newSisa, $newStatus, $tagihanId]);

            // 5. Auto-Input ke Transaksi Kas (Masuk)
            $kas = new TransaksiKas();
            $kas->create([
                'tanggal' => date('Y-m-d', strtotime($bill['created_at'])), // Use the Bill Date (Input Date) instead of Payment Date
                'jenis_transaksi' => 'Masuk',
                'kategori' => 'SPP',
                'keterangan' => 'Pembayaran Tagihan: ' . $bill['judul_tagihan'] . ($keterangan ? " ($keterangan)" : ''),
                'nominal' => $jumlah,
                'created_by' => $_SESSION['user_id'] ?? 1,
                'ref_type' => 'pembayaran_log',
                'ref_id' => $logId
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e; // Re-throw to handle in controller
        }
    }

    // Get Payment History for a bill
    public function getRiwayatPembayaran($tagihanId)
    {
        $stmt = $this->db->prepare("SELECT * FROM pembayaran_log WHERE tagihan_id = ? ORDER BY tanggal_bayar DESC");
        $stmt->execute([$tagihanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single bill details
    public function getTagihanById($id)
    {
        $sql = "SELECT t.*, s.nama_lengkap, s.nisn, k.nama_kelas 
                FROM tagihan_siswa t
                JOIN siswa s ON t.siswa_id = s.id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE t.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get latest payment date for a student (for portal display)
    public function getLatestPaymentDate($siswaId)
    {
        $sql = "SELECT MAX(pl.tanggal_bayar) as latest_date
                FROM pembayaran_log pl
                JOIN tagihan_siswa t ON pl.tagihan_id = t.id
                WHERE t.siswa_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siswaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['latest_date'] ?? null;
    }

    // Delete Bill
    public function deleteTagihan($id)
    {
        try {
            $this->db->beginTransaction();

            // 1. Get linked Logs to delete from Cash Book
            $stmtLogs = $this->db->prepare("SELECT id FROM pembayaran_log WHERE tagihan_id = ?");
            $stmtLogs->execute([$id]);
            $logs = $stmtLogs->fetchAll(PDO::FETCH_COLUMN);

            // 2. Delete from Transaksi Kas
            if (!empty($logs)) {
                $kas = new TransaksiKas();
                foreach ($logs as $logId) {
                    $kas->deleteByRef('pembayaran_log', $logId);
                }
            }

            // 3. Delete Tagihan (Cascade to logs generally, but if not, logs deleted above implicitly or here?)
            // We rely on DB FK cascade for logs, but we must delete tagihan
            $stmt = $this->db->prepare("DELETE FROM tagihan_siswa WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }


    // Get single payment log details
    public function getPembayaranById($id)
    {
        $sql = "SELECT pl.*, t.judul_tagihan, s.nama_lengkap, s.nisn, k.nama_kelas 
                FROM pembayaran_log pl
                JOIN tagihan_siswa t ON pl.tagihan_id = t.id
                JOIN siswa s ON t.siswa_id = s.id
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE pl.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get Financial Summary for Student (Total Bill, Total Paid, Remaining)
    public function getFinancialSummary($siswaId)
    {
        $sql = "SELECT 
                    SUM(total_tagihan) as total_tagihan, 
                    SUM(terbayar) as total_terbayar,
                    SUM(sisa_tagihan) as total_sisa
                FROM tagihan_siswa 
                WHERE siswa_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siswaId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_tagihan' => (float) ($result['total_tagihan'] ?? 0),
            'total_terbayar' => (float) ($result['total_terbayar'] ?? 0),
            'total_sisa' => (float) ($result['total_sisa'] ?? 0)
        ];
    }

    // Get List of Open/Unpaid Bills with details
    public function getOpenBills($siswaId)
    {
        $sql = "SELECT id, judul_tagihan, total_tagihan, terbayar, sisa_tagihan, created_at 
                FROM tagihan_siswa 
                WHERE siswa_id = ? AND status != 'Lunas'
                ORDER BY created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
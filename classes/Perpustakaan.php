<?php
require_once __DIR__ . '/../config/database.php';

class Perpustakaan
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all books
     */
    public function getAllBuku()
    {
        if ($this->db === null)
            return [];
        $stmt = $this->db->query("SELECT * FROM buku ORDER BY judul ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get book by ID
     */
    public function getBukuById($id)
    {
        if ($this->db === null)
            return null;
        $stmt = $this->db->prepare("SELECT * FROM buku WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create book
     */
    public function createBuku($data)
    {
        if ($this->db === null)
            return false;
        $stmt = $this->db->prepare("
            INSERT INTO buku (kode_buku, judul, pengarang, penerbit, tahun_terbit, kategori, stok, lokasi_rak)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['kode_buku'],
            $data['judul'],
            $data['pengarang'],
            $data['penerbit'],
            $data['tahun_terbit'],
            $data['kategori'],
            $data['stok'],
            $data['lokasi_rak'] ?? null
        ]);
    }

    /**
     * Update book
     */
    public function updateBuku($id, $data)
    {
        if ($this->db === null)
            return false;
        $stmt = $this->db->prepare("
            UPDATE buku SET 
            kode_buku = ?, judul = ?, pengarang = ?, penerbit = ?, 
            tahun_terbit = ?, kategori = ?, stok = ?, lokasi_rak = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['kode_buku'],
            $data['judul'],
            $data['pengarang'],
            $data['penerbit'],
            $data['tahun_terbit'],
            $data['kategori'],
            $data['stok'],
            $data['lokasi_rak'] ?? null,
            $id
        ]);
    }

    /**
     * Delete book
     */
    public function deleteBuku($id)
    {
        if ($this->db === null)
            return false;
        $stmt = $this->db->prepare("DELETE FROM buku WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all loans
     */
    public function getAllPeminjaman()
    {
        if ($this->db === null)
            return [];
        $stmt = $this->db->query("
            SELECT p.*, b.judul, b.kode_buku, s.nama_lengkap as nama_siswa, s.nisn 
            FROM peminjaman_buku p
            JOIN buku b ON p.buku_id = b.id
            JOIN siswa s ON p.siswa_id = s.id
            ORDER BY p.tanggal_pinjam DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Create loan
     */
    public function createPeminjaman($data)
    {
        if ($this->db === null)
            return false;

        try {
            $this->db->beginTransaction();

            // Check stock
            $buku = $this->getBukuById($data['buku_id']);
            if ($buku['stok'] <= 0) {
                throw new Exception("Stok buku habis");
            }

            // Create loan
            $stmt = $this->db->prepare("
                INSERT INTO peminjaman_buku (buku_id, siswa_id, tanggal_pinjam, tanggal_kembali_rencana, status)
                VALUES (?, ?, ?, ?, 'Dipinjam')
            ");
            $stmt->execute([
                $data['buku_id'],
                $data['siswa_id'],
                $data['tanggal_pinjam'],
                $data['tanggal_kembali_rencana']
            ]);

            // Decrease stock
            $this->db->prepare("UPDATE buku SET stok = stok - 1 WHERE id = ?")->execute([$data['buku_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Return book
     */
    public function kembalikanBuku($id, $tanggalKembali)
    {
        if ($this->db === null)
            return false;

        try {
            $this->db->beginTransaction();

            // Get loan info
            $stmt = $this->db->prepare("SELECT * FROM peminjaman_buku WHERE id = ?");
            $stmt->execute([$id]);
            $loan = $stmt->fetch();

            if (!$loan || $loan['status'] === 'Kembali') {
                return false;
            }

            // Update loan status
            $stmt = $this->db->prepare("
                UPDATE peminjaman_buku SET status = 'Kembali', tanggal_kembali_realisasi = ? WHERE id = ?
            ");
            $stmt->execute([$tanggalKembali, $id]);

            // Increase stock
            $this->db->prepare("UPDATE buku SET stok = stok + 1 WHERE id = ?")->execute([$loan['buku_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get stats
     */
    public function getStats()
    {
        if ($this->db === null)
            return ['total_buku' => 0, 'dipinjam' => 0, 'kunjungan_hari_ini' => 0];

        $stats = [];
        $stats['total_buku'] = $this->db->query("SELECT COUNT(*) FROM buku")->fetchColumn();
        $stats['dipinjam'] = $this->db->query("SELECT COUNT(*) FROM peminjaman_buku WHERE status = 'Dipinjam'")->fetchColumn();

        // Count today's visits
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM kunjungan_perpustakaan WHERE tanggal = ?");
        $stmt->execute([$today]);
        $stats['kunjungan_hari_ini'] = $stmt->fetchColumn();

        // Count total visits
        $stats['total_kunjungan'] = $this->db->query("SELECT COUNT(*) FROM kunjungan_perpustakaan")->fetchColumn();

        return $stats;
    }

    /**
     * Create visit record
     */
    public function createKunjungan($siswaId, $keperluan = 'Membaca')
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("
            INSERT INTO kunjungan_perpustakaan (siswa_id, tanggal, waktu, keperluan)
            VALUES (?, CURRENT_DATE, CURRENT_TIME, ?)
        ");
        return $stmt->execute([$siswaId, $keperluan]);
    }

    /**
     * Get visits (default today)
     */
    public function getAllKunjungan($tanggal = null)
    {
        if ($this->db === null)
            return [];

        $sql = "
            SELECT k.*, s.nama_lengkap as nama_siswa, s.nisn, s.kelas_id, kl.nama_kelas
            FROM kunjungan_perpustakaan k
            JOIN siswa s ON k.siswa_id = s.id
            LEFT JOIN kelas kl ON s.kelas_id = kl.id
        ";

        $params = [];
        if ($tanggal) {
            $sql .= " WHERE k.tanggal = ?";
            $params[] = $tanggal;
        }

        // Order by Date DESC, Time DESC
        $sql .= " ORDER BY k.tanggal DESC, k.waktu DESC LIMIT 100";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Delete visit record
     */
    public function deleteKunjungan($id)
    {
        if ($this->db === null)
            return false;

        $stmt = $this->db->prepare("DELETE FROM kunjungan_perpustakaan WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

class Siswa
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all siswa
     */
    public function getAll($limit = null, $offset = 0)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT s.*, k.nama_kelas 
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE s.status = 'Aktif'
                ORDER BY s.nama_lengkap ASC";

        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get siswa by ID
     */
    public function getById($id)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get siswa by NISN
     */
    public function getByNISN($nisn)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE s.nisn = ?
        ");
        $stmt->execute([$nisn]);
        return $stmt->fetch();
    }

    /**
     * Create new siswa
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        // Check duplicate NISN
        if ($this->getByNISN($data['nisn'])) {
            throw new Exception("NISN {$data['nisn']} sudah terdaftar!");
        }

        $stmt = $this->db->prepare("
            INSERT INTO siswa 
            (nisn, nama_lengkap, kelas_id, tahun_masuk, jenis_kelamin, tempat_lahir, 
             tanggal_lahir, alamat, nama_ortu, no_hp_ortu, foto, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')
        ");

        return $stmt->execute([
            $data['nisn'],
            $data['nama_lengkap'],
            $data['kelas_id'] ?? null,
            $data['tahun_masuk'],
            $data['jenis_kelamin'],
            $data['tempat_lahir'] ?? null,
            $data['tanggal_lahir'] ?? null,
            $data['alamat'] ?? null,
            $data['nama_ortu'] ?? null,
            $data['no_hp_ortu'] ?? null,
            $data['foto'] ?? null
        ]);
    }

    /**
     * Create new alumni
     */
    public function createAlumni($data)
    {
        if ($this->db === null) {
            return false;
        }

        // Check duplicate NISN
        if ($this->getByNISN($data['nisn'])) {
            throw new Exception("NISN {$data['nisn']} sudah terdaftar!");
        }

        $stmt = $this->db->prepare("
            INSERT INTO siswa 
            (nisn, nama_lengkap, tahun_masuk, tahun_lulus, jenis_kelamin, tempat_lahir, 
             tanggal_lahir, alamat, nama_ortu, no_hp_ortu, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Lulus')
        ");

        return $stmt->execute([
            $data['nisn'],
            $data['nama_lengkap'],
            $data['tahun_masuk'],
            $data['tahun_lulus'],
            $data['jenis_kelamin'],
            $data['tempat_lahir'] ?? null,
            $data['tanggal_lahir'] ?? null,
            $data['alamat'] ?? null,
            $data['nama_ortu'] ?? null,
            $data['no_hp_ortu'] ?? null
        ]);
    }

    /**
     * Update siswa
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $sql = "UPDATE siswa SET 
                nisn = ?, 
                nama_lengkap = ?, 
                kelas_id = ?, 
                tahun_masuk = ?, 
                jenis_kelamin = ?, 
                tempat_lahir = ?, 
                tanggal_lahir = ?, 
                alamat = ?, 
                nama_ortu = ?, 
                no_hp_ortu = ?";

        $params = [
            $data['nisn'],
            $data['nama_lengkap'],
            $data['kelas_id'] ?? null,
            $data['tahun_masuk'],
            $data['jenis_kelamin'],
            $data['tempat_lahir'] ?? null,
            $data['tanggal_lahir'] ?? null,
            $data['alamat'] ?? null,
            $data['nama_ortu'] ?? null,
            $data['no_hp_ortu'] ?? null
        ];

        if (isset($data['foto'])) {
            $sql .= ", foto = ?";
            $params[] = $data['foto'];
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete siswa (soft delete)
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("UPDATE siswa SET status = 'Keluar' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search siswa
     */
    public function search($keyword)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE (s.nisn LIKE ? OR s.nama_lengkap LIKE ?)
            AND s.status = 'Aktif'
            ORDER BY s.nama_lengkap ASC
        ");

        $searchTerm = "%$keyword%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    /**
     * Get filtered siswa (search, kelas, status)
     */
    public function getFiltered($search = '', $kelasId = '', $status = '')
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT s.*, k.nama_kelas 
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (s.nisn LIKE ? OR s.nama_lengkap LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($kelasId)) {
            $sql .= " AND s.kelas_id = ?";
            $params[] = $kelasId;
        }

        if (!empty($status)) {
            $sql .= " AND s.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY s.nama_lengkap ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Search siswa by name (for Autocomplete)
     */
    public function searchByName($name, $limit = 10)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT s.id, s.nama_lengkap, k.nama_kelas 
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE s.nama_lengkap LIKE ? AND s.status = 'Aktif'
            ORDER BY s.nama_lengkap ASC
            LIMIT ?
        ");

        $searchTerm = "%$name%";
        $stmt->bindParam(1, $searchTerm);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get by kelas
     */
    public function getByKelas($kelasId)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT s.*, k.nama_kelas 
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            WHERE s.kelas_id = ? AND s.status = 'Aktif'
            ORDER BY s.nama_lengkap ASC
        ");
        $stmt->execute([$kelasId]);
        return $stmt->fetchAll();
    }

    /**
     * Get total count
     */
    public function getTotalCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE status = 'Aktif'");
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Get statistics by gender
     */
    public function getStatsByGender()
    {
        if ($this->db === null) {
            return ['L' => 0, 'P' => 0];
        }

        $stmt = $this->db->query("
            SELECT jenis_kelamin, COUNT(*) as total 
            FROM siswa 
            WHERE status = 'Aktif' 
            GROUP BY jenis_kelamin
        ");

        $stats = ['L' => 0, 'P' => 0];
        while ($row = $stmt->fetch()) {
            $stats[$row['jenis_kelamin']] = $row['total'];
        }

        return $stats;
    }
    /**
     * Get count by kelas
     */
    public function getCountByKelas($kelasId)
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM siswa WHERE kelas_id = ? AND status = 'Aktif'");
        $stmt->execute([$kelasId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get new students count (current year)
     */
    public function getNewStudentCount()
    {
        if ($this->db === null) {
            return 0;
        }

        $currentYear = date('Y');
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM siswa WHERE tahun_masuk = ? AND status = 'Aktif'");
        $stmt->execute([$currentYear]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Graduate a student
     */
    public function graduateStudent($id, $tahunLulus)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE siswa 
            SET status = 'Lulus', 
                tahun_lulus = ?, 
                kelas_id = NULL 
            WHERE id = ?
        ");
        return $stmt->execute([$tahunLulus, $id]);
    }

    /**
     * Get all alumni (graduated students)
     */
    public function getAllAlumni($tahunLulus = null, $limit = null, $offset = 0)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT s.*, k.nama_kelas as kelas_terakhir
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                WHERE s.status = 'Lulus'";

        if ($tahunLulus) {
            $sql .= " AND s.tahun_lulus = :tahun";
        }

        $sql .= " ORDER BY s.tahun_lulus DESC, s.nama_lengkap ASC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $this->db->prepare($sql);

        if ($tahunLulus) {
            $stmt->bindParam(':tahun', $tahunLulus, PDO::PARAM_INT);
        }

        if ($limit !== null) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get alumni statistics
     */
    public function getAlumniStats()
    {
        if ($this->db === null) {
            return ['total' => 0, 'by_year' => []];
        }

        // Total alumni
        $stmtTotal = $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE status = 'Lulus'");
        $total = $stmtTotal->fetch()['total'];

        // By year
        $stmtByYear = $this->db->query("
            SELECT tahun_lulus, COUNT(*) as jumlah 
            FROM siswa 
            WHERE status = 'Lulus' AND tahun_lulus IS NOT NULL
            GROUP BY tahun_lulus 
            ORDER BY tahun_lulus DESC
        ");
        $byYear = $stmtByYear->fetchAll();

        return [
            'total' => $total,
            'by_year' => $byYear
        ];
    }
}

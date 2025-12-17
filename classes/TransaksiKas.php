<?php
require_once __DIR__ . '/../config/database.php';

class TransaksiKas
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all transaksi
     */
    public function getAll($limit = null, $offset = 0)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT tk.*, u.username as created_by_name 
                FROM transaksi_kas tk
                LEFT JOIN users u ON tk.created_by = u.id
                ORDER BY tk.tanggal DESC, tk.id DESC";

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
     * Get transaksi by period
     */
    public function getByPeriod($startDate, $endDate)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT tk.*, u.username as created_by_name 
            FROM transaksi_kas tk
            LEFT JOIN users u ON tk.created_by = u.id
            WHERE tk.tanggal BETWEEN ? AND ?
            ORDER BY tk.tanggal DESC, tk.id DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    /**
     * Create transaksi
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        // Calculate new saldo
        $saldoSebelum = $this->getSaldoAkhir();
        $saldoBaru = $data['jenis_transaksi'] === 'Masuk'
            ? $saldoSebelum + $data['nominal']
            : $saldoSebelum - $data['nominal'];

        $stmt = $this->db->prepare("
            INSERT INTO transaksi_kas 
            (tanggal, jenis_transaksi, kategori, keterangan, nominal, saldo, created_by, ref_type, ref_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['tanggal'],
            $data['jenis_transaksi'],
            $data['kategori'],
            $data['keterangan'],
            $data['nominal'],
            $saldoBaru,
            $data['created_by'],
            $data['ref_type'] ?? null,
            $data['ref_id'] ?? null
        ]);
    }

    /**
     * Delete transaksi by Reference
     */
    public function deleteByRef($refType, $refId)
    {
        if ($this->db === null) {
            return false;
        }

        // Get IDs to delete for recalculation? 
        // Or just delete and recalc everything.

        $stmt = $this->db->prepare("DELETE FROM transaksi_kas WHERE ref_type = ? AND ref_id = ?");
        $result = $stmt->execute([$refType, $refId]);

        if ($result && $stmt->rowCount() > 0) {
            $this->recalculateSaldo();
        }

        return $result;
    }

    /**
     * Update transaksi
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE transaksi_kas SET 
            tanggal = ?, 
            jenis_transaksi = ?, 
            kategori = ?, 
            keterangan = ?, 
            nominal = ?
            WHERE id = ?
        ");

        $result = $stmt->execute([
            $data['tanggal'],
            $data['jenis_transaksi'],
            $data['kategori'],
            $data['keterangan'],
            $data['nominal'],
            $id
        ]);

        // Recalculate saldo for all transactions after this date
        if ($result) {
            $this->recalculateSaldo();
        }

        return $result;
    }

    /**
     * Delete transaksi
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM transaksi_kas WHERE id = ?");
        $result = $stmt->execute([$id]);

        if ($result) {
            $this->recalculateSaldo();
        }

        return $result;
    }

    /**
     * Get saldo akhir
     */
    public function getSaldoAkhir()
    {
        if ($this->db === null) {
            return 0;
        }

        $stmt = $this->db->query("
            SELECT saldo 
            FROM transaksi_kas 
            ORDER BY tanggal DESC, id DESC 
            LIMIT 1
        ");
        $result = $stmt->fetch();

        return $result ? $result['saldo'] : 0;
    }

    /**
     * Recalculate all saldo
     */
    private function recalculateSaldo()
    {
        if ($this->db === null) {
            return false;
        }

        $transaksi = $this->db->query("
            SELECT * FROM transaksi_kas 
            ORDER BY tanggal ASC, id ASC
        ")->fetchAll();

        $saldo = 0;
        foreach ($transaksi as $t) {
            $saldo = $t['jenis_transaksi'] === 'Masuk'
                ? $saldo + $t['nominal']
                : $saldo - $t['nominal'];

            $stmt = $this->db->prepare("UPDATE transaksi_kas SET saldo = ? WHERE id = ?");
            $stmt->execute([$saldo, $t['id']]);
        }

        return true;
    }

    /**
     * Get total pemasukan dalam periode
     */
    public function getTotalPemasukan($startDate = null, $endDate = null)
    {
        if ($this->db === null) {
            return 0;
        }

        $sql = "SELECT SUM(nominal) as total 
                FROM transaksi_kas 
                WHERE jenis_transaksi = 'Masuk'";

        if ($startDate && $endDate) {
            $sql .= " AND tanggal BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
        } else {
            $stmt = $this->db->query($sql);
        }

        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get total pengeluaran dalam periode
     */
    public function getTotalPengeluaran($startDate = null, $endDate = null)
    {
        if ($this->db === null) {
            return 0;
        }

        $sql = "SELECT SUM(nominal) as total 
                FROM transaksi_kas 
                WHERE jenis_transaksi = 'Keluar'";

        if ($startDate && $endDate) {
            $sql .= " AND tanggal BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
        } else {
            $stmt = $this->db->query($sql);
        }

        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get transaksi by kategori
     */
    public function getByKategori($kategori)
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare("
            SELECT tk.*, u.username as created_by_name 
            FROM transaksi_kas tk
            LEFT JOIN users u ON tk.created_by = u.id
            WHERE tk.kategori = ?
            ORDER BY tk.tanggal DESC
        ");
        $stmt->execute([$kategori]);
        return $stmt->fetchAll();
    }
}

<?php
require_once __DIR__ . '/../config/database.php';

class TemplateSurat
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all templates
     */
    public function getAll($kategori = null)
    {
        if ($this->db === null) {
            return [];
        }

        $sql = "SELECT * FROM template_surat WHERE 1=1";
        $params = [];

        if ($kategori) {
            $sql .= " AND kategori = ?";
            $params[] = $kategori;
        }

        $sql .= " ORDER BY nama_template ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get by ID
     */
    public function getById($id)
    {
        if ($this->db === null) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT * FROM template_surat WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create template
     */
    public function create($data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            INSERT INTO template_surat 
            (nama_template, kode_surat, kategori, konten_template, variabel)
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['nama_template'],
            $data['kode_surat'],
            $data['kategori'] ?? 'Umum',
            $data['konten_template'],
            $data['variabel'] ?? '[]'
        ]);
    }

    /**
     * Update template
     */
    public function update($id, $data)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE template_surat SET 
            nama_template = ?, 
            kode_surat = ?, 
            kategori = ?,
            konten_template = ?,
            variabel = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['nama_template'],
            $data['kode_surat'],
            $data['kategori'],
            $data['konten_template'],
            $data['variabel'] ?? '[]',
            $id
        ]);
    }

    /**
     * Delete template
     */
    public function delete($id)
    {
        if ($this->db === null) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM template_surat WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Generate surat dari template
     */
    public function generateSurat($templateId, $data)
    {
        $template = $this->getById($templateId);
        if (!$template) {
            return null;
        }

        $konten = $template['konten_template'];

        // Replace variabel dengan data
        foreach ($data as $key => $value) {
            $konten = str_replace('{{' . strtoupper($key) . '}}', $value, $konten);
        }

        return [
            'template' => $template,
            'konten' => $konten,
            'data' => $data
        ];
    }

    /**
     * Parse variabel dari template
     */
    public function parseVariabel($kontenTemplate)
    {
        preg_match_all('/\{\{([A-Z_]+)\}\}/', $kontenTemplate, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Generate nomor surat otomatis
     */
    public function generateNomorSurat($kodeSurat)
    {
        if ($this->db === null) {
            return $kodeSurat . '/001/' . date('m') . '/' . date('Y');
        }

        // Count surat keluar this month
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total FROM surat_keluar 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute();
        $result = $stmt->fetch();
        $urutan = ($result['total'] ?? 0) + 1;

        return sprintf(
            '%03d/%s/TU/%s/%s',
            $urutan,
            $kodeSurat,
            date('m'),
            date('Y')
        );
    }
}

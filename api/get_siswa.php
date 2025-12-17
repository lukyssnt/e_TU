<?php
require_once __DIR__ . '/../classes/Siswa.php';

header('Content-Type: application/json');

if (isset($_GET['query'])) {
    $siswa = new Siswa();
    $keyword = $_GET['query'];
    
    // Only search if 3+ chars
    if (strlen($keyword) < 1) {
        echo json_encode([]);
        exit;
    }

    $results = $siswa->searchByName($keyword, 10);
    
    // Format for autocomplete
    $data = array_map(function($s) {
        return [
            'id' => $s['id'],
            'label' => $s['nama_lengkap'] . ' (' . ($s['nama_kelas'] ?? 'No Class') . ')',
            'value' => $s['nama_lengkap'],
            'kelas_id' => $s['id'] // Actually we need student ID
        ];
    }, $results);
    
    echo json_encode($data);
}
?>

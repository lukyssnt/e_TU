<?php
require_once __DIR__ . '/../classes/Pembayaran.php';
require_once __DIR__ . '/../classes/Siswa.php';
require_once __DIR__ . '/../classes/Portal.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Check Feature Status
    $portal = new Portal();
    $content = $portal->getAllContent();
    $isFeatureActive = isset($content['finance_feature_status']) ? ($content['finance_feature_status']['content_value'] == '1') : true;

    if (!$isFeatureActive) {
        echo json_encode([
            'success' => false,
            'message' => 'Layanan Cek Keuangan sedang ditutup sementara oleh Admin.'
        ]);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $siswaId = $input['siswa_id'] ?? null;

    if (!$siswaId) {
        echo json_encode(['success' => false, 'message' => 'Siswa tidak ditemukan']);
        exit;
    }

    $pembayaran = new Pembayaran();
    $summary = $pembayaran->getFinancialSummary($siswaId);
    $details = $pembayaran->getOpenBills($siswaId); // Get detailed list

    $siswa = new Siswa();
    $sData = $siswa->getById($siswaId);

    // Get latest payment date from bendahara input
    $latestDate = $pembayaran->getLatestPaymentDate($siswaId);
    $displayDate = $latestDate ? date('d-m-Y', strtotime($latestDate)) : date('d-m-Y');

    echo json_encode([
        'success' => true,
        'student' => $sData['nama_lengkap'],
        'server_time' => $displayDate,
        'summary' => $summary,
        'details' => $details
    ]);
}
?>
<?php
/**
 * Helper Functions
 * Common utility functions for E-ADMIN TU MA AL IHSAN
 */

/**
 * Format tanggal Indonesia
 */
function formatTanggal($date, $format = 'long')
{
    if (empty($date))
        return '-';

    $timestamp = strtotime($date);

    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    if ($format === 'long') {
        return date('d', $timestamp) . ' ' . $bulan[date('n', $timestamp)] . ' ' . date('Y', $timestamp);
    } elseif ($format === 'short') {
        return date('d/m/Y', $timestamp);
    } else {
        return date('Y-m-d', $timestamp);
    }
}

/**
 * Format rupiah
 */
function formatRupiah($angka)
{
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Upload file handler
 */
function uploadFile($file, $targetDir = 'uploads/', $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'])
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }

    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileTmp = $file['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($fileExt, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }

    // Validate size (max 5MB)
    if ($fileSize > 5242880) {
        return ['success' => false, 'message' => 'File size too large (max 5MB)'];
    }

    // Create unique filename
    $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
    $uploadPath = __DIR__ . '/../' . $targetDir;

    // Create directory if not exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }

    $targetFile = $uploadPath . $newFileName;

    if (move_uploaded_file($fileTmp, $targetFile)) {
        return [
            'success' => true,
            'filename' => $newFileName,
            'path' => $targetDir . $newFileName
        ];
    }

    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Generate nomor surat otomatis
 */
function generateNomorSurat($kodeSurat = 'SKS', $unit = 'TU')
{
    require_once __DIR__ . '/../config/database.php';

    $db = Database::getInstance()->getConnection();
    if ($db === null) {
        // Fallback jika database tidak tersedia
        return '001/' . $kodeSurat . '/' . $unit . '/' . strtoupper(bulanRomawi(date('n'))) . '/' . date('Y');
    }

    $tahun = date('Y');
    $bulan = date('n');

    // Get last number for this month and year
    $stmt = $db->prepare("
        SELECT nomor_surat 
        FROM surat_keluar 
        WHERE YEAR(tanggal_surat) = ? 
        AND MONTH(tanggal_surat) = ?
        AND nomor_surat LIKE ?
        ORDER BY id DESC 
        LIMIT 1
    ");

    $pattern = '%/' . $kodeSurat . '/%';
    $stmt->execute([$tahun, $bulan, $pattern]);
    $lastSurat = $stmt->fetch();

    if ($lastSurat) {
        // Extract number from format: 001/SKS/TU/XII/2024
        $parts = explode('/', $lastSurat['nomor_surat']);
        $lastNumber = intval($parts[0]);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    $nomorUrut = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    $bulanRomawi = bulanRomawi($bulan);

    return $nomorUrut . '/' . $kodeSurat . '/' . $unit . '/' . $bulanRomawi . '/' . $tahun;
}

/**
 * Convert bulan ke romawi
 */
function bulanRomawi($bulan)
{
    $romawi = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
    return $romawi[$bulan];
}

/**
 * Sanitize input
 */
function clean($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect helper
 */
function redirect($url, $message = '', $type = 'success')
{
    require_once __DIR__ . '/../config/session.php';

    if (!empty($message)) {
        Session::setFlash($type, $message);
    }

    if (headers_sent()) {
        echo '<script>window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
    } else {
        header('Location: ' . $url);
    }
    exit;
}

/**
 * Check if user has permission for module
 */
function checkPermission($module)
{
    require_once __DIR__ . '/../config/session.php';

    if (!Session::hasPermission($module)) {
        redirect('/e-TU/index.php', 'Anda tidak memiliki akses ke modul ini!', 'error');
    }
}

/**
 * Get file icon by extension
 */
function getFileIcon($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    $icons = [
        'pdf' => 'fa-file-pdf text-red-600',
        'doc' => 'fa-file-word text-blue-600',
        'docx' => 'fa-file-word text-blue-600',
        'xls' => 'fa-file-excel text-green-600',
        'xlsx' => 'fa-file-excel text-green-600',
        'jpg' => 'fa-file-image text-purple-600',
        'jpeg' => 'fa-file-image text-purple-600',
        'png' => 'fa-file-image text-purple-600',
    ];

    return $icons[$ext] ?? 'fa-file text-gray-600';
}

/**
 * Format file size
 */
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Get time ago
 */
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return $diff . ' detik yang lalu';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' menit yang lalu';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' jam yang lalu';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' hari yang lalu';
    } else {
        return formatTanggal($datetime, 'short');
    }
}
/**
 * Log user activity
 */
function logActivity($action, $module, $description = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../classes/ActivityLog.php';
        $log = new ActivityLog();
        $log->log($_SESSION['user_id'], $action, $module, $description);
    }
}

/**
 * Konversi angka ke terbilang (Indonesia)
 */
function terbilang($angka)
{
    $angka = (int) abs($angka);
    $baca = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
    $terbilang = "";

    if ($angka < 12) {
        $terbilang = " " . $baca[$angka];
    } else if ($angka < 20) {
        $terbilang = terbilang($angka - 10) . " Belas";
    } else if ($angka < 100) {
        $terbilang = terbilang($angka / 10) . " Puluh" . terbilang($angka % 10);
    } else if ($angka < 200) {
        $terbilang = " Seratus" . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $terbilang = terbilang($angka / 100) . " Ratus" . terbilang($angka % 100);
    } else if ($angka < 2000) {
        $terbilang = " Seribu" . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $terbilang = terbilang($angka / 1000) . " Ribu" . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $terbilang = terbilang($angka / 1000000) . " Juta" . terbilang($angka % 1000000);
    }

    return trim($terbilang);
}

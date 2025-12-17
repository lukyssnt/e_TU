<?php
/**
 * AJAX Handler for Document Upload
 * This file handles AJAX upload requests separately to avoid output buffer issues
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/DokumenSiswa.php';

checkPermission('admin');

// Set JSON header immediately
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Clear any output buffer
while (ob_get_level()) {
    ob_end_clean();
}

$dokumenSiswa = new DokumenSiswa();

// Handle upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'upload_dokumen':
                $siswaId = $_POST['siswa_id'] ?? null;
                $jenisDokumen = clean($_POST['jenis_dokumen'] ?? '');
                $namaDokumen = $jenisDokumen === 'lainnya' ? clean($_POST['nama_dokumen'] ?? '') : null;

                if (!$siswaId) {
                    throw new Exception('Siswa ID tidak ditemukan');
                }

                if (!isset($_FILES['dokumen']) || $_FILES['dokumen']['error'] !== 0) {
                    $errorMsg = 'File tidak ditemukan atau error upload';
                    if (isset($_FILES['dokumen']['error'])) {
                        $errorMsg .= ' (Error code: ' . $_FILES['dokumen']['error'] . ')';
                    }
                    throw new Exception($errorMsg);
                }

                $result = $dokumenSiswa->upload($siswaId, $jenisDokumen, $_FILES['dokumen'], $namaDokumen);
                echo json_encode($result);
                break;

            case 'delete_dokumen':
                $dokumenId = $_POST['dokumen_id'] ?? null;

                if (!$dokumenId) {
                    throw new Exception('Dokumen ID tidak ditemukan');
                }

                $success = $dokumenSiswa->delete($dokumenId);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Dokumen berhasil dihapus!' : 'Gagal menghapus dokumen!'
                ]);
                break;

            case 'get_dokumen':
                $siswaId = $_POST['siswa_id'] ?? null;

                if (!$siswaId) {
                    throw new Exception('Siswa ID tidak ditemukan');
                }

                $dokumen = $dokumenSiswa->getBySiswa($siswaId);
                echo json_encode([
                    'success' => true,
                    'dokumen' => $dokumen
                ]);
                break;

            default:
                throw new Exception('Action tidak valid');
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

exit;

<?php
ob_start();

// Include classes first for AJAX handling
require_once __DIR__ . '/../../classes/Siswa.php';
require_once __DIR__ . '/../../classes/DokumenSiswa.php';
require_once __DIR__ . '/../../classes/Kelas.php';
require_once __DIR__ . '/../../includes/functions.php'; // Add this for clean() function

$siswa = new Siswa();
$dokumenSiswa = new DokumenSiswa();
$kelas = new Kelas();

// Handle CSV template download
if (isset($_GET['action']) && $_GET['action'] === 'download_template') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="template_siswa.csv"');

    // Clean output buffer
    if (ob_get_level())
        ob_end_clean();

    $output = fopen('php://output', 'w');

    // Add separator hint for Excel
    fwrite($output, "sep=;\n");

    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel UTF-8

    // Header row
    fputcsv($output, ['NISN', 'Nama Lengkap', 'Kelas ID', 'Tahun Masuk', 'Jenis Kelamin (L/P)', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Alamat', 'Nama Ortu', 'No HP Ortu'], ';', '"', '\\');

    // Example row
    fputcsv($output, ['0123456789', 'Andi Pratama', '1', '2024', 'L', 'Jakarta', '2010-05-15', 'Jl. Merdeka No. 10', 'Pak Budi', '08123456789'], ';', '"', '\\');

    fclose($output);
    exit;
}

// Handle CSV export
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_siswa_' . date('Y-m-d') . '.csv"');

    // Clean output buffer
    if (ob_get_level())
        ob_end_clean();

    $output = fopen('php://output', 'w');

    // Add separator hint for Excel
    fwrite($output, "sep=;\n");

    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

    // Header
    fputcsv($output, ['NISN', 'Nama Lengkap', 'Kelas', 'Tahun Masuk', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir', 'Alamat', 'Nama Ortu', 'No HP Ortu'], ';', '"', '\\');

    // Data
    $allStudents = $siswa->getAll();
    foreach ($allStudents as $s) {
        fputcsv($output, [
            $s['nisn'],
            $s['nama_lengkap'],
            $s['nama_kelas'] ?? '',
            $s['tahun_masuk'],
            $s['jenis_kelamin'],
            $s['tempat_lahir'] ?? '',
            $s['tanggal_lahir'] ?? '',
            $s['alamat'] ?? '',
            $s['nama_ortu'] ?? '',
            $s['no_hp_ortu'] ?? '',
        ], ';', '"', '\\');
    }

    fclose($output);
    exit;
}

// Handle AJAX request FIRST before any HTML output
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_dokumen' && isset($_GET['siswa_id'])) {
    header('Content-Type: application/json');
    $dokumen = $dokumenSiswa->getBySiswa($_GET['siswa_id']);
    echo json_encode(['dokumen' => $dokumen]);
    exit;
}

$message = '';
$messageType = 'success';

// Handle form submissions BEFORE HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'import':
            if (isset($_FILES['file_import']) && $_FILES['file_import']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file_import']['tmp_name'];
                $handle = fopen($file, "r");

                // Skip header
                fgetcsv($handle, 0, ';');

                $successCount = 0;
                $errorCount = 0;

                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    // Map CSV columns
                    $data = [
                        'nisn' => trim($row[0] ?? ''),
                        'nama_lengkap' => trim($row[1] ?? ''),
                        'kelas_id' => !empty($row[2]) ? intval($row[2]) : null,
                        'tahun_masuk' => trim($row[3] ?? date('Y')),
                        'jenis_kelamin' => trim($row[4] ?? ''),
                        'tempat_lahir' => trim($row[5] ?? ''),
                        'tanggal_lahir' => trim($row[6] ?? ''),
                        'alamat' => trim($row[7] ?? ''),
                        'nama_ortu' => trim($row[8] ?? ''),
                        'no_hp_ortu' => trim($row[9] ?? ''),
                        'foto' => null
                    ];

                    // Validation
                    if (empty($data['nisn']) || empty($data['nama_lengkap'])) {
                        $errorCount++;
                        continue;
                    }

                    if ($siswa->create($data)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }

                fclose($handle);

                $message = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
                $messageType = $errorCount > 0 ? 'warning' : 'success';
            } else {
                $message = 'Gagal mengupload file!';
                $messageType = 'error';
            }
            break;

        case 'create':
            $fotoUpload = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $uploadDir = __DIR__ . '/../../uploads/siswa/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0755, true);
                $fileName = time() . '_' . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                    $fotoUpload = $fileName;
                }
            }

            try {
                $result = $siswa->create([
                    'nisn' => $_POST['nisn'],
                    'nama_lengkap' => $_POST['nama_lengkap'],
                    'kelas_id' => $_POST['kelas_id'] ?: null,
                    'tahun_masuk' => $_POST['tahun_masuk'],
                    'jenis_kelamin' => $_POST['jenis_kelamin'],
                    'tempat_lahir' => $_POST['tempat_lahir'] ?: null,
                    'tanggal_lahir' => $_POST['tanggal_lahir'] ?: null,
                    'alamat' => $_POST['alamat'] ?: null,
                    'nama_ortu' => $_POST['nama_ortu'] ?: null,
                    'no_hp_ortu' => $_POST['no_hp_ortu'] ?: null,
                    'foto' => $fotoUpload
                ]);

                if ($result) {
                    $message = 'Siswa berhasil ditambahkan!';
                    $messageType = 'success';
                }
            } catch (Exception $e) {
                $message = $e->getMessage();
                $messageType = 'error';
            }
            break;

        case 'update':
            $fotoUpload = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $uploadDir = __DIR__ . '/../../uploads/siswa/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0755, true);
                $fileName = time() . '_' . basename($_FILES['foto']['name']);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fileName)) {
                    $fotoUpload = $fileName;
                }
            }

            $data = [
                'nisn' => $_POST['nisn'],
                'nama_lengkap' => $_POST['nama_lengkap'],
                'kelas_id' => $_POST['kelas_id'] ?: null,
                'tahun_masuk' => $_POST['tahun_masuk'],
                'jenis_kelamin' => $_POST['jenis_kelamin'],
                'tempat_lahir' => $_POST['tempat_lahir'] ?: null,
                'tanggal_lahir' => $_POST['tanggal_lahir'] ?: null,
                'alamat' => $_POST['alamat'] ?: null,
                'nama_ortu' => $_POST['nama_ortu'] ?: null,
                'no_hp_ortu' => $_POST['no_hp_ortu'] ?: null,
            ];
            if ($fotoUpload)
                $data['foto'] = $fotoUpload;

            if ($siswa->update($_POST['id'], $data)) {
                $message = 'Data siswa berhasil diperbarui!';
                $messageType = 'success';
                // Redirect to refresh page and show updated data
                header('Location: ' . $_SERVER['PHP_SELF'] . '?updated=1');
                exit;
            } else {
                $message = 'Gagal memperbarui data!';
                $messageType = 'error';
            }
            break;

        case 'delete':
            if ($siswa->delete($_POST['id'])) {
                $message = 'Siswa berhasil dihapus!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menghapus siswa!';
                $messageType = 'error';
            }
            break;

        case 'upload_dokumen':
            $siswaId = $_POST['siswa_id'];
            $jenisDok = $_POST['jenis_dokumen'];
            $namaDokumen = $_POST['nama_dokumen'] ?? null;

            if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === 0) {
                $result = $dokumenSiswa->upload($siswaId, $jenisDok, $_FILES['dokumen'], $namaDokumen);
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';

                    // If AJAX request, return JSON
                    if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => $message]);
                        exit;
                    }
                } else {
                    $message = $result['message'];
                    $messageType = 'error';

                    // If AJAX request, return JSON
                    if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $message]);
                        exit;
                    }
                }
            } else {
                $message = 'Pilih file untuk diupload!';
                $messageType = 'error';

                // If AJAX request, return JSON
                if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
            }
            break;

        case 'delete_dokumen':
            if ($dokumenSiswa->delete($_POST['dokumen_id'])) {
                $message = 'Dokumen berhasil dihapus!';
                $messageType = 'success';

                // If AJAX request, return JSON
                if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => $message]);
                    exit;
                }
            } else {
                $message = 'Gagal menghapus dokumen!';
                $messageType = 'error';

                // If AJAX request, return JSON
                if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
            }
            break;

        case 'graduate':
            $tahunLulus = $_POST['tahun_lulus'] ?? date('Y');
            if ($siswa->graduateStudent($_POST['id'], $tahunLulus)) {
                $message = 'Siswa berhasil diluluskan!';
                $messageType = 'success';
            } else {
                $message = 'Gagal meluluskan siswa!';
                $messageType = 'error';
            }
            break;
    }
}

// NOW safe to include header and output HTML
$pageTitle = 'Data Siswa - Kesiswaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';

checkPermission('kesiswaan');

// Check for success message from redirect
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $message = 'Data siswa berhasil diperbarui!';
    $messageType = 'success';
}

// Get data
$filterKelas = $_GET['kelas'] ?? null;
$allSiswa = $filterKelas ? $siswa->getByKelas($filterKelas) : $siswa->getAll();
$stats = $siswa->getStatsByGender();
$totalSiswa = $siswa->getTotalCount();
$kelasList = $kelas->getAll();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kesiswaan/index.php" class="hover:text-blue-600">Kesiswaan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Data Siswa</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-graduate text-white text-xl"></i>
                    </div>
                    Data Siswa
                </h2>
                <p class="text-gray-600 mt-2">Kelola data siswa dan dokumen kelengkapan</p>
            </div>
            <div class="flex gap-2">
                <a href="?action=download_template"
                    class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm"
                    title="Download Template CSV">
                    <i class="fas fa-download mr-2"></i>Template
                </a>
                <button onclick="document.getElementById('importFile').click()"
                    class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold text-sm"
                    title="Import CSV">
                    <i class="fas fa-file-import mr-2"></i>Import
                </button>
                <a href="?action=export"
                    class="px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold text-sm"
                    title="Export CSV">
                    <i class="fas fa-file-export mr-2"></i>Export
                </a>
                <button onclick="openModal('modalAdd')"
                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Tambah Siswa
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden import form -->
    <form method="POST" enctype="multipart/form-data" id="importForm" class="hidden">
        <input type="hidden" name="action" value="import">
        <input type="file" name="file_import" id="importFile" accept=".csv" onchange="this.form.submit()">
    </form>

    <?php if ($message): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Siswa</p>
                    <p class="text-3xl font-bold text-emerald-600"><?= $totalSiswa ?></p>
                </div>
                <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-emerald-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Laki-laki</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['L'] ?? 0 ?></p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-male text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Perempuan</p>
                    <p class="text-3xl font-bold text-pink-600"><?= $stats['P'] ?? 0 ?></p>
                </div>
                <div class="w-14 h-14 bg-pink-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-female text-pink-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Jumlah Kelas</p>
                    <p class="text-3xl font-bold text-amber-600"><?= count($kelasList) ?></p>
                </div>
                <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chalkboard text-amber-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Daftar Siswa</h3>
            <div class="flex gap-2">
                <select id="filterKelas" onchange="filterByKelas()"
                    class="px-4 py-2 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelasList as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filterKelas == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchInput" placeholder="Cari NISN/nama..."
                    class="px-4 py-2 border border-gray-300 rounded-lg w-64 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left w-12">No</th>
                        <th class="text-left w-16">Foto</th>
                        <th class="text-left w-32">NISN</th>
                        <th class="text-left">Nama Lengkap</th>
                        <th class="text-left w-28">Kelas</th>
                        <th class="text-left w-16">L/P</th>
                        <th class="text-center w-60">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allSiswa)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <i class="fas fa-user-graduate text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-semibold">Belum ada data siswa</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allSiswa as $i => $s): ?>
                            <?php $kelengkapan = $dokumenSiswa->getKelengkapan($s['id']); ?>
                            <tr>
                                <td class="dark:text-gray-300"><?= $i + 1 ?></td>
                                <td>
                                    <?php if ($s['foto']): ?>
                                        <img src="/e-TU/uploads/siswa/<?= $s['foto'] ?>" alt="Foto"
                                            class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div
                                            class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400 dark:text-gray-500"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span
                                        class="font-mono bg-gray-100 dark:bg-gray-700 dark:text-gray-200 px-2 py-1 rounded text-sm"><?= htmlspecialchars($s['nisn']) ?></span>
                                </td>
                                <td class="font-semibold dark:text-white"><?= htmlspecialchars($s['nama_lengkap']) ?></td>
                                <td class="dark:text-gray-300"><?= htmlspecialchars($s['nama_kelas'] ?? '-') ?></td>
                                <td>
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-bold <?= $s['jenis_kelamin'] === 'L' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300' ?>">
                                        <?= $s['jenis_kelamin'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button onclick='viewDokumen(<?= $s['id'] ?>, <?= json_encode($s['nama_lengkap']) ?>)'
                                        class="px-3 py-1 rounded-lg text-sm font-semibold
                                    <?= $kelengkapan['percentage'] >= 100 ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : ($kelengkapan['percentage'] >= 50 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300') ?>">
                                        <i class="fas fa-folder-open mr-1"></i><?= $kelengkapan['percentage'] ?>%
                                    </button>
                                    <button onclick='editSiswa(<?= json_encode($s) ?>)'
                                        class="px-3 py-1 bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick='graduateSiswa(<?= $s['id'] ?>, <?= json_encode($s['nama_lengkap']) ?>)'
                                        class="px-3 py-1 bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300 rounded-lg hover:bg-green-200 dark:hover:bg-green-900"
                                        title="Luluskan Siswa">
                                        <i class="fas fa-user-graduate"></i>
                                    </button>
                                    <button onclick='deleteSiswa(<?= $s['id'] ?>)'
                                        class="px-3 py-1 bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300 rounded-lg hover:bg-red-200 dark:hover:bg-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6 sticky top-0 bg-white pb-4 border-b">
            <h3 class="text-2xl font-bold text-gray-800">Tambah Siswa Baru</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">NISN *</label>
                    <input type="text" name="nisn" required pattern="[0-9]{10}" maxlength="10"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono" placeholder="10 digit">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas</label>
                    <select name="kelas_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Masuk *</label>
                    <input type="number" name="tahun_masuk" required value="<?= date('Y') ?>" min="2000"
                        max="<?= date('Y') + 1 ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pas Foto</label>
                    <input type="file" name="foto" accept="image/*"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                    <textarea name="alamat" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Orang Tua/Wali</label>
                    <input type="text" name="nama_ortu" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No HP Orang Tua</label>
                    <input type="tel" name="no_hp_ortu" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold">Batal</button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-content max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6 sticky top-0 bg-white pb-4 border-b">
            <h3 class="text-2xl font-bold text-gray-800">Edit Data Siswa</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="formEdit">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">NISN *</label>
                    <input type="text" name="nisn" id="edit_nisn" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" id="edit_nama_lengkap" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kelas</label>
                    <select name="kelas_id" id="edit_kelas_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Masuk *</label>
                    <input type="number" name="tahun_masuk" id="edit_tahun_masuk" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" id="edit_jenis_kelamin" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="edit_tempat_lahir"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ganti Foto</label>
                    <input type="file" name="foto" accept="image/*"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                    <textarea name="alamat" id="edit_alamat" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Orang Tua/Wali</label>
                    <input type="text" name="nama_ortu" id="edit_nama_ortu"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No HP Orang Tua</label>
                    <input type="tel" name="no_hp_ortu" id="edit_no_hp_ortu"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold">Batal</button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dokumen -->
<div id="modalDokumen" class="modal-overlay">
    <div class="modal-content max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6 sticky top-0 bg-white pb-4 border-b z-10">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Kelengkapan Dokumen</h3>
                <p class="text-gray-600" id="dokumen_siswa_nama"></p>
            </div>
            <button onclick="closeModal('modalDokumen')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <input type="hidden" id="current_siswa_id">

        <div id="dokumenList" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Dokumen items loaded via JS -->
        </div>

        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="font-bold text-blue-800 mb-3"><i class="fas fa-upload mr-2"></i>Upload Dokumen Baru</h4>
            <form id="uploadForm" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">
                <input type="hidden" name="action" value="upload_dokumen">
                <input type="hidden" name="siswa_id" id="upload_siswa_id">
                <input type="hidden" name="is_ajax" value="1">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-blue-800 mb-1">Jenis Dokumen</label>
                    <select name="jenis_dokumen" id="upload_jenis_dokumen" required onchange="toggleNamaDokumen()"
                        class="w-full px-3 py-2 border border-blue-300 rounded-lg">
                        <?php foreach (DokumenSiswa::$jenisDoc as $key => $label): ?>
                            <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="namaDokumenContainer" class="flex-1 min-w-[200px] hidden">
                    <label class="block text-sm font-semibold text-blue-800 mb-1">Nama Dokumen <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="nama_dokumen" id="upload_nama_dokumen"
                        placeholder="Contoh: Surat Pindah, Sertifikat, dll"
                        class="w-full px-3 py-2 border border-blue-300 rounded-lg">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-blue-800 mb-1">Pilih File (JPG/PNG/PDF, max
                        5MB)</label>
                    <input type="file" name="dokumen" id="upload_file" required accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full px-3 py-2 border border-blue-300 rounded-lg bg-white">
                </div>
                <button type="submit" id="uploadBtn"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-upload mr-2"></i>Upload
                </button>
            </form>
            <div id="uploadStatus" class="mt-3 text-sm hidden"></div>
        </div>
    </div>
</div>

<!-- Modal Delete Confirmation -->
<div id="modalDelete" class="modal-overlay">
    <div class="modal-content max-w-md">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus data siswa ini? Data yang terhapus tidak
                dapat dikembalikan.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeModal('modalDelete')"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                <button onclick="confirmDelete()"
                    class="px-6 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Graduate Confirmation -->
<div id="modalGraduate" class="modal-overlay">
    <div class="modal-content max-w-md">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-graduate text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Luluskan Siswa</h3>
            <p class="text-gray-600 mb-4" id="graduate_nama"></p>
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Kelulusan</label>
                <input type="number" id="graduate_tahun" value="<?= date('Y') ?>" min="2000" max="<?= date('Y') + 5 ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg text-center font-bold text-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>
            <div class="flex gap-3 justify-center">
                <button onclick="closeModal('modalGraduate')"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition">Batal</button>
                <button onclick="confirmGraduate()"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">Luluskan</button>
            </div>
        </div>
    </div>
</div>

<!-- Form Delete -->
<form method="POST" id="formDelete">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<!-- Form Graduate -->
<form method="POST" id="formGraduate">
    <input type="hidden" name="action" value="graduate">
    <input type="hidden" name="id" id="graduate_id">
    <input type="hidden" name="tahun_lulus" id="graduate_tahun_input">
</form>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    const jenisDoc = <?= json_encode(DokumenSiswa::$jenisDoc) ?>;

    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const url = new URL(window.location.href);
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        window.location.href = url.toString();
    });

    function editSiswa(s) {
        document.getElementById('edit_id').value = s.id;
        document.getElementById('edit_nisn').value = s.nisn;
        document.getElementById('edit_nama_lengkap').value = s.nama_lengkap;
        document.getElementById('edit_kelas_id').value = s.kelas_id || '';
        document.getElementById('edit_tahun_masuk').value = s.tahun_masuk;
        document.getElementById('edit_jenis_kelamin').value = s.jenis_kelamin;
        document.getElementById('edit_tempat_lahir').value = s.tempat_lahir || '';
        document.getElementById('edit_tanggal_lahir').value = s.tanggal_lahir || '';
        document.getElementById('edit_alamat').value = s.alamat || '';
        document.getElementById('edit_nama_ortu').value = s.nama_ortu || '';
        document.getElementById('edit_no_hp_ortu').value = s.no_hp_ortu || '';
        openModal('modalEdit');
    }

    function deleteSiswa(id) {
        document.getElementById('delete_id').value = id;
        openModal('modalDelete');
    }

    function graduateSiswa(id, nama) {
        document.getElementById('graduate_id').value = id;
        document.getElementById('graduate_siswa_nama').textContent = nama;
        document.getElementById('graduate_tahun').value = new Date().getFullYear();
        openModal('modalGraduate');
    }

    function viewDokumen(siswaId, nama) {
        document.getElementById('current_siswa_id').value = siswaId;
        document.getElementById('upload_siswa_id').value = siswaId;
        document.getElementById('dokumen_siswa_nama').textContent = nama;

        // Load dokumen via AJAX
        fetch(`?ajax=get_dokumen&siswa_id=${siswaId}`)
            .then(res => res.json())
            .then(data => {
                renderDokumenList(siswaId, data.dokumen || []);
                openModal('modalDokumen');
            })
            .catch(() => {
                renderDokumenList(siswaId, []);
                openModal('modalDokumen');
            });
    }

    function toggleNamaDokumen() {
        const jenisSelect = document.getElementById('upload_jenis_dokumen');
        const namaContainer = document.getElementById('namaDokumenContainer');
        const namaInput = document.getElementById('upload_nama_dokumen');

        if (jenisSelect.value === 'lainnya') {

            // Separate lainnya from other documents
            const lainnyaDocs = dokumen.filter(d => d.jenis_dokumen === 'lainnya');
            const otherDocs = {};
            dokumen.forEach(d => {
                if (d.jenis_dokumen !== 'lainnya') {
                    otherDocs[d.jenis_dokumen] = d;
                }
            });

            let html = '';

            // Render regular documents
            for (const [key, label] of Object.entries(jenisDoc)) {
                if (key === 'lainnya') continue; // Skip lainnya, will render separately

                const doc = otherDocs[key];
                const isRequired = ['kk', 'akte', 'ijazah_sd', 'ktp_ayah', 'ktp_ibu', 'foto'].includes(key);

                html += `
            <div class="p-4 rounded-lg border-2 ${doc ? 'border-green-300 bg-green-50' : (isRequired ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50')}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg ${doc ? 'bg-green-200' : 'bg-gray-200'} flex items-center justify-center">
                            <i class="fas ${doc ? 'fa-check text-green-600' : 'fa-file text-gray-400'}"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">${label}</p>
                            ${isRequired ? '<span class="text-xs text-red-600">* Wajib</span>' : ''}
                            ${doc ? `<p class="text-xs text-gray-500">${doc.nama_file}</p>` : ''}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        ${doc ? `
                            <a href="/e-TU/uploads/dokumen-siswa/${siswaId}/${doc.nama_file}" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 text-sm" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/e-TU/uploads/dokumen-siswa/${siswaId}/${doc.nama_file}" download class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 text-sm" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <button onclick="deleteDokumen(${doc.id})" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : `
                            <span class="text-sm text-gray-400">Belum upload</span>
                        `}
                    </div>
                </div>
            </div>
        `;
            }

            // Render lainnya documents separately
            if (lainnyaDocs.length > 0) {
                lainnyaDocs.forEach(doc => {
                    html += `
                <div class="p-4 rounded-lg border-2 border-purple-300 bg-purple-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-200 flex items-center justify-center">
                                <i class="fas fa-file-alt text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">${doc.nama_dokumen || 'Dokumen Lainnya'}</p>
                                <p class="text-xs text-gray-500">${doc.nama_file}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="/e-TU/uploads/dokumen-siswa/${siswaId}/${doc.nama_file}" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 text-sm" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/e-TU/uploads/dokumen-siswa/${siswaId}/${doc.nama_file}" download class="px-3 py-1 bg-green-100 text-green-600 rounded hover:bg-green-200 text-sm" title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <button onclick="deleteDokumen(${doc.id})" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
                });
            }

            container.innerHTML = html;
        }

        function deleteDokumen(id) {
            if (confirm('Hapus dokumen ini?')) {
                // AJAX delete
                const formData = new FormData();
                formData.append('action', 'delete_dokumen');
                formData.append('dokumen_id', id);
                formData.append('is_ajax', '1');

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Refresh document list
                            const currentSiswaId = document.getElementById('current_siswa_id').value;
                            fetch(`?ajax=get_dokumen&siswa_id=${currentSiswaId}`)
                                .then(res => res.json())
                                .then(data => {
                                    renderDokumenList(currentSiswaId, data.dokumen || []);
                                });
                        } else {
                            alert(data.message || 'Gagal menghapus dokumen');
                        }
                    })
                    .catch(error => {
                        console.error('Delete error:', error);
                        alert('Terjadi kesalahan saat menghapus dokumen');
                    });
            }
        }

        // AJAX Upload Handler
        document.getElementById('uploadForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const siswaId = document.getElementById('upload_siswa_id').value;
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadStatus = document.getElementById('uploadStatus');

            // Disable button and show loading
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
            uploadStatus.className = 'mt-3 text-sm text-blue-600';
            uploadStatus.textContent = 'Mengupload dokumen...';
            uploadStatus.classList.remove('hidden');

            fetch('', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        uploadStatus.className = 'mt-3 text-sm text-green-600';
                        uploadStatus.textContent = data.message || 'Dokumen berhasil diupload!';

                        // Refresh document list
                        const currentSiswaId = document.getElementById('current_siswa_id').value;
                        fetch(`?ajax=get_dokumen&siswa_id=${currentSiswaId}`)
                            .then(res => res.json())
                            .then(data => {
                                renderDokumenList(currentSiswaId, data.dokumen || []);
                                // Reset form
                                document.getElementById('uploadForm').reset();
                            })
                            .catch(() => {
                                // Fallback: reload the modal
                                viewDokumen(currentSiswaId, document.getElementById('dokumen_siswa_nama').textContent);
                            });

                        // Hide status after 3 seconds
                        setTimeout(() => {
                            uploadStatus.classList.add('hidden');
                        }, 3000);
                    } else {
                        uploadStatus.className = 'mt-3 text-sm text-red-600';
                        uploadStatus.textContent = data.message || 'Gagal upload dokumen. Silakan coba lagi.';
                    }
                })
                .catch(error => {
                    uploadStatus.className = 'mt-3 text-sm text-red-600';
                    uploadStatus.textContent = 'Terjadi kesalahan. Silakan coba lagi.';
                    console.error('Upload error:', error);
                })
                .finally(() => {
                    // Re-enable button
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload';
                });
        });

        function graduateSiswa(id, nama) {
            document.getElementById('graduate_id').value = id;
            document.getElementById('graduate_nama').textContent = `Luluskan siswa "${nama}"?`;
            openModal('modalGraduate');
        }

        function confirmDelete() {
            document.getElementById('formDelete').submit();
        }

        function confirmGraduate() {
            const tahun = document.getElementById('graduate_tahun').value;
            document.getElementById('graduate_tahun_input').value = tahun;
            document.getElementById('formGraduate').submit();
        }

        function deleteDokumen(id) {
            if (!confirm('Yakin ingin menghapus dokumen ini?')) return;

            const siswaId = document.getElementById('current_siswa_id').value;

            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_dokumen&dokumen_id=${id}&is_ajax=1`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Reload dokumen list
                        fetch(`?ajax=get_dokumen&siswa_id=${siswaId}`)
                            .then(res => res.json())
                            .then(data => {
                                renderDokumenList(siswaId, data.dokumen || []);
                            });
                    } else {
                        alert(data.message || 'Gagal menghapus dokumen');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('Terjadi kesalahan saat menghapus dokumen');
                });
        }
</script>


<script src="/e-TU/assets/js/app.js"></script>
<script>
        // Functions for siswa CRUD operations
        function editSiswa(s) {
            document.getElementById('edit_id').value = s.id;
            document.getElementById('edit_nisn').value = s.nisn;
            document.getElementById('edit_nama_lengkap').value = s.nama_lengkap;
            document.getElementById('edit_kelas_id').value = s.kelas_id || '';
            document.getElementById('edit_tahun_masuk').value = s.tahun_masuk;
            document.getElementById('edit_jenis_kelamin').value = s.jenis_kelamin;
            document.getElementById('edit_tempat_lahir').value = s.tempat_lahir || '';
            document.getElementById('edit_tanggal_lahir').value = s.tanggal_lahir || '';
            document.getElementById('edit_alamat').value = s.alamat || '';
            document.getElementById('edit_nama_ortu').value = s.nama_ortu || '';
            document.getElementById('edit_no_hp_ortu').value = s.no_hp_ortu || '';
            openModal('modalEdit');
        }

        async function deleteSiswa(id, nama) {
            const confirmed = await confirmDelete(`Yakin ingin menghapus data siswa "${nama}"?`);
            if (confirmed) {
                document.getElementById('delete_id').value = id;
                document.getElementById('formDelete').submit();
            }
        }

        async function graduateSiswa(id, nama) {
            const tahun = prompt('Tahun Kelulusan:', new Date().getFullYear());
            if (tahun) {
                document.getElementById('graduate_id').value = id;
                document.getElementById('graduate_tahun_input').value = tahun;
                document.getElementById('formGraduate').submit();
            }
        }

        function viewDokumen(siswaId, nama) {
            document.getElementById('current_siswa_id').value = siswaId;
            document.getElementById('upload_siswa_id').value = siswaId;
            document.getElementById('dokumen_siswa_nama').textContent = nama;

            // Load dokumen via AJAX
            fetch(`?ajax=get_dokumen&siswa_id=${siswaId}`)
                .then(res => res.json())
                .then(data => {
                    renderDokumenList(siswaId, data.dokumen || []);
                    openModal('modalDokumen');
                })
                .catch(() => {
                    renderDokumenList(siswaId, []);
                    openModal('modalDokumen');
                });
        }

        function toggleNamaDokumen() {
            const jenisSelect = document.getElementById('upload_jenis_dokumen');
            const namaContainer = document.getElementById('namaDokumenContainer');
            const namaInput = document.getElementById('upload_nama_dokumen');

            if (jenisSelect.value === 'lainnya') {
                namaContainer.classList.remove('hidden');
                namaInput.setAttribute('required', 'required');
            } else {
                namaContainer.classList.add('hidden');
                namaInput.removeAttribute('required');
                namaInput.value = '';
            }
        }

        const jenisDoc = <?= json_encode(DokumenSiswa::$jenisDoc) ?>;

        function renderDokumenList(siswaId, dokumen) {
            const container = document.getElementById('dokumenList');
            const lainnyaDocs = dokumen.filter(d => d.jenis_dokumen === 'lainnya');
            const otherDocs = {};
            dokumen.forEach(d => {
                if (d.jenis_dokumen !== 'lainnya') {
                    otherDocs[d.jenis_dokumen] = d;
                }
            });

            let html = '';

            for (const [key, label] of Object.entries(jenisDoc)) {
                if (key === 'lainnya') continue;

                const doc = otherDocs[key];
                const isRequired = ['kk', 'akte', 'ijazah_sd', 'ktp_ayah', 'ktp_ibu', 'foto'].includes(key);

                html += `
        <div class="p-4 rounded-lg border-2 ${doc ? 'border-green-300 bg-green-50' : (isRequired ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-gray-50')}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg ${doc ? 'bg-green-200' : 'bg-gray-200'} flex items-center justify-center">
                        <i class="fas ${doc ? 'fa-check text-green-600' : 'fa-file text-gray-400'}"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">${label}</p>
                        ${isRequired ? '<span class="text-xs text-red-600">* Wajib</span>' : ''}
                        ${doc ? `<p class="text-xs text-gray-500">${doc.nama_file}</p>` : ''}
                    </div>
                </div>
                <div class="flex gap-2">
                    ${doc ? `
                        <a href="/e-TU/uploads/dokumen-siswa/${siswaId}/${doc.nama_file}" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 text-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="deleteDokumen(${doc.id})" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : '<span class="text-sm text-gray-400">Belum upload</span>'}
                </div>
            </div>
        </div>`;
            }

            if (lainnyaDocs.length > 0) {
                lainnyaDocs.forEach(doc => {
                    html += `
            <div class="p-4 rounded-lg border-2 border-purple-300 bg-purple-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-200 flex items-center justify-center">
                            <i class="fas fa-file-alt text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">${doc.nama_dokumen || 'Dokumen Lainnya'}</p>
                            <p class="text-xs text-gray-500">${doc.nama_file}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="/e-TU/uploads/dokumen-siswa/${siswaId}/${doc.nama_file}" target="_blank" class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 text-sm">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="deleteDokumen(${doc.id})" class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 text-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
                });
            }

            container.innerHTML = html;
        }

        async function deleteDokumen(id) {
            const confirmed = await confirmDelete('Yakin ingin menghapus dokumen ini?');
            if (!confirmed) return;

            const formData = new FormData();
            formData.append('action', 'delete_dokumen');
            formData.append('dokumen_id', id);

            try {
                const response = await fetch('upload_dokumen_ajax.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    const currentSiswaId = document.getElementById('current_siswa_id').value;
                    const res = await fetch(`?ajax=get_dokumen&siswa_id=${currentSiswaId}`);
                    const dokData = await res.json();
                    renderDokumenList(currentSiswaId, dokData.dokumen || []);
                    showSuccess('Dokumen berhasil dihapus!');
                } else {
                    showError(data.message || 'Gagal menghapus dokumen');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showError('Terjadi kesalahan saat menghapus dokumen');
            }
        }

        // Upload form handler
        document.getElementById('uploadForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(this);
            const uploadBtn = document.getElementById('uploadBtn');
            const uploadStatus = document.getElementById('uploadStatus');

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';
            uploadStatus.className = 'mt-3 text-sm text-blue-600';
            uploadStatus.textContent = 'Mengupload dokumen...';
            uploadStatus.classList.remove('hidden');

            try {
                const response = await fetch('upload_dokumen_ajax.php', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    showSuccess(data.message || 'Dokumen berhasil diupload!');

                    const currentSiswaId = document.getElementById('current_siswa_id').value;
                    const res = await fetch(`?ajax=get_dokumen&siswa_id=${currentSiswaId}`);
                    const dokData = await res.json();
                    renderDokumenList(currentSiswaId, dokData.dokumen || []);

                    this.reset();
                    uploadStatus.classList.add('hidden');
                } else {
                    showError(data.message || 'Gagal upload dokumen');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showError('Terjadi kesalahan saat upload');
            } finally {
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload';
            }
        });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
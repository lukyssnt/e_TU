<?php
ob_start();

// Handle template download
if (isset($_GET['action']) && $_GET['action'] === 'download_template') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="template_pegawai.csv"');

    $output = fopen('php://output', 'w');

    // Clean buffer
    if (ob_get_length())
        ob_end_clean();

    // Add separator hint for Excel
    fwrite($output, "sep=;\n");

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Header row - Use Semicolon for Excel compatibility in ID region
    fputcsv($output, ['NIP', 'Nama Lengkap', 'Jabatan', 'Status Kepegawaian (PNS/PPPK/Honorer/GTT/PTT)', 'Golongan', 'Email', 'No HP', 'Alamat'], ';', '"', '\\');

    // Example row
    fputcsv($output, ['198001012000011001', 'Budi Santoso', 'Guru Matematika', 'PNS', 'III/a', 'budi@example.com', '08123456789', 'Jl. Merdeka No. 1'], ';', '"', '\\');

    fclose($output);
    exit;
}

// Handle data export
if (isset($_GET['action']) && $_GET['action'] === 'export_data') {
    // Ensure session is started for auth check (since this runs before header.php)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: /e-TU/login.php');
        exit;
    }

    require_once __DIR__ . '/../../classes/Pegawai.php';
    $pegawai = new Pegawai();
    $data = $pegawai->getAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="data_pegawai_' . date('Y-m-d') . '.csv"');

    // Clean output buffer to remove any whitespace/HTML injection
    if (ob_get_level())
        ob_end_clean();

    $output = fopen('php://output', 'w');

    // Add separator hint for Excel
    fwrite($output, "sep=;\n");

    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

    // Header
    fputcsv($output, ['No', 'NIP', 'Nama Lengkap', 'Jabatan', 'Status', 'Golongan', 'Email', 'No HP', 'Alamat'], ';', '"', '\\');

    $no = 1;
    foreach ($data as $row) {
        fputcsv($output, [
            $no++,
            "'" . $row['nip'], // Prevent excel auto-format
            $row['nama_lengkap'],
            $row['nama_jabatan'] ?? '-',
            $row['status_kepegawaian'],
            $row['golongan'],
            $row['email'],
            $row['no_hp'],
            $row['alamat']
        ], ';', '"', '\\');
    }

    fclose($output);
    exit;
}

$pageTitle = 'Data Pegawai - Kepegawaian';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Pegawai.php';

checkPermission('kepegawaian');

$pegawai = new Pegawai();
$listJabatan = $pegawai->getAllJabatan();



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'import') {
            if (isset($_FILES['file_import']) && $_FILES['file_import']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file_import']['tmp_name'];
                $handle = fopen($file, "r");

                // Skip header (read with semicolon)
                fgetcsv($handle, 0, ';');

                $successCount = 0;
                $errorCount = 0;

                while (($row = fgetcsv($handle, 0, ';')) !== false) {
                    // Map CSV columns to database fields
                    $data = [
                        'nip' => clean($row[0] ?? ''),
                        'nama_lengkap' => clean($row[1] ?? ''),
                        'status_kepegawaian' => clean($row[2] ?? 'Honorer'),
                        'golongan' => clean($row[3] ?? ''),
                        'email' => clean($row[4] ?? ''),
                        'no_hp' => clean($row[5] ?? ''),
                        'alamat' => clean($row[6] ?? ''),
                        'jabatan_id' => null,
                        'foto' => null
                    ];

                    // Basic validation
                    if (empty($data['nip']) || empty($data['nama_lengkap'])) {
                        $errorCount++;
                        continue;
                    }

                    if ($pegawai->create($data)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }
                }

                fclose($handle);

                $msg = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
                $type = $errorCount > 0 ? 'warning' : 'success';
                redirect($_SERVER['PHP_SELF'], $msg, $type);
            } else {
                throw new Exception("Gagal mengupload file.");
            }
        } elseif ($_POST['action'] === 'create') {
            $fotoUpload = null;
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['foto'], 'uploads/pegawai/', ['jpg', 'jpeg', 'png']);
                if ($upload['success']) {
                    $fotoUpload = $upload['path'];
                }
            }

            $data = [
                'nip' => clean($_POST['nip']),
                'nama_lengkap' => clean($_POST['nama_lengkap']),
                'jabatan_id' => $_POST['jabatan_id'] ?? null,
                'status_kepegawaian' => $_POST['status_kepegawaian'],
                'golongan' => clean($_POST['golongan']) ?? null,
                'email' => clean($_POST['email']) ?? null,
                'no_hp' => clean($_POST['no_hp']) ?? null,
                'alamat' => clean($_POST['alamat']) ?? null,
                'foto' => $fotoUpload
            ];

            if ($pegawai->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Data pegawai berhasil ditambahkan!', 'success');
            } else {
                redirect($_SERVER['PHP_SELF'], 'Gagal menambahkan data pegawai!', 'error');
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $existing = $pegawai->getById($id);

            $fotoUpload = $existing['foto'];
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['foto'], 'uploads/pegawai/', ['jpg', 'jpeg', 'png']);
                if ($upload['success']) {
                    // Delete old photo if exists
                    if ($existing['foto'] && file_exists(__DIR__ . '/../../' . $existing['foto'])) {
                        unlink(__DIR__ . '/../../' . $existing['foto']);
                    }
                    $fotoUpload = $upload['path'];
                }
            }

            $data = [
                'nip' => clean($_POST['nip']),
                'nama_lengkap' => clean($_POST['nama_lengkap']),
                'jabatan_id' => $_POST['jabatan_id'] ?? null,
                'status_kepegawaian' => $_POST['status_kepegawaian'],
                'golongan' => clean($_POST['golongan']) ?? null,
                'email' => clean($_POST['email']) ?? null,
                'no_hp' => clean($_POST['no_hp']) ?? null,
                'alamat' => clean($_POST['alamat']) ?? null,
                'foto' => $fotoUpload
            ]

            ;

            if ($pegawai->update($id, $data)) {
                redirect($_SERVER['PHP_SELF'], 'Data pegawai berhasil diperbarui!', 'success');
            } else {
                redirect($_SERVER['PHP_SELF'], 'Gagal memperbarui data pegawai!', 'error');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($pegawai->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Data pegawai berhasil dihapus!', 'success');
            } else {
                redirect($_SERVER['PHP_SELF'], 'Gagal menghapus data pegawai!', 'error');
            }
        }
    } catch (Exception $e) {
        redirect($_SERVER['PHP_SELF'], 'Error: ' . $e->getMessage(), 'error');
    }
}

try {
    $allPegawai = $pegawai->getAll();
    // Debug: uncomment to see data
    // echo '<pre>'; var_dump($allPegawai); echo '</pre>'; exit;
} catch (Exception $e) {
    $allPegawai = [];
    // Show error in console
    echo '<script>console.error("Error loading pegawai:", ' . json_encode($e->getMessage()) . ');</script>';
}
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kepegawaian/index.php" class="hover:text-blue-600">Kepegawaian</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Data Pegawai</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-tie text-white text-xl"></i>
                    </div>
                    Data Pegawai
                </h2>
                <p class="text-gray-600 mt-2">Kelola data pegawai dan guru</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modalImport')"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-file-excel mr-2"></i>Import Excel
                </button>
                <a href="?action=export_data" target="_blank"
                    class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-file-export mr-2"></i>Export Data
                </a>
                <button onclick="openModalAdd()"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Tambah Pegawai
                </button>
            </div>
        </div>
    </div>

    <!-- Usage Tips -->
    <div class="mb-6 bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-r-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0 mt-0.5">
                <i class="fas fa-info-circle text-indigo-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-indigo-800">Tips Manajemen Data Pegawai:</h3>
                <ul class="mt-2 text-sm text-indigo-700 list-disc pl-5 space-y-1">
                    <li>Gunakan <strong>Import Excel</strong> untuk memasukkan banyak data sekaligus (download template
                        terlebih dahulu).</li>
                    <li>Pastikan kolom <strong>NIP</strong> diawali tanda kutip satu (') di Excel agar tidak berubah
                        menjadi format ilmiah (misal: 1.98E+17).</li>
                    <li>Fitur <strong>Export Data</strong> akan menghasilkan file CSV yang bisa dibuka di Excel.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Pegawai</p>
                    <p class="text-2xl font-bold text-blue-600"><?= count($allPegawai) ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">PNS</p>
                    <p class="text-2xl font-bold text-green-600">
                        <?= count(array_filter($allPegawai, fn($p) => $p['status_kepegawaian'] === 'PNS')) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Honorer</p>
                    <p class="text-2xl font-bold text-amber-600">
                        <?= count(array_filter($allPegawai, fn($p) => $p['status_kepegawaian'] === 'Honorer')) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">GTT/PTT</p>
                    <p class="text-2xl font-bold text-purple-600">
                        <?= count(array_filter($allPegawai, fn($p) => $p['status_kepegawaian'] === 'GTT/PTT')) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-graduate text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pegawai</h3>
            <input type="text" id="searchInput" placeholder="Cari pegawai..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Foto</th>
                        <th class="text-left">NIP</th>
                        <th class="text-left">Nama Lengkap</th>
                        <th class="text-left">Jabatan</th>
                        <th class="text-left">Status</th>
                        <th class="text-left">Golongan</th>
                        <th class="text-left">No. HP</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allPegawai) > 0): ?>
                        <?php foreach ($allPegawai as $index => $p): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <?php if ($p['foto']): ?>
                                        <img src="/e-TU/<?= $p['foto'] ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="font-semibold"><?= htmlspecialchars($p['nip']) ?></td>
                                <td><?= htmlspecialchars($p['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($p['nama_jabatan'] ?? '-') ?></td>
                                <td>
                                    <span
                                        class="badge badge-<?= $p['status_kepegawaian'] === 'PNS' ? 'success' : ($p['status_kepegawaian'] === 'PPPK' ? 'primary' : 'warning') ?>">
                                        <?= $p['status_kepegawaian'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($p['golongan'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($p['no_hp'] ?? '-') ?></td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="/e-TU/modules/kepegawaian/riwayat.php?id=<?= $p['id'] ?>"
                                            class="px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white rounded text-sm"
                                            title="Riwayat Kepegawaian">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <a href="/e-TU/modules/kepegawaian/dokumen.php?id=<?= $p['id'] ?>"
                                            class="px-3 py-1.5 bg-indigo-500 hover:bg-indigo-600 text-white rounded text-sm"
                                            title="Kelola Dokumen">
                                            <i class="fas fa-folder-open"></i>
                                        </a>
                                        <button onclick='viewPegawai(<?= json_encode($p) ?>)'
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick='editPegawai(<?= json_encode($p) ?>)'
                                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deletePegawai(<?= $p['id'] ?>)"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada data pegawai</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add/Edit -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Tambah Data Pegawai</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="pegawaiId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Induk (NIP) *</label>
                    <input type="text" name="nip" id="nip" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jabatan</label>
                    <select name="jabatan_id" id="jabatan_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Jabatan --</option>
                        <?php foreach ($listJabatan as $j): ?>
                            <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['nama_jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status Kepegawaian *</label>
                    <select name="status_kepegawaian" id="status_kepegawaian" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih...</option>
                        <option value="PNS">PNS</option>
                        <option value="PPPK">PPPK</option>
                        <option value="Honorer">Honorer</option>
                        <option value="GTT/PTT">GTT/PTT</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Golongan</label>
                    <input type="text" name="golongan" id="golongan" placeholder="Contoh: III/a"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP</label>
                    <input type="text" name="no_hp" id="no_hp"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                    <textarea name="alamat" id="alamat" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Pegawai (JPG/PNG)</label>
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Maksimal 5MB. Biarkan kosong jika tidak ingin mengubah foto.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View -->
<div id="modalView" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Detail Pegawai</h3>
            <button onclick="closeModal('modalView')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="viewContent" class="space-y-4">
            <!-- Content populated by JS -->
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeModal('modalView')"
                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>
</div>

<!-- Modal Import -->
<div id="modalImport" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Import Data Pegawai</h3>
            <button onclick="closeModal('modalImport')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-800 mb-2 font-semibold">Langkah-langkah Import:</p>
            <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                <li>Download template Excel (CSV) terlebih dahulu.</li>
                <li>Isi data pegawai sesuai format template.</li>
                <li>Simpan file sebagai CSV.</li>
                <li>Upload file CSV yang sudah diisi.</li>
            </ol>
            <a href="?action=download_template" target="_blank"
                class="inline-block mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-semibold transition-colors">
                <i class="fas fa-download mr-2"></i>Download Template
            </a>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import">

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File CSV</label>
                <input type="file" name="file_import" accept=".csv" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalImport')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-upload mr-2"></i>Import Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function openModalAdd() {
        document.getElementById('modalTitle').textContent = 'Tambah Data Pegawai';
        document.getElementById('formAction').value = 'create';
        document.getElementById('pegawaiId').value = '';
        document.getElementById('formPegawai').reset();
        openModal('modalAdd');
    }

    function editPegawai(data) {
        document.getElementById('modalTitle').textContent = 'Edit Data Pegawai';
        document.getElementById('formAction').value = 'update';
        document.getElementById('pegawaiId').value = data.id;

        document.getElementById('nip').value = data.nip;
        document.getElementById('nama_lengkap').value = data.nama_lengkap;
        document.getElementById('jabatan_id').value = data.jabatan_id || '';
        document.getElementById('status_kepegawaian').value = data.status_kepegawaian;
        document.getElementById('golongan').value = data.golongan || '';
        document.getElementById('email').value = data.email || '';
        document.getElementById('no_hp').value = data.no_hp || '';
        document.getElementById('alamat').value = data.alamat || '';

        openModal('modalAdd');
    }

    function viewPegawai(data) {
        const content = `
            <div class="flex items-center gap-4 mb-6">
                <div class="w-20 h-20 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                    ${data.foto ? `<img src="/e-TU/${data.foto}" class="w-full h-full object-cover">` : `<div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-user text-3xl"></i></div>`}
                </div>
                <div>
                    <h4 class="text-xl font-bold text-gray-800">${data.nama_lengkap}</h4>
                    <p class="text-blue-600 font-medium">${data.nip}</p>
                    <span class="inline-block mt-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                        ${data.status_kepegawaian}
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Jabatan</p>
                    <p class="font-semibold text-gray-800">${data.nama_jabatan || '-'}</p>
                </div>
                <div>
                    <p class="text-gray-500">Golongan</p>
                    <p class="font-semibold text-gray-800">${data.golongan || '-'}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="font-semibold text-gray-800">${data.email || '-'}</p>
                </div>
                <div>
                    <p class="text-gray-500">No. HP</p>
                    <p class="font-semibold text-gray-800">${data.no_hp || '-'}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500">Alamat</p>
                    <p class="font-semibold text-gray-800">${data.alamat || '-'}</p>
                </div>
            </div>
        `;
        document.getElementById('viewContent').innerHTML = content;
        openModal('modalView');
    }

    function deletePegawai(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data pegawai ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
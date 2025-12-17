<?php
$pageTitle = 'Data Alumni - Kesiswaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Siswa.php';

checkPermission('kesiswaan');

$siswa = new Siswa();

// Get filter
$filterTahun = $_GET['tahun'] ?? null;

// Get data
$alumni = $siswa->getAllAlumni($filterTahun);
$stats = $siswa->getAlumniStats();
$message = '';
$messageType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            if ($_POST['action'] === 'create') {
                $data = [
                    'nisn' => $_POST['nisn'],
                    'nama_lengkap' => $_POST['nama_lengkap'],
                    'tahun_masuk' => $_POST['tahun_masuk'],
                    'tahun_lulus' => $_POST['tahun_lulus'],
                    'jenis_kelamin' => $_POST['jenis_kelamin'],
                    'tempat_lahir' => $_POST['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $_POST['tanggal_lahir'] ?? null,
                    'alamat' => $_POST['alamat'] ?? null,
                    'nama_ortu' => $_POST['nama_ortu'] ?? null,
                    'no_hp_ortu' => $_POST['no_hp_ortu'] ?? null
                ];
                if ($siswa->createAlumni($data)) {
                    $message = 'Data alumni berhasil ditambahkan!';
                    $messageType = 'success';
                    // Refresh data
                    $alumni = $siswa->getAllAlumni($filterTahun);
                    $stats = $siswa->getAlumniStats();
                } else {
                    throw new Exception('Gagal menambahkan data alumni.');
                }
            } elseif ($_POST['action'] === 'import') {
                if (isset($_FILES['file_import']) && $_FILES['file_import']['error'] === 0) {
                    $file = $_FILES['file_import']['tmp_name'];
                    $handle = fopen($file, "r");
                    if ($handle !== FALSE) {
                        $row = 0;
                        $successCount = 0;
                        $errorCount = 0;
                        // Use semicolon separator
                        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                            $row++;
                            if ($row === 1)
                                continue; // Skip header

                            // Validate required fields (NISN, Nama)
                            if (empty($data[0]) || empty($data[1]))
                                continue;

                            $alumniData = [
                                'nisn' => trim($data[0]),
                                'nama_lengkap' => trim($data[1]),
                                'jenis_kelamin' => strtoupper(trim($data[2])) === 'P' ? 'P' : 'L',
                                'tahun_masuk' => trim($data[3]) ?: (trim($data[4]) - 3),
                                'tahun_lulus' => trim($data[4]) ?: date('Y'),
                                'tempat_lahir' => trim($data[5]),
                                'tanggal_lahir' => trim($data[6]) ?: null,
                                'alamat' => trim($data[7]),
                                'nama_ortu' => trim($data[8]),
                                'no_hp_ortu' => trim($data[9])
                            ];

                            try {
                                if ($siswa->createAlumni($alumniData)) {
                                    $successCount++;
                                } else {
                                    $errorCount++;
                                }
                            } catch (Exception $e) {
                                $errorCount++;
                            }
                        }
                        fclose($handle);
                        $message = "Import selesai. Berhasil: $successCount, Gagal: $errorCount";
                        $messageType = 'info';
                        // Refresh data
                        $alumni = $siswa->getAllAlumni($filterTahun);
                        $stats = $siswa->getAlumniStats();
                    }
                }
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<main class="lg:ml-72 min-h-screen p-6">
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Data Alumni</span>
        </nav>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-graduate text-white text-xl"></i>
                    </div>
                    Data Alumni
                </h2>
                <p class="text-gray-600 mt-2">Daftar siswa yang telah lulus</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modalImport')"
                    class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold shadow-sm">
                    <i class="fas fa-file-import mr-2 text-green-600"></i>Import
                </button>
                <button onclick="openModal('modalAdd')"
                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold shadow-lg hover:opacity-90">
                    <i class="fas fa-plus mr-2"></i>Tambah Manual
                </button>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : ($messageType === 'info' ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-red-100 text-red-800 border border-red-300') ?>">
            <i
                class="fas <?= $messageType === 'success' ? 'fa-check-circle' : ($messageType === 'info' ? 'fa-info-circle' : 'fa-exclamation-circle') ?> mr-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm mb-1">Total Alumni</p>
                    <p class="text-3xl font-bold"><?= number_format($stats['total']) ?></p>
                </div>
                <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-2xl"></i>
                </div>
            </div>
        </div>

        <?php
        $recentYears = array_slice($stats['by_year'], 0, 3);
        foreach ($recentYears as $yearData):
            ?>
            <div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Tahun <?= $yearData['tahun_lulus'] ?></p>
                        <p class="text-2xl font-bold text-gray-800"><?= number_format($yearData['jumlah']) ?></p>
                        <p class="text-xs text-gray-400 mt-1">Alumni</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-indigo-600"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Alumni</h3>
            <div class="flex gap-3">
                <select id="filterTahun" onchange="filterByTahun()" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($stats['by_year'] as $y): ?>
                        <option value="<?= $y['tahun_lulus'] ?>" <?= $filterTahun == $y['tahun_lulus'] ? 'selected' : '' ?>>
                            <?= $y['tahun_lulus'] ?> (<?= $y['jumlah'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchInput" placeholder="Cari NISN/nama..."
                    class="px-4 py-2 border border-gray-300 rounded-lg w-64">
                <a href="export_alumni.php<?= $filterTahun ? '?tahun=' . $filterTahun : '' ?>" target="_blank"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full text-sm" id="dataTable">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">No</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">NISN</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Nama Lengkap</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">L/P</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Thn Masuk</th>
                        <th class="text-center py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Thn Lulus</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">TTL</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Alamat</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700 whitespace-nowrap">Ortu & HP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($alumni)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-12 text-gray-500">
                                <i class="fas fa-user-graduate text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-semibold">Belum ada data alumni</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($alumni as $i => $a): ?>
                            <tr class="hover:bg-gray-50 border-b border-gray-100 last:border-0">
                                <td class="py-3 px-4"><?= $i + 1 ?></td>
                                <td class="py-3 px-4"><span
                                        class="font-mono bg-gray-100 px-2 py-1 rounded text-xs"><?= htmlspecialchars($a['nisn']) ?></span>
                                </td>
                                <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-bold <?= $a['jenis_kelamin'] === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' ?>">
                                        <?= $a['jenis_kelamin'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center"><?= htmlspecialchars($a['tahun_masuk'] ?? '-') ?></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-bold text-xs">
                                        <?= $a['tahun_lulus'] ?? '-' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <?= htmlspecialchars($a['tempat_lahir'] ?? '-') ?>,<br>
                                    <?= $a['tanggal_lahir'] ? date('d/m/Y', strtotime($a['tanggal_lahir'])) : '-' ?>
                                </td>
                                <td class="py-3 px-4 text-xs max-w-[200px] truncate" title="<?= htmlspecialchars($a['alamat'] ?? '') ?>">
                                    <?= htmlspecialchars($a['alamat'] ?? '-') ?>
                                </td>
                                <td class="py-3 px-4 text-xs whitespace-nowrap">
                                    <div class="font-medium"><?= htmlspecialchars($a['nama_ortu'] ?? '-') ?></div>
                                    <div class="text-gray-500"><?= htmlspecialchars($a['no_hp_ortu'] ?? '-') ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add Manual -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Tambah Data Alumni</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">NISN *</label>
                    <input type="text" name="nisn" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Masuk (Perkiraan) *</label>
                    <input type="number" name="tahun_masuk" value="<?= date('Y') - 3 ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Lulus *</label>
                    <input type="number" name="tahun_lulus" value="<?= date('Y') ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                    <textarea name="alamat" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Orang Tua</label>
                    <input type="text" name="nama_ortu" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No HP Ortu</label>
                    <input type="text" name="no_hp_ortu" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                <button type="submit"
                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import -->
<div id="modalImport" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Import Data Alumni</h3>
            <button onclick="closeModal('modalImport')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="import">
            <div class="mb-4">
                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg mb-4 text-sm">
                    <p class="font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Petunjuk Import</p>
                    <ul class="list-disc list-inside ml-2">
                        <li>Gunakan template yang telah disediakan.</li>
                        <li>Format file harus <strong>.csv</strong>.</li>
                        <li>Pastikan kolom NISN unik (belum terdaftar).</li>
                    </ul>
                    <a href="download_template_alumni.php"
                        class="inline-block mt-3 text-blue-600 hover:underline font-semibold">
                        <i class="fas fa-download mr-1"></i> Download Template CSV
                    </a>
                </div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih File CSV</label>
                <input type="file" name="file_import" accept=".csv" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalImport')"
                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg">Batal</button>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Import</button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Filter by year
    function filterByTahun() {
        const tahun = document.getElementById('filterTahun').value;
        const searchInput = document.getElementById('searchInput').value;
        const url = new URL(window.location.href);

        if (tahun) url.searchParams.set('tahun', tahun);
        else url.searchParams.delete('tahun');

        window.location.href = url.toString();
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
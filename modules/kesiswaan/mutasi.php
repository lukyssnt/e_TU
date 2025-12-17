<?php
$pageTitle = 'Mutasi Siswa - Kesiswaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Mutasi.php';
require_once __DIR__ . '/../../classes/Siswa.php';

checkPermission('kesiswaan');

$mutasi = new Mutasi();
$siswa = new Siswa();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $mutasi->create([
                    'siswa_id' => $_POST['siswa_id'],
                    'jenis_mutasi' => $_POST['jenis_mutasi'],
                    'tanggal_mutasi' => $_POST['tanggal_mutasi'],
                    'alasan' => $_POST['alasan'],
                    'sekolah_asal' => $_POST['sekolah_asal'] ?: null,
                    'sekolah_tujuan' => $_POST['sekolah_tujuan'] ?: null,
                    'keterangan' => $_POST['keterangan'] ?: null
                ]);

                if ($result) {
                    $message = 'Data mutasi berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan data mutasi!';
                    $messageType = 'error';
                }
                break;

            case 'delete':
                if ($mutasi->delete($_POST['id'])) {
                    $message = 'Data mutasi berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus data mutasi!';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get data
$filterJenis = $_GET['jenis'] ?? null;
$mutasiList = $mutasi->getAll($filterJenis);
$stats = $mutasi->getStats();
$totalMutasi = $mutasi->getTotalCount();
$siswaList = $siswa->getAll(); // Untuk dropdown
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kesiswaan/index.php" class="hover:text-blue-600">Kesiswaan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Mutasi Siswa</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-exchange-alt text-white text-xl"></i>
                    </div>
                    Mutasi Siswa
                </h2>
                <p class="text-gray-600 mt-2">Kelola data perpindahan siswa (Masuk/Keluar)</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Mutasi
            </button>
        </div>
    </div>

    <?php if ($message): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Mutasi</p>
                    <p class="text-3xl font-bold text-amber-600"><?= $totalMutasi ?></p>
                </div>
                <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-amber-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <a href="?jenis=Masuk"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= $filterJenis === 'Masuk' ? 'ring-2 ring-green-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Mutasi Masuk</p>
                    <p class="text-3xl font-bold text-green-600"><?= $stats['Masuk'] ?></p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-green-600 text-2xl"></i>
                </div>
            </div>
        </a>
        <a href="?jenis=Keluar"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= $filterJenis === 'Keluar' ? 'ring-2 ring-red-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Mutasi Keluar</p>
                    <p class="text-3xl font-bold text-red-600"><?= $stats['Keluar'] ?></p>
                </div>
                <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
                </div>
            </div>
        </a>
    </div>

    <?php if ($filterJenis): ?>
        <div class="mb-4">
            <a href="mutasi.php" class="text-amber-600 hover:underline">
                <i class="fas fa-times mr-1"></i>Hapus filter (<?= $filterJenis ?>)
            </a>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Mutasi</h3>
            <input type="text" id="searchInput" placeholder="Cari siswa/sekolah..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 w-64">
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">No</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Siswa</th>
                        <th class="text-left">Jenis</th>
                        <th class="text-left">Sekolah Asal/Tujuan</th>
                        <th class="text-left">Alasan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($mutasiList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <i class="fas fa-exchange-alt text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-semibold">Belum ada data mutasi</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($mutasiList as $i => $m): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= date('d/m/Y', strtotime($m['tanggal_mutasi'])) ?></td>
                                <td>
                                    <p class="font-semibold"><?= htmlspecialchars($m['nama_lengkap']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($m['nisn']) ?></p>
                                </td>
                                <td>
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-bold <?= $m['jenis_mutasi'] === 'Masuk' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $m['jenis_mutasi'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($m['jenis_mutasi'] === 'Masuk'): ?>
                                        <span class="text-gray-500">Dari:</span> <?= htmlspecialchars($m['sekolah_asal']) ?>
                                    <?php else: ?>
                                        <span class="text-gray-500">Ke:</span> <?= htmlspecialchars($m['sekolah_tujuan']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($m['alasan']) ?></td>
                                <td class="text-center">
                                    <button onclick="deleteMutasi(<?= $m['id'] ?>)"
                                        class="px-3 py-1 bg-red-100 text-red-600 rounded-lg hover:bg-red-200">
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
    <div class="modal-content max-w-lg">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Tambah Mutasi Siswa</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Mutasi *</label>
                    <select name="jenis_mutasi" id="jenis_mutasi" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg" onchange="toggleSekolahField()">
                        <option value="Keluar">Mutasi Keluar</option>
                        <option value="Masuk">Mutasi Masuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Siswa *</label>
                    <select name="siswa_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg select2">
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswaList as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap']) ?> (<?= $s['nisn'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">* Pastikan data siswa sudah ada di Data Siswa</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mutasi *</label>
                    <input type="date" name="tanggal_mutasi" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div id="field_sekolah_asal" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sekolah Asal *</label>
                    <input type="text" name="sekolah_asal" placeholder="Nama Sekolah Asal"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div id="field_sekolah_tujuan">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sekolah Tujuan *</label>
                    <input type="text" name="sekolah_tujuan" placeholder="Nama Sekolah Tujuan"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan Mutasi *</label>
                    <textarea name="alasan" required rows="2" placeholder="Contoh: Pindah domisili orang tua"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-amber-600 to-amber-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete -->
<form method="POST" id="formDelete">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function toggleSekolahField() {
        const jenis = document.getElementById('jenis_mutasi').value;
        const fieldAsal = document.getElementById('field_sekolah_asal');
        const fieldTujuan = document.getElementById('field_sekolah_tujuan');

        if (jenis === 'Masuk') {
            fieldAsal.classList.remove('hidden');
            fieldTujuan.classList.add('hidden');
            fieldAsal.querySelector('input').required = true;
            fieldTujuan.querySelector('input').required = false;
        } else {
            fieldAsal.classList.add('hidden');
            fieldTujuan.classList.remove('hidden');
            fieldAsal.querySelector('input').required = false;
            fieldTujuan.querySelector('input').required = true;
        }
    }

    function deleteMutasi(id) {
        if (confirm('Yakin ingin menghapus data mutasi ini? Status siswa akan dikembalikan.')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('formDelete').submit();
        }
    }

    // Init toggle
    toggleSekolahField();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
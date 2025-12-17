<?php
$pageTitle = 'Manajemen Kelas - Kesiswaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Kelas.php';
require_once __DIR__ . '/../../classes/Siswa.php';
require_once __DIR__ . '/../../classes/AcademicSettings.php';
require_once __DIR__ . '/../../classes/TahunAjaran.php';

checkPermission('kesiswaan');

$kelas = new Kelas();
$siswa = new Siswa();
$academicSettings = new AcademicSettings();
$tahunAjaranObj = new TahunAjaran();

$currentAcademicYear = $academicSettings->getCurrentAcademicYear();
$allYears = $tahunAjaranObj->getAll();

// Filter Logic: Default to current active year if not set
$filterTahun = $_GET['tahun'] ?? $currentAcademicYear;
// If user explicitly wants 'all', handle it
$queryTahun = ($filterTahun === 'all') ? null : $filterTahun;

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $kelas->create([
                    'nama_kelas' => $_POST['nama_kelas'],
                    'wali_kelas' => $_POST['wali_kelas'] ?: null,
                    'tahun_ajaran' => $_POST['tahun_ajaran'] ?: null,
                    'keterangan' => $_POST['keterangan'] ?: null
                ]);

                if ($result) {
                    $message = 'Kelas berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan kelas!';
                    $messageType = 'error';
                }
                break;

            case 'update':
                $result = $kelas->update($_POST['id'], [
                    'nama_kelas' => $_POST['nama_kelas'],
                    'wali_kelas' => $_POST['wali_kelas'] ?: null,
                    'tahun_ajaran' => $_POST['tahun_ajaran'] ?: null,
                    'keterangan' => $_POST['keterangan'] ?: null
                ]);

                if ($result) {
                    $message = 'Data kelas berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui data kelas!';
                    $messageType = 'error';
                }
                break;

            case 'delete':
                // Cek apakah ada siswa di kelas ini
                $count = $siswa->getCountByKelas($_POST['id']);
                if ($count > 0) {
                    $message = 'Tidak dapat menghapus kelas karena masih ada ' . $count . ' siswa aktif!';
                    $messageType = 'error';
                } else {
                    if ($kelas->delete($_POST['id'])) {
                        $message = 'Kelas berhasil dihapus!';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal menghapus kelas!';
                        $messageType = 'error';
                    }
                }
                break;
        }
    }
}

// Get data
$kelasList = $kelas->getAll($queryTahun);
$totalKelas = count($kelasList);
$totalSiswa = $siswa->getTotalCount();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kesiswaan/index.php" class="hover:text-blue-600">Kesiswaan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Manajemen Kelas</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-chalkboard text-white text-xl"></i>
                    </div>
                    Manajemen Kelas
                </h2>
                <p class="text-gray-600 mt-2">Kelola data kelas dan wali kelas</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Kelas
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Kelas</p>
                    <p class="text-3xl font-bold text-indigo-600"><?= $totalKelas ?></p>
                </div>
                <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chalkboard text-indigo-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Siswa Aktif</p>
                    <p class="text-3xl font-bold text-emerald-600"><?= $totalSiswa ?></p>
                </div>
                <div class="w-14 h-14 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-graduate text-emerald-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Kelas</h3>
            <div class="flex gap-2">
                <select onchange="window.location.href='?tahun='+this.value"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="all">Semua Tahun Ajaran</option>
                    <?php foreach ($allYears as $y): ?>
                        <option value="<?= $y['tahun_ajaran'] ?>" <?= $filterTahun == $y['tahun_ajaran'] ? 'selected' : '' ?>>
                            <?= $y['tahun_ajaran'] ?>     <?= $y['is_active'] ? '(Aktif)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchInput" placeholder="Cari kelas..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 w-64">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">No</th>
                        <th class="text-left">Nama Kelas</th>
                        <th class="text-left">Wali Kelas</th>
                        <th class="text-left">Tahun Ajaran</th>
                        <th class="text-center">Jumlah Siswa</th>
                        <th class="text-left">Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kelasList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <i class="fas fa-chalkboard text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-semibold">Belum ada data kelas</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kelasList as $i => $k): ?>
                            <?php $jumlahSiswa = $siswa->getCountByKelas($k['id']); ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <span class="font-bold text-indigo-700"><?= htmlspecialchars($k['nama_kelas']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($k['wali_kelas'] ?? '-') ?></td>
                                <td>
                                    <span
                                        class="bg-gray-100 px-2 py-1 rounded text-sm"><?= htmlspecialchars($k['tahun_ajaran'] ?? '-') ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-emerald-100 text-emerald-700">
                                        <?= $jumlahSiswa ?> Siswa
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($k['keterangan'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="siswa.php?kelas=<?= $k['id'] ?>"
                                        class="px-3 py-1 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 mr-1"
                                        title="Lihat Siswa">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <button onclick='editKelas(<?= json_encode($k) ?>)'
                                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 mr-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteKelas(<?= $k['id'] ?>, <?= $jumlahSiswa ?>)"
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
            <h3 class="text-2xl font-bold text-gray-800">Tambah Kelas Baru</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kelas *</label>
                    <input type="text" name="nama_kelas" required placeholder="Contoh: X IPA 1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Wali Kelas</label>
                    <input type="text" name="wali_kelas" placeholder="Nama Wali Kelas"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran</label>
                    <select name="tahun_ajaran"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($allYears as $y): ?>
                            <option value="<?= $y['tahun_ajaran'] ?>" <?= $currentAcademicYear == $y['tahun_ajaran'] ? 'selected' : '' ?>>
                                <?= $y['tahun_ajaran'] ?>     <?= $y['is_active'] ? '(Aktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Edit Kelas</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formEdit">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Kelas *</label>
                    <input type="text" name="nama_kelas" id="edit_nama_kelas" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Wali Kelas</label>
                    <input type="text" name="wali_kelas" id="edit_wali_kelas"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Ajaran</label>
                    <select name="tahun_ajaran" id="edit_tahun_ajaran"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <?php foreach ($allYears as $y): ?>
                            <option value="<?= $y['tahun_ajaran'] ?>">
                                <?= $y['tahun_ajaran'] ?>     <?= $y['is_active'] ? '(Aktif)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Perbarui
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

    function editKelas(k) {
        document.getElementById('edit_id').value = k.id;
        document.getElementById('edit_nama_kelas').value = k.nama_kelas;
        document.getElementById('edit_wali_kelas').value = k.wali_kelas || '';
        document.getElementById('edit_tahun_ajaran').value = k.tahun_ajaran || '';
        document.getElementById('edit_keterangan').value = k.keterangan || '';
        openModal('modalEdit');
    }

    function deleteKelas(id, jumlahSiswa) {
        if (jumlahSiswa > 0) {
            alert('Tidak dapat menghapus kelas karena masih ada ' + jumlahSiswa + ' siswa aktif di kelas ini. Silakan pindahkan atau hapus siswa terlebih dahulu.');
            return;
        }

        if (confirm('Yakin ingin menghapus kelas ini?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('formDelete').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
$pageTitle = 'Arsip Digital - Persuratan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Arsip.php';

checkPermission('persuratan');

$arsip = new Arsip();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $fileArsip = null;
                if (isset($_FILES['file_arsip']) && $_FILES['file_arsip']['error'] === 0) {
                    $uploadDir = __DIR__ . '/../../uploads/arsip/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $fileName = time() . '_' . basename($_FILES['file_arsip']['name']);
                    if (move_uploaded_file($_FILES['file_arsip']['tmp_name'], $uploadDir . $fileName)) {
                        $fileArsip = $fileName;
                    }
                }

                $result = $arsip->create([
                    'kode_arsip' => $_POST['kode_arsip'],
                    'judul' => $_POST['judul'],
                    'deskripsi' => $_POST['deskripsi'] ?: null,
                    'jenis' => $_POST['jenis'],
                    'kategori' => $_POST['kategori'] ?: null,
                    'tanggal_arsip' => $_POST['tanggal_arsip'],
                    'file_arsip' => $fileArsip,
                    'lokasi_fisik' => $_POST['lokasi_fisik'] ?: null,
                    'created_by' => $_SESSION['user_id'] ?? null
                ]);

                if ($result) {
                    $message = 'Arsip berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan arsip!';
                    $messageType = 'error';
                }
                break;

            case 'update':
                $fileArsip = $_POST['existing_file'] ?? null;
                if (isset($_FILES['file_arsip']) && $_FILES['file_arsip']['error'] === 0) {
                    $uploadDir = __DIR__ . '/../../uploads/arsip/';
                    if (!is_dir($uploadDir))
                        mkdir($uploadDir, 0755, true);
                    $fileName = time() . '_' . basename($_FILES['file_arsip']['name']);
                    if (move_uploaded_file($_FILES['file_arsip']['tmp_name'], $uploadDir . $fileName)) {
                        $fileArsip = $fileName;
                    }
                }

                $result = $arsip->update($_POST['id'], [
                    'kode_arsip' => $_POST['kode_arsip'],
                    'judul' => $_POST['judul'],
                    'deskripsi' => $_POST['deskripsi'] ?: null,
                    'jenis' => $_POST['jenis'],
                    'kategori' => $_POST['kategori'] ?: null,
                    'tanggal_arsip' => $_POST['tanggal_arsip'],
                    'file_arsip' => $fileArsip,
                    'lokasi_fisik' => $_POST['lokasi_fisik'] ?: null
                ]);

                if ($result) {
                    $message = 'Arsip berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui arsip!';
                    $messageType = 'error';
                }
                break;

            case 'delete':
                if ($arsip->delete($_POST['id'])) {
                    $message = 'Arsip berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus arsip!';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get filter params
$filterJenis = $_GET['jenis'] ?? null;
$filterTahun = $_GET['tahun'] ?? null;

// Get data
$arsipList = $arsip->getAll($filterJenis, $filterTahun);
$countByJenis = $arsip->getCountByJenis();
$totalArsip = $arsip->getTotalCount();
$availableYears = $arsip->getAvailableYears();
$kodeArsipBaru = $arsip->generateKode($_GET['jenis'] ?? 'Dokumen');

$jenisOptions = ['Surat Masuk', 'Surat Keluar', 'Dokumen', 'Lainnya'];
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/persuratan/index.php" class="hover:text-blue-600">Persuratan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Arsip Digital</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-archive text-white text-xl"></i>
                    </div>
                    Arsip Digital
                </h2>
                <p class="text-gray-600 mt-2">Kelola arsip dokumen digital sekolah</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-cyan-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Arsip
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
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <a href="arsip.php"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= !$filterJenis ? 'ring-2 ring-cyan-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Arsip</p>
                    <p class="text-3xl font-bold text-cyan-600"><?= $totalArsip ?></p>
                </div>
                <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-archive text-cyan-600 text-xl"></i>
                </div>
            </div>
        </a>
        <a href="?jenis=Surat+Masuk"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= $filterJenis === 'Surat Masuk' ? 'ring-2 ring-blue-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Surat Masuk</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $countByJenis['Surat Masuk'] ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-inbox text-blue-600 text-xl"></i>
                </div>
            </div>
        </a>
        <a href="?jenis=Surat+Keluar"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= $filterJenis === 'Surat Keluar' ? 'ring-2 ring-orange-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Surat Keluar</p>
                    <p class="text-3xl font-bold text-orange-600"><?= $countByJenis['Surat Keluar'] ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-paper-plane text-orange-600 text-xl"></i>
                </div>
            </div>
        </a>
        <a href="?jenis=Dokumen"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= $filterJenis === 'Dokumen' ? 'ring-2 ring-green-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Dokumen</p>
                    <p class="text-3xl font-bold text-green-600"><?= $countByJenis['Dokumen'] ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-file-alt text-green-600 text-xl"></i>
                </div>
            </div>
        </a>
        <a href="?jenis=Lainnya"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg <?= $filterJenis === 'Lainnya' ? 'ring-2 ring-purple-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Lainnya</p>
                    <p class="text-3xl font-bold text-purple-600"><?= $countByJenis['Lainnya'] ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-folder text-purple-600 text-xl"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- Filter -->
    <?php if ($filterJenis || $filterTahun): ?>
        <div class="mb-4 flex items-center gap-2 flex-wrap">
            <span class="text-gray-600">Filter aktif:</span>
            <?php if ($filterJenis): ?>
                <span class="px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-sm font-medium">
                    Jenis: <?= htmlspecialchars($filterJenis) ?>
                </span>
            <?php endif; ?>
            <?php if ($filterTahun): ?>
                <span class="px-3 py-1 bg-cyan-100 text-cyan-700 rounded-full text-sm font-medium">
                    Tahun: <?= htmlspecialchars($filterTahun) ?>
                </span>
            <?php endif; ?>
            <a href="arsip.php" class="text-red-600 hover:underline text-sm">
                <i class="fas fa-times mr-1"></i>Hapus filter
            </a>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Arsip</h3>
            <div class="flex gap-2">
                <select id="filterTahun" onchange="filterByTahun()" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($availableYears as $y): ?>
                        <option value="<?= $y ?>" <?= $filterTahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="searchInput" placeholder="Cari kode/judul..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 w-64">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">No</th>
                        <th class="text-left">Kode</th>
                        <th class="text-left">Judul</th>
                        <th class="text-left">Jenis</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">File</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($arsipList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-archive text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-semibold">Belum ada arsip</p>
                                    <p class="text-sm">Klik "Tambah Arsip" untuk menambahkan dokumen</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($arsipList as $i => $a): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><span
                                        class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($a['kode_arsip']) ?></span>
                                </td>
                                <td>
                                    <p class="font-semibold"><?= htmlspecialchars($a['judul']) ?></p>
                                    <?php if ($a['kategori']): ?>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($a['kategori']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $jenisClass = [
                                        'Surat Masuk' => 'bg-blue-100 text-blue-700',
                                        'Surat Keluar' => 'bg-orange-100 text-orange-700',
                                        'Dokumen' => 'bg-green-100 text-green-700',
                                        'Lainnya' => 'bg-purple-100 text-purple-700'
                                    ][$a['jenis']] ?? 'bg-gray-100 text-gray-700';
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $jenisClass ?>">
                                        <?= htmlspecialchars($a['jenis']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($a['tanggal_arsip'])) ?></td>
                                <td>
                                    <?php if ($a['file_arsip']): ?>
                                        <a href="/e-TU/uploads/arsip/<?= $a['file_arsip'] ?>" target="_blank"
                                            class="text-cyan-600 hover:underline">
                                            <i class="fas fa-download mr-1"></i>Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button onclick='editArsip(<?= json_encode($a) ?>)'
                                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 mr-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteArsip(<?= $a['id'] ?>)"
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
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Tambah Arsip Baru</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Arsip *</label>
                    <input type="text" name="kode_arsip" id="add_kode" value="<?= $kodeArsipBaru ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis *</label>
                    <select name="jenis" id="add_jenis" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg" onchange="updateKode()">
                        <?php foreach ($jenisOptions as $j): ?>
                            <option value="<?= $j ?>"><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Arsip *</label>
                    <input type="text" name="judul" required placeholder="Judul dokumen arsip..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <input type="text" name="kategori" placeholder="Contoh: SK, Proposal, Laporan..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Arsip *</label>
                    <input type="date" name="tanggal_arsip" value="<?= date('Y-m-d') ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File</label>
                    <input type="file" name="file_arsip" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.rar,.zip"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi Fisik</label>
                    <input type="text" name="lokasi_fisik" placeholder="Lemari A, Rak 2..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="2" placeholder="Deskripsi singkat..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-cyan-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Edit Arsip</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="formEdit">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="existing_file" id="edit_existing_file">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Arsip *</label>
                    <input type="text" name="kode_arsip" id="edit_kode_arsip" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis *</label>
                    <select name="jenis" id="edit_jenis" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <?php foreach ($jenisOptions as $j): ?>
                            <option value="<?= $j ?>"><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Arsip *</label>
                    <input type="text" name="judul" id="edit_judul" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <input type="text" name="kategori" id="edit_kategori"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Arsip *</label>
                    <input type="date" name="tanggal_arsip" id="edit_tanggal_arsip" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ganti File</label>
                    <input type="file" name="file_arsip" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.rar,.zip"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1" id="current_file_info"></p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi Fisik</label>
                    <input type="text" name="lokasi_fisik" id="edit_lokasi_fisik"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
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

    function filterByTahun() {
        const tahun = document.getElementById('filterTahun').value;
        const url = new URL(window.location.href);
        if (tahun) {
            url.searchParams.set('tahun', tahun);
        } else {
            url.searchParams.delete('tahun');
        }
        window.location.href = url.toString();
    }

    function updateKode() {
        const jenis = document.getElementById('add_jenis').value;
        const prefix = {
            'Surat Masuk': 'SM',
            'Surat Keluar': 'SK',
            'Dokumen': 'DOK',
            'Lainnya': 'ARS'
        }[jenis] || 'ARS';
        const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        document.getElementById('add_kode').value = prefix + '-' + today + '-001';
    }

    function editArsip(a) {
        document.getElementById('edit_id').value = a.id;
        document.getElementById('edit_kode_arsip').value = a.kode_arsip;
        document.getElementById('edit_jenis').value = a.jenis;
        document.getElementById('edit_judul').value = a.judul;
        document.getElementById('edit_kategori').value = a.kategori || '';
        document.getElementById('edit_tanggal_arsip').value = a.tanggal_arsip;
        document.getElementById('edit_lokasi_fisik').value = a.lokasi_fisik || '';
        document.getElementById('edit_deskripsi').value = a.deskripsi || '';
        document.getElementById('edit_existing_file').value = a.file_arsip || '';
        document.getElementById('current_file_info').textContent = a.file_arsip ? 'File: ' + a.file_arsip : 'Belum ada file';
        openModal('modalEdit');
    }

    function deleteArsip(id) {
        if (confirm('Yakin ingin menghapus arsip ini?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('formDelete').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
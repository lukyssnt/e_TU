<?php
$pageTitle = 'Surat Masuk - Persuratan & Kearsipan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/SuratMasuk.php';

checkPermission('persuratan');

$suratMasuk = new SuratMasuk();
$message = '';
$messageType = 'success';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create') {
            $fileUpload = null;
            if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['file_surat'], 'uploads/surat_masuk/');
                if ($upload['success']) {
                    $fileUpload = $upload['path'];
                }
            }

            $data = [
                'nomor_surat' => clean($_POST['nomor_surat']),
                'tanggal_terima' => $_POST['tanggal_terima'],
                'pengirim' => clean($_POST['pengirim']),
                'perihal' => clean($_POST['perihal']),
                'sifat_surat' => $_POST['sifat_surat'],
                'file_surat' => $fileUpload,
                'created_by' => Session::get('user_id')
            ];

            if ($suratMasuk->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Surat masuk berhasil ditambahkan!', 'success');
            } else {
                $message = 'Gagal menambahkan surat masuk!';
                $messageType = 'error';
            }
        } elseif ($action === 'update') {
            $id = $_POST['id'];
            $fileUpload = null;

            if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['file_surat'], 'uploads/surat_masuk/');
                if ($upload['success']) {
                    $fileUpload = $upload['path'];
                }
            }

            $data = [
                'nomor_surat' => clean($_POST['nomor_surat']),
                'tanggal_terima' => $_POST['tanggal_terima'],
                'pengirim' => clean($_POST['pengirim']),
                'perihal' => clean($_POST['perihal']),
                'sifat_surat' => $_POST['sifat_surat']
            ];

            if ($fileUpload) {
                $data['file_surat'] = $fileUpload;
            }

            if ($suratMasuk->update($id, $data)) {
                redirect($_SERVER['PHP_SELF'], 'Surat masuk berhasil diupdate!', 'success');
            } else {
                $message = 'Gagal mengupdate surat masuk!';
                $messageType = 'error';
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            if ($suratMasuk->delete($id)) {
                redirect($_SERVER['PHP_SELF'], 'Surat masuk berhasil dihapus!', 'success');
            } else {
                $message = 'Gagal menghapus surat masuk!';
                $messageType = 'error';
            }
        }
    }
}

// Get all surat masuk
$allSuratMasuk = $suratMasuk->getAll();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<!-- Main Content -->
<main class="lg:ml-72 min-h-screen p-6">

    <!-- Header -->
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/persuratan/index.php" class="hover:text-blue-600">Persuratan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Surat Masuk</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-inbox text-white text-xl"></i>
                    </div>
                    Surat Masuk
                </h2>
                <p class="text-gray-600 mt-2">Kelola surat masuk dan disposisi</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-lg font-semibold smooth-transition shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Surat Masuk
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Surat</p>
                    <p class="text-2xl font-bold text-gray-800"><?= count($allSuratMasuk) ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-envelope text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Belum Disposisi</p>
                    <p class="text-2xl font-bold text-amber-600">
                        <?= count(array_filter($allSuratMasuk, fn($s) => $s['status'] === 'Belum Disposisi')) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Sudah Disposisi</p>
                    <p class="text-2xl font-bold text-green-600">
                        <?= count(array_filter($allSuratMasuk, fn($s) => $s['status'] === 'Sudah Disposisi')) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Selesai</p>
                    <p class="text-2xl font-bold text-purple-600">
                        <?= count(array_filter($allSuratMasuk, fn($s) => $s['status'] === 'Selesai')) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-double text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Daftar Surat Masuk</h3>
            <div class="flex gap-2">
                <input type="text" id="searchInput" placeholder="Cari surat..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <select id="filterStatus"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status</option>
                    <option value="Belum Disposisi">Belum Disposisi</option>
                    <option value="Sudah Disposisi">Sudah Disposisi</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="tableSuratMasuk">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">No. Surat</th>
                        <th class="text-left">Tgl Terima</th>
                        <th class="text-left">Pengirim</th>
                        <th class="text-left">Perihal</th>
                        <th class="text-left">Sifat</th>
                        <th class="text-left">Status</th>
                        <th class="text-left">File</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allSuratMasuk) > 0): ?>
                        <?php foreach ($allSuratMasuk as $index => $surat): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="font-semibold"><?= htmlspecialchars($surat['nomor_surat']) ?></td>
                                <td><?= formatTanggal($surat['tanggal_terima'], 'short') ?></td>
                                <td><?= htmlspecialchars($surat['pengirim']) ?></td>
                                <td><?= htmlspecialchars($surat['perihal']) ?></td>
                                <td>
                                    <span
                                        class="badge badge-<?= $surat['sifat_surat'] === 'Segera' ? 'danger' : ($surat['sifat_surat'] === 'Penting' ? 'warning' : 'info') ?>">
                                        <?= $surat['sifat_surat'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span
                                        class="status-pill <?= $surat['status'] === 'Belum Disposisi' ? 'pending' : ($surat['status'] === 'Selesai' ? 'approved' : '') ?>">
                                        <?= $surat['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($surat['file_surat']): ?>
                                        <a href="/e-TU/<?= $surat['file_surat'] ?>" target="_blank"
                                            class="text-blue-600 hover:text-blue-800">
                                            <i class="fas <?= getFileIcon($surat['file_surat']) ?> mr-1"></i>
                                            Lihat
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick="viewSurat(<?= $surat['id'] ?>)"
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm"
                                            title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editSurat(<?= $surat['id'] ?>)"
                                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteSurat(<?= $surat['id'] ?>)"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm"
                                            title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php if ($surat['status'] === 'Belum Disposisi'): ?>
                                            <a href="/e-TU/modules/persuratan/disposisi.php?surat_id=<?= $surat['id'] ?>"
                                                class="px-3 py-1.5 bg-purple-500 hover:bg-purple-600 text-white rounded text-sm"
                                                title="Disposisi">
                                                <i class="fas fa-share"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-500">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-inbox"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada surat masuk</p>
                                    <p class="text-sm">Klik tombol "Tambah Surat Masuk" untuk menambah data</p>
                                </div>
                            </td>
                        </tr>
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
            <h3 class="text-2xl font-bold text-gray-800">Tambah Surat Masuk</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="formAdd">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Surat</label>
                    <input type="text" name="nomor_surat" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Terima</label>
                    <input type="date" name="tanggal_terima" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sifat Surat</label>
                    <select name="sifat_surat" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        <option value="Biasa">Biasa</option>
                        <option value="Penting">Penting</option>
                        <option value="Segera">Segera</option>
                        <option value="Rahasia">Rahasia</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pengirim</label>
                    <input type="text" name="pengirim" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perihal</label>
                    <textarea name="perihal" required rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File Surat (PDF/DOCX)</label>
                    <input type="file" name="file_surat" accept=".pdf,.doc,.docx"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    <p class="text-xs text-gray-500 mt-1">Maksimal 5MB</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        filterTable(searchTerm, document.getElementById('filterStatus').value);
    });

    // Filter by status
    document.getElementById('filterStatus').addEventListener('change', function () {
        filterTable(document.getElementById('searchInput').value.toLowerCase(), this.value);
    });

    function filterTable(searchTerm, status) {
        const rows = document.querySelectorAll('#tableSuratMasuk tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const statusCell = row.cells[6]?.textContent.trim();
            const matchSearch = text.includes(searchTerm);
            const matchStatus = status === '' || statusCell === status;

            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    function viewSurat(id) {
        // Implementation for view detail
        alert('View detail surat ID: ' + id);
    }

    function editSurat(id) {
        // Implementation for edit
        alert('Edit surat ID: ' + id);
    }

    function deleteSurat(id) {
        if (confirmDelete('Apakah Anda yakin ingin menghapus surat masuk ini?')) {
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
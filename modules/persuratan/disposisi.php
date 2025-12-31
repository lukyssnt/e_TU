<?php
$pageTitle = 'Disposisi Digital - Persuratan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Disposisi.php';
require_once __DIR__ . '/../../classes/Pegawai.php';
require_once __DIR__ . '/../../classes/SuratMasuk.php';

checkPermission('persuratan');

// Handle AJAX requests AFTER permission check
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_detail' && isset($_GET['id'])) {
    header('Content-Type: application/json');

    $disposisi = new Disposisi();
    $detail = $disposisi->getById($_GET['id']);

    if ($detail) {
        echo json_encode(['success' => true, 'data' => $detail]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Disposisi tidak ditemukan']);
    }
    exit;
}

$disposisi = new Disposisi();
$pegawai = new Pegawai();
$suratMasuk = new SuratMasuk();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $result = $disposisi->create([
                    'surat_masuk_id' => $_POST['surat_masuk_id'],
                    'dari_pegawai_id' => $_POST['dari_pegawai_id'] ?: null,
                    'kepada_pegawai_id' => $_POST['kepada_pegawai_id'],
                    'instruksi' => $_POST['instruksi'],
                    'deadline' => $_POST['deadline'] ?: null,
                    'status' => 'Pending',
                    'catatan' => $_POST['catatan'] ?: null
                ]);

                if ($result) {
                    $message = 'Disposisi berhasil dibuat!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal membuat disposisi!';
                    $messageType = 'error';
                }
                break;

            case 'update_status':
                $result = $disposisi->updateStatus($_POST['id'], $_POST['status'], $_POST['catatan'] ?? null);
                if ($result) {
                    $message = 'Status disposisi berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui status!';
                    $messageType = 'error';
                }
                break;

            case 'delete':
                if ($disposisi->delete($_POST['id'])) {
                    $message = 'Disposisi berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus disposisi!';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get data
$filterStatus = $_GET['status'] ?? null;
$disposisiList = $disposisi->getAll($filterStatus);
$statusCounts = $disposisi->getStatusCounts();
$pegawaiList = $pegawai->getAll();
$suratBelumDisposisi = $disposisi->getSuratBelumDisposisi();
$totalDisposisi = $disposisi->getTotalCount();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/persuratan/index.php" class="hover:text-blue-600">Persuratan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Disposisi Digital</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-share text-white text-xl"></i>
                    </div>
                    Disposisi Digital
                </h2>
                <p class="text-gray-600 mt-2">Kelola disposisi surat masuk ke pegawai</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-semibold shadow-lg <?= empty($suratBelumDisposisi) ? 'opacity-50 cursor-not-allowed' : '' ?>"
                <?= empty($suratBelumDisposisi) ? 'disabled' : '' ?>>
                <i class="fas fa-plus mr-2"></i>Buat Disposisi
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Disposisi</p>
                    <p class="text-3xl font-bold text-purple-600"><?= $totalDisposisi ?></p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-share text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <a href="?status=Pending"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow <?= $filterStatus === 'Pending' ? 'ring-2 ring-amber-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-3xl font-bold text-amber-600"><?= $statusCounts['Pending'] ?></p>
                </div>
                <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-2xl"></i>
                </div>
            </div>
        </a>
        <a href="?status=Proses"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow <?= $filterStatus === 'Proses' ? 'ring-2 ring-blue-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Proses</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $statusCounts['Proses'] ?></p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-spinner text-blue-600 text-2xl"></i>
                </div>
            </div>
        </a>
        <a href="?status=Selesai"
            class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow <?= $filterStatus === 'Selesai' ? 'ring-2 ring-green-500' : '' ?>">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Selesai</p>
                    <p class="text-3xl font-bold text-green-600"><?= $statusCounts['Selesai'] ?></p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
        </a>
    </div>

    <?php if ($filterStatus): ?>
        <div class="mb-4">
            <a href="disposisi.php" class="text-purple-600 hover:underline">
                <i class="fas fa-times mr-1"></i>Hapus filter (<?= $filterStatus ?>)
            </a>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Disposisi</h3>
            <input type="text" id="searchInput" placeholder="Cari..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 w-64">
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">No</th>
                        <th class="text-left">Surat Masuk</th>
                        <th class="text-left">Kepada</th>
                        <th class="text-left">Instruksi</th>
                        <th class="text-left">Deadline</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($disposisiList)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-12 text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-share text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-lg font-semibold">Belum ada disposisi</p>
                                    <p class="text-sm">
                                        <?php if (empty($suratBelumDisposisi)): ?>
                                            Semua surat masuk sudah didisposisikan
                                        <?php else: ?>
                                            Klik "Buat Disposisi" untuk mendisposisikan surat masuk
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($disposisiList as $i => $d): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div>
                                        <p class="font-mono text-sm bg-gray-100 px-2 py-1 rounded inline-block">
                                            <?= htmlspecialchars($d['nomor_surat'] ?? '-') ?>
                                        </p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <?= htmlspecialchars($d['surat_perihal'] ?? '-') ?>
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-purple-600 text-xs"></i>
                                        </div>
                                        <span class="font-semibold"><?= htmlspecialchars($d['kepada_nama'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="max-w-xs">
                                    <p class="truncate"><?= htmlspecialchars($d['instruksi']) ?></p>
                                </td>
                                <td>
                                    <?php if ($d['deadline']): ?>
                                        <span
                                            class="<?= strtotime($d['deadline']) < time() && $d['status'] !== 'Selesai' ? 'text-red-600 font-bold' : '' ?>">
                                            <?= date('d/m/Y', strtotime($d['deadline'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusClass = [
                                        'Pending' => 'bg-amber-100 text-amber-700',
                                        'Proses' => 'bg-blue-100 text-blue-700',
                                        'Selesai' => 'bg-green-100 text-green-700'
                                    ][$d['status']] ?? 'bg-gray-100 text-gray-700';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $statusClass ?>">
                                        <?= $d['status'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button onclick='updateStatus(<?= json_encode($d) ?>)'
                                        class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 mr-1"
                                        title="Update Status">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                    <button onclick="viewDetail(<?= $d['id'] ?>)"
                                        class="px-3 py-1 bg-purple-100 text-purple-600 rounded-lg hover:bg-purple-200 mr-1"
                                        title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="deleteDisposisi(<?= $d['id'] ?>)"
                                        class="px-3 py-1 bg-red-100 text-red-600 rounded-lg hover:bg-red-200" title="Hapus">
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
            <h3 class="text-2xl font-bold text-gray-800">Buat Disposisi Baru</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Surat Masuk *</label>
                    <select name="surat_masuk_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">-- Pilih Surat Masuk --</option>
                        <?php foreach ($suratBelumDisposisi as $sm): ?>
                            <option value="<?= $sm['id'] ?>"><?= htmlspecialchars($sm['nomor_surat']) ?> -
                                <?= htmlspecialchars($sm['perihal']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Dari (Pemberi Disposisi)</label>
                        <select name="dari_pegawai_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">-- Kepala TU --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kepada (Penerima) *</label>
                        <select name="kepada_pegawai_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            <option value="">-- Pilih Pegawai --</option>
                            <?php foreach ($pegawaiList as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_lengkap']) ?> -
                                    <?= htmlspecialchars($p['nama_jabatan'] ?? '-') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instruksi *</label>
                    <textarea name="instruksi" required rows="3" placeholder="Isi instruksi disposisi..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Deadline</label>
                        <input type="date" name="deadline" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                        <input type="text" name="catatan" placeholder="Catatan tambahan..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Buat Disposisi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Status -->
<div id="modalStatus" class="modal-overlay">
    <div class="modal-content max-w-md">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Update Status</h3>
            <button onclick="closeModal('modalStatus')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formStatus">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="status_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="status_select" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="Pending">Pending</option>
                        <option value="Proses">Proses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <textarea name="catatan" id="status_catatan" rows="3" placeholder="Catatan update..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalStatus')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div id="modalDetail" class="modal-overlay">
    <div class="modal-content max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800"><i class="fas fa-info-circle text-purple-600 mr-2"></i>Detail
                Disposisi</h3>
            <button onclick="closeModal('modalDetail')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <div id="detailContent" class="space-y-4">
            <!-- Loading state -->
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-3"></i>
                <p class="text-gray-600">Memuat detail...</p>
            </div>
        </div>

        <div class="mt-6 flex gap-3 justify-end">
            <button type="button" onclick="closeModal('modalDetail')"
                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                Tutup
            </button>
        </div>
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

    function updateStatus(d) {
        document.getElementById('status_id').value = d.id;
        document.getElementById('status_select').value = d.status;
        document.getElementById('status_catatan').value = d.catatan || '';
        openModal('modalStatus');
    }

    function viewDetail(id) {
        openModal('modalDetail');

        // Show loading
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-spinner fa-spin text-4xl text-purple-600 mb-3"></i>
                <p class="text-gray-600">Memuat detail...</p>
            </div>
        `;

        // Fetch detail
        fetch(`?ajax=get_detail&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const d = data.data;

                    // Status badge
                    const statusColors = {
                        'Pending': 'bg-amber-100 text-amber-700 border-amber-300',
                        'Proses': 'bg-blue-100 text-blue-700 border-blue-300',
                        'Selesai': 'bg-green-100 text-green-700 border-green-300'
                    };
                    const statusClass = statusColors[d.status] || 'bg-gray-100 text-gray-700 border-gray-300';

                    // Deadline warning
                    const isOverdue = d.deadline && new Date(d.deadline) < new Date() && d.status !== 'Selesai';
                    const deadlineClass = isOverdue ? 'text-red-600 font-bold' : 'text-gray-700';

                    document.getElementById('detailContent').innerHTML = `
                        <!-- Header Info -->
                        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-bold mb-2">Disposisi #${d.id}</h4>
                                    <p class="text-purple-100 text-sm"><i class="fas fa-calendar mr-2"></i>Dibuat: ${new Date(d.created_at).toLocaleString('id-ID')}</p>
                                </div>
                                <span class="px-4 py-2 ${statusClass} border-2 rounded-lg font-bold text-lg">
                                    ${d.status}
                                </span>
                            </div>
                        </div>

                        <!-- Surat Masuk Info -->
                        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-5">
                            <h5 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-envelope text-blue-600"></i>
                                Informasi Surat Masuk
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-white p-4 rounded-lg">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Nomor Surat</p>
                                    <p class="font-mono text-sm font-semibold bg-gray-100 px-3 py-2 rounded">${d.nomor_surat || '-'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Tanggal Surat</p>
                                    <p class="font-semibold text-sm">${d.tanggal_surat ? new Date(d.tanggal_surat).toLocaleDateString('id-ID') : '-'}</p>
                                </div>
                                <div class="md:col-span-2">
                                    <p class="text-xs text-gray-500 mb-1">Perihal</p>
                                    <p class="font-semibold">${d.surat_perihal || '-'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Pengirim</p>
                                    <p class="font-semibold">${d.surat_pengirim || '-'}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Tanggal Diterima</p>
                                    <p class="font-semibold">${d.tanggal_terima ? new Date(d.tanggal_terima).toLocaleDateString('id-ID') : '-'}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Disposisi Detail -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Dari -->
                            <div class="bg-white border-2 border-gray-200 rounded-xl p-5">
                                <h5 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-user-tie text-purple-600"></i>
                                    Pemberi Disposisi
                                </h5>
                                <div class="flex items-center gap-3 bg-purple-50 p-4 rounded-lg">
                                    <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                        ${d.dari_nama ? d.dari_nama.charAt(0).toUpperCase() : 'K'}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">${d.dari_nama || 'Kepala TU'}</p>
                                        <p class="text-sm text-purple-600">${d.dari_jabatan || 'Kepala Tata Usaha'}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Kepada -->
                            <div class="bg-white border-2 border-gray-200 rounded-xl p-5">
                                <h5 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                                    <i class="fas fa-user-check text-green-600"></i>
                                    Penerima Disposisi
                                </h5>
                                <div class="flex items-center gap-3 bg-green-50 p-4 rounded-lg">
                                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                        ${d.kepada_nama ? d.kepada_nama.charAt(0).toUpperCase() : '?'}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">${d.kepada_nama || '-'}</p>
                                        <p class="text-sm text-green-600">${d.kepada_jabatan || '-'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instruksi & Deadline -->
                        <div class="bg-white border-2 border-gray-200 rounded-xl p-5">
                            <h5 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                                <i class="fas fa-clipboard-list text-indigo-600"></i>
                                Instruksi Disposisi
                            </h5>
                            <div class="bg-gray-50 p-4 rounded-lg mb-3">
                                <p class="text-gray-800 leading-relaxed">${d.instruksi || '-'}</p>
                            </div>
                            ${d.deadline ? `
                            <div class="flex items-center gap-2 ${isOverdue ? 'bg-red-50 border-red-300' : 'bg-gray-100 border-gray-300'} border-2 p-3 rounded-lg">
                                <i class="fas fa-clock ${deadlineClass}"></i>
                                <span class="${deadlineClass}">
                                    <strong>Deadline:</strong> ${new Date(d.deadline).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                                    ${isOverdue ? '<span class="ml-2 font-bold">(TERLAMBAT!)</span>' : ''}
                                </span>
                            </div>
                            ` : ''}
                        </div>

                        <!-- Catatan -->
                        ${d.catatan ? `
                        <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-5">
                            <h5 class="font-bold text-amber-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-sticky-note text-amber-600"></i>
                                Catatan
                            </h5>
                            <p class="text-gray-700 leading-relaxed">${d.catatan}</p>
                        </div>
                        ` : ''}

                        <!-- Timeline -->
                        <div class="bg-white border-2 border-gray-200 rounded-xl p-5">
                            <h5 class="font-bold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="fas fa-history text-gray-600"></i>
                                Timeline
                            </h5>
                            <div class="space-y-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-plus text-purple-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800">Disposisi Dibuat</p>
                                        <p class="text-sm text-gray-600">${new Date(d.created_at).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                    </div>
                                </div>
                                ${d.updated_at && d.updated_at !== d.created_at ? `
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-sync text-blue-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-800">Terakhir Diupdate</p>
                                        <p class="text-sm text-gray-600">${new Date(d.updated_at).toLocaleString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                } else {
                    document.getElementById('detailContent').innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-3"></i>
                            <p class="text-red-600 font-semibold">${data.message || 'Gagal memuat detail'}</p>
                        </div>
                    `;
                }
            })
            .catch(err => {
                document.getElementById('detailContent').innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-times-circle text-4xl text-red-500 mb-3"></i>
                        <p class="text-red-600 font-semibold">Error: ${err.message}</p>
                    </div>
                `;
            });
    }

    function deleteDisposisi(id) {
        if (confirm('Yakin ingin menghapus disposisi ini?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('formDelete').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
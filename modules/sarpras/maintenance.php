<?php
$pageTitle = 'Maintenance Aset';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Maintenance.php';
require_once __DIR__ . '/../../classes/Aset.php';

checkPermission('sarpras');

$maintenance = new Maintenance();
$aset = new Aset();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'add') {
                $data = [
                    'aset_id' => $_POST['aset_id'],
                    'tanggal_maintenance' => $_POST['tanggal_maintenance'],
                    'jenis_maintenance' => $_POST['jenis_maintenance'],
                    'deskripsi' => $_POST['deskripsi'] ?? '',
                    'biaya' => $_POST['biaya'] ?? 0,
                    'teknisi' => $_POST['teknisi'] ?? '',
                    'status' => $_POST['status'] ?? 'Proses',
                    'keterangan' => $_POST['keterangan'] ?? ''
                ];

                if ($maintenance->create($data)) {
                    redirect($_SERVER['PHP_SELF'], 'Data maintenance berhasil ditambahkan!', 'success');
                }
            } elseif ($_POST['action'] === 'delete' && isset($_POST['id'])) {
                if ($maintenance->delete($_POST['id'])) {
                    redirect($_SERVER['PHP_SELF'], 'Data maintenance berhasil dihapus!', 'success');
                }
            } elseif ($_POST['action'] === 'selesai' && isset($_POST['id'])) {
                if ($maintenance->updateStatus($_POST['id'], 'Selesai')) {
                    redirect($_SERVER['PHP_SELF'], 'Maintenance berhasil diselesaikan!', 'success');
                }
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get all data
$allMaintenance = $maintenance->getAll();
$allAset = $aset->getAll();
$totalMaintenance = count($allMaintenance);
$maintenanceProses = count(array_filter($allMaintenance, fn($m) => $m['status'] === 'Proses'));
$maintenanceBulanIni = $maintenance->getCountByMonth();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/sarpras/index.php" class="hover:text-blue-600">Sarana Prasarana</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Maintenance Aset</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-tools text-white text-xl"></i>
                    </div>
                    Maintenance Aset
                </h2>
                <p class="text-gray-600 mt-2">Kelola data maintenance dan perbaikan aset</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Maintenance
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Maintenance</p>
                    <p class="text-2xl font-bold text-orange-600"><?= $totalMaintenance ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-600"><?= $maintenanceBulanIni ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Dalam Proses</p>
                    <p class="text-2xl font-bold text-blue-600"><?= $maintenanceProses ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-spinner text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Maintenance Aset</h3>
            <div class="flex gap-2">
                <input type="text" id="searchInput" placeholder="Cari data..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Kode</th>
                        <th class="text-left">Aset</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Jenis Maintenance</th>
                        <th class="text-right">Biaya</th>
                        <th class="text-left">Teknisi</th>
                        <th class="text-left">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allMaintenance) > 0): ?>
                        <?php foreach ($allMaintenance as $index => $m): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="font-semibold"><?= htmlspecialchars($m['kode_maintenance']) ?></td>
                                <td>
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($m['nama_barang']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($m['kode_aset']) ?></p>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($m['tanggal_maintenance'])) ?></td>
                                <td><?= htmlspecialchars($m['jenis_maintenance']) ?></td>
                                <td class="text-right"><?= formatRupiah($m['biaya']) ?></td>
                                <td><?= htmlspecialchars($m['teknisi'] ?? '-') ?></td>
                                <td>
                                    <?php if ($m['status'] === 'Proses'): ?>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                            <i class="fas fa-spinner mr-1"></i>Proses
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                            <i class="fas fa-check mr-1"></i>Selesai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($m['status'] === 'Proses'): ?>
                                            <button onclick="selesaiMaintenance(<?= $m['id'] ?>)"
                                                class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg text-sm font-semibold">
                                                <i class="fas fa-check mr-1"></i>Selesai
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteMaintenance(<?= $m['id'] ?>)"
                                            class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-500">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada data maintenance</p>
                                    <p class="text-sm">Klik tombol "Tambah Maintenance" untuk menambahkan</p>
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
            <h3 class="text-2xl font-bold text-gray-800">Tambah Maintenance Aset</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formAdd">
            <input type="hidden" name="action" value="add">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Aset *</label>
                    <select name="aset_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">-- Pilih Aset --</option>
                        <?php foreach ($allAset as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['kode_aset']) ?> -
                                <?= htmlspecialchars($a['nama_barang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Maintenance *</label>
                    <input type="date" name="tanggal_maintenance" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Maintenance *</label>
                    <select name="jenis_maintenance" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">-- Pilih Jenis --</option>
                        <option value="Perbaikan Rutin">Perbaikan Rutin</option>
                        <option value="Perbaikan Darurat">Perbaikan Darurat</option>
                        <option value="Penggantian Spare Part">Penggantian Spare Part</option>
                        <option value="Kalibrasi">Kalibrasi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Biaya</label>
                    <input type="number" name="biaya" value="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Teknisi</label>
                    <input type="text" name="teknisi"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
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

    // Delete function
    function deleteMaintenance(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data maintenance ini?')) {
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

    // Complete maintenance function
    function selesaiMaintenance(id) {
        if (confirm('Tandai maintenance ini sebagai selesai?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="selesai">
            <input type="hidden" name="id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
require_once __DIR__ . '/../../config/session.php'; // Ensure session is started first if not already
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/TransaksiKas.php';

checkPermission('keuangan');

$kas = new TransaksiKas();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        if ($kas->delete($id)) {
            logActivity('DELETE', 'Keuangan', "Menghapus transaksi kas ID: $id");
            Session::setFlash('success', 'Transaksi berhasil dihapus!');
        } else {
            Session::setFlash('error', 'Gagal menghapus transaksi.');
        }
        header('Location: kas.php');
        exit;
    }

    $data = [
        'tanggal' => $_POST['tanggal'],
        'jenis_transaksi' => $_POST['jenis_transaksi'],
        'kategori' => $_POST['kategori'],
        'keterangan' => clean($_POST['keterangan']),
        'nominal' => (float) $_POST['nominal'],
        'created_by' => Session::get('user_id')
    ];

    if ($action === 'create') {
        if ($kas->create($data)) {
            logActivity('CREATE', 'Keuangan', "Menambahkan transaksi kas: " . $data['keterangan']);
            Session::setFlash('success', 'Transaksi berhasil ditambahkan!');
        } else {
            Session::setFlash('error', 'Gagal menambahkan transaksi.');
        }
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        if ($kas->update($id, $data)) {
            logActivity('UPDATE', 'Keuangan', "Mengupdate transaksi kas ID: $id");
            Session::setFlash('success', 'Transaksi berhasil diperbarui!');
        } else {
            Session::setFlash('error', 'Gagal memperbarui transaksi.');
        }
    }

    header('Location: kas.php');
    exit;
}

$allTransaksi = $kas->getAll();
$saldo = $kas->getSaldoAkhir();

$pageTitle = 'Transaksi Kas - Keuangan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/keuangan/index.php" class="hover:text-blue-600">Keuangan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Transaksi Kas</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-cash-register text-white text-xl"></i>
                    </div>
                    Transaksi Kas
                </h2>
                <p class="text-gray-600 mt-2">Input transaksi kas masuk dan keluar</p>
            </div>
            <button onclick="openModalAdd()"
                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Transaksi
            </button>
        </div>
    </div>

    <!-- Saldo Card -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl p-8 text-white mb-6 shadow-2xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm mb-2">Saldo Kas Saat Ini</p>
                <p class="text-5xl font-bold"><?= formatRupiah($saldo) ?></p>
            </div>
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center">
                <i class="fas fa-wallet text-5xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Pemasukan</p>
                    <p class="text-2xl font-bold text-green-600"><?= formatRupiah($kas->getTotalPemasukan()) ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Pengeluaran</p>
                    <p class="text-2xl font-bold text-red-600"><?= formatRupiah($kas->getTotalPengeluaran()) ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Transaksi</h3>
            <input type="text" id="searchInput" placeholder="Cari transaksi..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Jenis</th>
                        <th class="text-left">Kategori</th>
                        <th class="text-left">Keterangan</th>
                        <th class="text-right">Nominal</th>
                        <th class="text-right">Saldo</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allTransaksi) > 0): ?>
                        <?php foreach ($allTransaksi as $index => $t): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= formatTanggal($t['tanggal'], 'short') ?></td>
                                <td>
                                    <span class="badge badge-<?= $t['jenis_transaksi'] === 'Masuk' ? 'success' : 'danger' ?>">
                                        <?= $t['jenis_transaksi'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($t['kategori']) ?></td>
                                <td><?= htmlspecialchars($t['keterangan']) ?></td>
                                <td
                                    class="text-right font-semibold <?= $t['jenis_transaksi'] === 'Masuk' ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= formatRupiah($t['nominal']) ?>
                                </td>
                                <td class="text-right font-bold text-blue-600"><?= formatRupiah($t['saldo']) ?></td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='openModalEdit(<?= json_encode($t) ?>)'
                                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteItem(<?= $t['id'] ?>)"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-cash-register"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada transaksi</p>
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
<div id="modalForm" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Tambah Transaksi Kas</h3>
            <button onclick="closeModal('modalForm')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formKas">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="formId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal *</label>
                    <input type="date" name="tanggal" id="inputTanggal" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Transaksi *</label>
                    <select name="jenis_transaksi" id="inputJenis" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        <option value="">Pilih...</option>
                        <option value="Masuk">Kas Masuk</option>
                        <option value="Keluar">Kas Keluar</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori *</label>
                    <select name="kategori" id="inputKategori" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        <option value="">Pilih...</option>
                        <option value="SPP">SPP</option>
                        <option value="Dana BOS">Dana BOS</option>
                        <option value="Donasi">Donasi</option>
                        <option value="Gaji">Gaji Pegawai</option>
                        <option value="Belanja">Belanja Operasional</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nominal (Rp) *</label>
                    <input type="number" name="nominal" id="inputNominal" required min="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan *</label>
                    <textarea name="keterangan" id="inputKeterangan" required rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"
                        placeholder="Keterangan detail transaksi..."></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalForm')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden Form for Delete -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

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
        document.getElementById('modalTitle').innerText = 'Tambah Transaksi Kas';
        document.getElementById('formAction').value = 'create';
        document.getElementById('formId').value = '';
        document.getElementById('formKas').reset();
        document.getElementById('inputTanggal').value = '<?= date('Y-m-d') ?>';
        openModal('modalForm');
    }

    function openModalEdit(item) {
        document.getElementById('modalTitle').innerText = 'Edit Transaksi Kas';
        document.getElementById('formAction').value = 'update';
        document.getElementById('formId').value = item.id;

        document.getElementById('inputTanggal').value = item.tanggal;
        document.getElementById('inputJenis').value = item.jenis_transaksi;
        document.getElementById('inputKategori').value = item.kategori;
        document.getElementById('inputNominal').value = item.nominal;
        document.getElementById('inputKeterangan').value = item.keterangan;

        openModal('modalForm');
    }

    function deleteItem(id) {
        if (confirm('Apakah Anda yakin ingin menghapus transaksi ini? Saldo akan dikalkulasi ulang.')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
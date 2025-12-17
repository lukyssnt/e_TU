<?php
$pageTitle = 'Unit Kesehatan Sekolah - Layanan Khusus';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/UKS.php';
require_once __DIR__ . '/../../classes/Siswa.php';

checkPermission('layanan');

$uks = new UKS();
$siswa = new Siswa();

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'obat';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_obat':
                $result = $uks->createObat([
                    'nama_obat' => $_POST['nama_obat'],
                    'jenis' => $_POST['jenis'],
                    'stok' => $_POST['stok'],
                    'satuan' => $_POST['satuan'],
                    'keterangan' => $_POST['keterangan']
                ]);
                if ($result) {
                    $message = 'Data obat berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan data obat!';
                    $messageType = 'error';
                }
                $activeTab = 'obat';
                break;

            case 'update_obat':
                $result = $uks->updateObat($_POST['id'], [
                    'nama_obat' => $_POST['nama_obat'],
                    'jenis' => $_POST['jenis'],
                    'stok' => $_POST['stok'],
                    'satuan' => $_POST['satuan'],
                    'keterangan' => $_POST['keterangan']
                ]);
                if ($result) {
                    $message = 'Data obat berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui data obat!';
                    $messageType = 'error';
                }
                $activeTab = 'obat';
                break;

            case 'delete_obat':
                if ($uks->deleteObat($_POST['id'])) {
                    $message = 'Data obat berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus data obat!';
                    $messageType = 'error';
                }
                $activeTab = 'obat';
                break;

            case 'create_catatan':
                $result = $uks->createCatatan([
                    'siswa_id' => $_POST['siswa_id'],
                    'tanggal_periksa' => $_POST['tanggal_periksa'],
                    'keluhan' => $_POST['keluhan'],
                    'diagnosa' => $_POST['diagnosa'],
                    'tindakan' => $_POST['tindakan'],
                    'obat_diberikan' => $_POST['obat_diberikan'],
                    'obat_id' => $_POST['obat_id'],
                    'jumlah_obat' => $_POST['jumlah_obat']
                ]);
                if ($result) {
                    $message = 'Catatan kesehatan berhasil disimpan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menyimpan catatan kesehatan!';
                    $messageType = 'error';
                }
                $activeTab = 'catatan';
                break;
        }
    }
}

// Get data
$obatList = $uks->getAllObat();
$catatanList = $uks->getAllCatatan();
$siswaList = $siswa->getAll();
$stats = $uks->getStats();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/layanan/index.php" class="hover:text-blue-600">Layanan Khusus</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">UKS</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-medkit text-white text-xl"></i>
                    </div>
                    Unit Kesehatan Sekolah
                </h2>
                <p class="text-gray-600 mt-2">Manajemen obat dan catatan kesehatan siswa</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modalAddObat')"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold shadow hover:bg-red-700">
                    <i class="fas fa-plus mr-2"></i>Tambah Obat
                </button>
                <button onclick="openModal('modalPeriksa')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700">
                    <i class="fas fa-user-md mr-2"></i>Periksa Siswa
                </button>
            </div>
        </div>
    </div>
    </div>

    <!-- Usage Tip -->
    <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-bold text-red-800">Tips Penggunaan UKS:</h3>
                <div class="mt-2 text-sm text-red-700 list-disc pl-5 space-y-1">
                    <li>Gunakan tombol <strong>Periksa Siswa</strong> untuk mencatat setiap kunjungan siswa sakit.</li>
                    <li>Jika memberikan obat, pilih nama obat di form pemeriksaan agar <strong>Stok Obat</strong>
                        berkurang secara otomatis.</li>
                    <li>Pastikan stok obat selalu diperbarui manual jika ada pembelian baru melalui tombol "Tambah
                        Obat".</li>
                </div>
            </div>
        </div>
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
                    <p class="text-gray-500 text-sm">Total Jenis Obat</p>
                    <p class="text-3xl font-bold text-red-600"><?= $stats['total_obat'] ?></p>
                </div>
                <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-pills text-red-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pasien Bulan Ini</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['pasien_bulan_ini'] ?></p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-injured text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent"
            role="tablist">
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 <?= $activeTab === 'obat' ? 'border-red-600 text-red-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' ?>"
                    id="obat-tab" data-tabs-target="#obat" type="button" role="tab" aria-controls="obat"
                    aria-selected="<?= $activeTab === 'obat' ? 'true' : 'false' ?>" onclick="switchTab('obat')">
                    <i class="fas fa-pills mr-2"></i>Stok Obat
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 <?= $activeTab === 'catatan' ? 'border-red-600 text-red-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' ?>"
                    id="catatan-tab" data-tabs-target="#catatan" type="button" role="tab" aria-controls="catatan"
                    aria-selected="<?= $activeTab === 'catatan' ? 'true' : 'false' ?>" onclick="switchTab('catatan')">
                    <i class="fas fa-notes-medical mr-2"></i>Catatan Kesehatan
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div id="myTabContent">
        <!-- Tab Obat -->
        <div class="<?= $activeTab === 'obat' ? '' : 'hidden' ?>" id="obat" role="tabpanel" aria-labelledby="obat-tab">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Obat</h3>
                    <input type="text" id="searchObat" placeholder="Cari obat..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-64">
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table w-full" id="tableObat">
                        <thead>
                            <tr>
                                <th class="text-left">Nama Obat</th>
                                <th class="text-left">Jenis</th>
                                <th class="text-center">Stok</th>
                                <th class="text-left">Satuan</th>
                                <th class="text-left">Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($obatList as $o): ?>
                                <tr>
                                    <td class="font-semibold"><?= htmlspecialchars($o['nama_obat']) ?></td>
                                    <td><?= htmlspecialchars($o['jenis']) ?></td>
                                    <td class="text-center">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-bold <?= $o['stok'] > 5 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                            <?= $o['stok'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($o['satuan']) ?></td>
                                    <td><?= htmlspecialchars($o['keterangan']) ?></td>
                                    <td class="text-center">
                                        <button onclick='editObat(<?= json_encode($o) ?>)'
                                            class="px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteObat(<?= $o['id'] ?>)"
                                            class="px-2 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Catatan -->
        <div class="<?= $activeTab === 'catatan' ? '' : 'hidden' ?>" id="catatan" role="tabpanel"
            aria-labelledby="catatan-tab">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Riwayat Pemeriksaan</h3>
                    <input type="text" id="searchCatatan" placeholder="Cari siswa..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-64">
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table w-full" id="tableCatatan">
                        <thead>
                            <tr>
                                <th class="text-left">Tanggal</th>
                                <th class="text-left">Siswa</th>
                                <th class="text-left">Keluhan</th>
                                <th class="text-left">Diagnosa</th>
                                <th class="text-left">Tindakan/Obat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($catatanList as $c): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($c['tanggal_periksa'])) ?></td>
                                    <td>
                                        <div class="font-semibold"><?= htmlspecialchars($c['nama_siswa']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($c['nisn']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($c['keluhan']) ?></td>
                                    <td><?= htmlspecialchars($c['diagnosa']) ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($c['tindakan']) ?></div>
                                        <?php if ($c['obat_diberikan']): ?>
                                            <div class="text-xs text-red-600 mt-1">
                                                <i class="fas fa-pills mr-1"></i><?= htmlspecialchars($c['obat_diberikan']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Add Obat -->
<div id="modalAddObat" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Tambah Obat Baru</h3>
            <button onclick="closeModal('modalAddObat')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_obat">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Obat</label>
                    <input type="text" name="nama_obat" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Jenis</label>
                    <select name="jenis" class="w-full border rounded px-3 py-2">
                        <option value="Tablet">Tablet</option>
                        <option value="Sirup">Sirup</option>
                        <option value="Kapsul">Kapsul</option>
                        <option value="Salep">Salep</option>
                        <option value="Alat">Alat Kesehatan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok</label>
                        <input type="number" name="stok" required min="0" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Satuan</label>
                        <input type="text" name="satuan" placeholder="pcs, botol, strip" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddObat')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Obat -->
<div id="modalEditObat" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Edit Obat</h3>
            <button onclick="closeModal('modalEditObat')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_obat">
            <input type="hidden" name="id" id="edit_id">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Obat</label>
                    <input type="text" name="nama_obat" id="edit_nama_obat" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Jenis</label>
                    <select name="jenis" id="edit_jenis" class="w-full border rounded px-3 py-2">
                        <option value="Tablet">Tablet</option>
                        <option value="Sirup">Sirup</option>
                        <option value="Kapsul">Kapsul</option>
                        <option value="Salep">Salep</option>
                        <option value="Alat">Alat Kesehatan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Stok</label>
                        <input type="number" name="stok" id="edit_stok" required min="0"
                            class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Satuan</label>
                        <input type="text" name="satuan" id="edit_satuan" required
                            class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" rows="2"
                        class="w-full border rounded px-3 py-2"></textarea>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditObat')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Periksa -->
<div id="modalPeriksa" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Pemeriksaan Siswa</h3>
            <button onclick="closeModal('modalPeriksa')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_catatan">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Siswa</label>
                    <select name="siswa_id" required class="w-full border rounded px-3 py-2 select2">
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswaList as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap']) ?> (<?= $s['nisn'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tanggal Periksa</label>
                    <input type="date" name="tanggal_periksa" value="<?= date('Y-m-d') ?>" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Keluhan</label>
                    <textarea name="keluhan" required rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Diagnosa</label>
                    <input type="text" name="diagnosa" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold mb-1">Tindakan</label>
                    <textarea name="tindakan" required rows="2" class="w-full border rounded px-3 py-2"></textarea>
                </div>

                <div class="col-span-2 border-t pt-4 mt-2">
                    <h4 class="font-semibold text-gray-700 mb-2">Pemberian Obat (Opsional)</h4>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Pilih Obat</label>
                    <select name="obat_id" class="w-full border rounded px-3 py-2 select2"
                        onchange="updateNamaObat(this)">
                        <option value="">-- Tidak Ada --</option>
                        <?php foreach ($obatList as $o): ?>
                            <?php if ($o['stok'] > 0): ?>
                                <option value="<?= $o['id'] ?>" data-nama="<?= htmlspecialchars($o['nama_obat']) ?>">
                                    <?= htmlspecialchars($o['nama_obat']) ?> (Stok: <?= $o['stok'] ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="obat_diberikan" id="input_nama_obat">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Jumlah</label>
                    <input type="number" name="jumlah_obat" min="1" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalPeriksa')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete -->
<form method="POST" id="formDelete">
    <input type="hidden" name="action" value="delete_obat">
    <input type="hidden" name="id" id="delete_id">
</form>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function switchTab(tab) {
        document.querySelectorAll('[role="tabpanel"]').forEach(el => el.classList.add('hidden'));
        document.getElementById(tab).classList.remove('hidden');

        document.querySelectorAll('[role="tab"]').forEach(el => {
            el.classList.remove('border-red-600', 'text-red-600');
            el.classList.add('border-transparent');
        });
        document.getElementById(tab + '-tab').classList.add('border-red-600', 'text-red-600');
        document.getElementById(tab + '-tab').classList.remove('border-transparent');

        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);
    }

    function editObat(o) {
        document.getElementById('edit_id').value = o.id;
        document.getElementById('edit_nama_obat').value = o.nama_obat;
        document.getElementById('edit_jenis').value = o.jenis;
        document.getElementById('edit_stok').value = o.stok;
        document.getElementById('edit_satuan').value = o.satuan;
        document.getElementById('edit_keterangan').value = o.keterangan || '';
        openModal('modalEditObat');
    }

    function deleteObat(id) {
        if (confirm('Yakin ingin menghapus data obat ini?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('formDelete').submit();
        }
    }

    function updateNamaObat(select) {
        const option = select.options[select.selectedIndex];
        const nama = option.getAttribute('data-nama');
        document.getElementById('input_nama_obat').value = nama || '';

        // Auto-set quantity to 1 if empty
        const jumlahInput = document.querySelector('input[name="jumlah_obat"]');
        if (select.value && !jumlahInput.value) {
            jumlahInput.value = 1;
        }
    }

    // Search functionality
    document.getElementById('searchObat')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#tableObat tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    document.getElementById('searchCatatan')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#tableCatatan tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
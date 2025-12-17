<?php
$pageTitle = 'Pembayaran Siswa - Keuangan';

// 1. Dependencies & Auth (Logic only)
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Pembayaran.php';
require_once __DIR__ . '/../../classes/Siswa.php';
require_once __DIR__ . '/../../classes/Kelas.php';

// Check Auth & Permission
if (!Session::isLoggedIn()) {
    header('Location: /e-TU/login.php');
    exit;
}
checkPermission('keuangan');

// Init Objects
$pembayaran = new Pembayaran();
$siswa = new Siswa();
$kelas = new Kelas();

// 2. Handle AJAX Requests (BEFORE ANY HTML OUTPUT)
if (isset($_GET['action']) && $_GET['action'] === 'get_history') {
    // Clear buffer if any
    if (ob_get_length())
        ob_clean();

    header('Content-Type: application/json');
    try {
        if (!isset($_GET['id']))
            throw new Exception("ID required");
        $history = $pembayaran->getRiwayatPembayaran($_GET['id']);
        echo json_encode(['success' => true, 'data' => $history]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 3. Page Logic (POST Handlers & Data Fetching)
$message = '';
$messageType = 'success';

// Handle Actions (POST)
// ... existing POST logic is fine here ...

// 4. Output HTML
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// ... rest of the file ...


// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_single':
                    $data = [
                        'siswa_id' => $_POST['siswa_id'],
                        'judul_tagihan' => $_POST['judul_tagihan'],
                        'total_tagihan' => str_replace(['Rp', '.', ' '], '', $_POST['total_tagihan']),
                        'terbayar' => str_replace(['Rp', '.', ' '], '', $_POST['jumlah_terbayar'] ?? '0'),
                        'tanggal_input' => $_POST['tanggal_input'] ?? date('Y-m-d'),
                        'keterangan' => $_POST['keterangan']
                    ];
                    if ($pembayaran->createTagihan($data)) {
                        $message = 'Tagihan berhasil dibuat!';
                    } else {
                        throw new Exception('Gagal membuat tagihan.');
                    }
                    break;

                case 'create_bulk':
                    $total = str_replace(['Rp', '.', ' '], '', $_POST['total_tagihan']);
                    $terbayar = str_replace(['Rp', '.', ' '], '', $_POST['jumlah_terbayar'] ?? '0');
                    $tanggalInput = $_POST['tanggal_input'] ?? date('Y-m-d');
                    $count = $pembayaran->createTagihanBulk($_POST['kelas_id'], $_POST['judul_tagihan'], $total, $_POST['keterangan'], $terbayar, $tanggalInput);
                    if ($count !== false) {
                        $message = "Tagihan berhasil dibuat untuk $count siswa!";
                    } else {
                        throw new Exception('Gagal membuat tagihan massal.');
                    }
                    break;

                case 'bayar':
                    $jumlah = str_replace(['Rp', '.', ' '], '', $_POST['jumlah_bayar']);
                    $pembayaran->addPembayaran($_POST['tagihan_id'], $jumlah, $_POST['tanggal_bayar'], $_POST['keterangan']);
                    $message = 'Pembayaran berhasil dicatat!';
                    break;

                case 'delete':
                    if ($pembayaran->deleteTagihan($_POST['id'])) {
                        $message = 'Tagihan berhasil dihapus!';
                    } else {
                        throw new Exception('Gagal menghapus tagihan.');
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Data Fetching
$filterKelas = $_GET['kelas'] ?? null;
$listTagihan = $pembayaran->getAllTagihan($filterKelas);
$listKelas = $kelas->getAll();
$listSiswa = $siswa->getAll(); // For single add
?>

<main class="lg:ml-72 min-h-screen p-6">
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/keuangan/index.php" class="hover:text-blue-600">Keuangan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Pembayaran Siswa</span>
        </nav>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                    </div>
                    Pembayaran Siswa
                </h2>
                <p class="text-gray-600 mt-2">Kelola tagihan dan pembayaran siswa (SPP, dll)</p>

                <!-- Tips -->
                <div class="mt-4 p-3 bg-blue-50 border-l-4 border-blue-500 rounded text-sm text-blue-800">
                    <strong><i class="fas fa-info-circle mr-1"></i> Info Penting:</strong>
                    <ul class="list-disc ml-4 mt-1 space-y-1">
                        <li><strong>Tanggal Input</strong> = Menentukan periode Laporan Keuangan (Buku Kas). Gunakan
                            tanggal mundur (backdate) jika ingin mencatat tagihan untuk bulan lalu.</li>
                        <li><strong>Tanggal Bayar</strong> = Waktu uang diterima fisik.</li>
                        <li>Wali murid akan melihat <strong>Tanggal Input</strong> sebagai tanggal resmi tagihan terbit.
                        </li>
                    </ul>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modalAddSingle')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 shadow-md transition">
                    <i class="fas fa-user-plus mr-2"></i>Tagihan Personal
                </button>
                <button onclick="openModal('modalAddBulk')"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 shadow-md transition">
                    <i class="fas fa-users mr-2"></i>Tagihan Per Kelas
                </button>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?> flex items-center shadow-sm animate-fade-in">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3 text-xl"></i>
            <span class="font-medium"><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <label class="font-semibold text-gray-700">Filter Kelas:</label>
                <select id="filterKelas" onchange="applyFilter()"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 bg-gray-50">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $filterKelas == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Cari Nama/NISN/Tagihan..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64 focus:ring-2 focus:ring-emerald-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="dataTable">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3 rounded-tl-lg">Siswa</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Judul Tagihan</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Dibayar</th>
                        <th class="px-4 py-3 text-right">Sisa (Kekurangan)</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 rounded-tr-lg text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($listTagihan)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                <p>Belum ada data tagihan.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($listTagihan as $t): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-bold text-gray-800"><?= htmlspecialchars($t['nama_lengkap']) ?></p>
                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($t['nisn']) ?></p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="px-2 py-1 bg-gray-100 rounded text-xs font-semibold text-gray-600"><?= htmlspecialchars($t['nama_kelas']) ?></span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-700"><?= htmlspecialchars($t['judul_tagihan']) ?>
                                </td>
                                <td class="px-4 py-3 text-right font-mono"><?= formatRupiah($t['total_tagihan']) ?></td>
                                <td class="px-4 py-3 text-right font-mono text-green-600"><?= formatRupiah($t['terbayar']) ?>
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-mono font-bold <?= $t['sisa_tagihan'] > 0 ? 'text-red-500' : 'text-gray-400' ?>">
                                    <?= formatRupiah($t['sisa_tagihan']) ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($t['status'] === 'Lunas'): ?>
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold"><i
                                                class="fas fa-check mr-1"></i>Lunas</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">Belum
                                            Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center flex justify-center gap-2">
                                    <?php if ($t['sisa_tagihan'] > 0): ?>
                                        <button onclick='openBayarModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)'
                                            class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200 flex items-center justify-center transition"
                                            title="Bayar">
                                            <i class="fas fa-coins"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="viewHistory(<?= $t['id'] ?>)"
                                        class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-200 flex items-center justify-center transition"
                                        title="Riwayat">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button onclick="deleteTagihan(<?= $t['id'] ?>)"
                                        class="w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 flex items-center justify-center transition"
                                        title="Hapus">
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

<!-- Modal Add Single -->
<div id="modalAddSingle" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-xl font-bold text-gray-800">Buat Tagihan Personal</h3>
            <button onclick="closeModal('modalAddSingle')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_single">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Siswa</label>
                    <select name="siswa_id" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Cari Siswa --</option>
                        <?php foreach ($listSiswa as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap']) ?> -
                                <?= htmlspecialchars($s['nama_kelas']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Input</label>
                    <input type="date" name="tanggal_input" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">*Tanggal pencatatan tagihan oleh bendahara</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Tagihan</label>
                    <input type="text" name="judul_tagihan" required placeholder="Contoh: SPP Januari 2024"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Total Tagihan (Rp)</label>
                    <input type="text" name="total_tagihan" required onkeyup="formatRupiahInput(this)"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sudah Dibayar (Opsional)</label>
                    <input type="text" name="jumlah_terbayar" onkeyup="formatRupiahInput(this)" placeholder="0"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 font-mono">
                    <p class="text-xs text-gray-500 mt-1">*Isi jika siswa sudah membayar sebagian/lunas</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalAddSingle')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Bulk -->
<div id="modalAddBulk" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-xl font-bold text-gray-800">Buat Tagihan Per Kelas</h3>
            <button onclick="closeModal('modalAddBulk')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_bulk">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Kelas</label>
                    <select name="kelas_id" required
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">-- Cari Kelas --</option>
                        <?php foreach ($listKelas as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Input</label>
                    <input type="date" name="tanggal_input" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">*Tanggal pencatatan tagihan oleh bendahara</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Tagihan</label>
                    <input type="text" name="judul_tagihan" required placeholder="Contoh: Uang Gedung 2024"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Total Tagihan (Rp)</label>
                    <input type="text" name="total_tagihan" required onkeyup="formatRupiahInput(this)"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sudah Dibayar (Opsional)</label>
                    <input type="text" name="jumlah_terbayar" onkeyup="formatRupiahInput(this)" placeholder="0"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 font-mono">
                    <p class="text-xs text-gray-500 mt-1">*Isi jika sudah ada pembayaran awal</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan (Opsional)</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalAddBulk')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold">Generate</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Bayar -->
<div id="modalBayar" class="modal-overlay">
    <div class="modal-content max-w-md">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-xl font-bold text-gray-800">Input Pembayaran</h3>
            <button onclick="closeModal('modalBayar')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="bayar">
            <input type="hidden" name="tagihan_id" id="bayar_tagihan_id">

            <div class="mb-4 p-4 bg-gray-50 rounded-lg border">
                <p class="text-sm text-gray-600">Tagihan:</p>
                <p class="font-bold text-gray-800" id="bayar_judul"></p>
                <p class="text-sm text-gray-600 mt-2">Sisa Kekurangan:</p>
                <p class="font-bold text-red-600 text-lg" id="bayar_sisa"></p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Bayar (Rp)</label>
                    <input type="text" name="jumlah_bayar" id="bayar_jumlah" required onkeyup="formatRupiahInput(this)"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 font-mono text-lg font-bold">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Metode transfer / tunai"
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalBayar')"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-semibold">Bayar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal History -->
<div id="modalHistory" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex justify-between items-center mb-5 border-b pb-3">
            <h3 class="text-xl font-bold text-gray-800">Riwayat Pembayaran</h3>
            <button onclick="closeModal('modalHistory')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>

        <div id="historyLoading" class="text-center py-8">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-500"></i>
            <p class="mt-2 text-gray-500">Memuat data...</p>
        </div>

        <div id="historyContent" class="hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="historyTableBody" class="divide-y divide-gray-100 text-sm">
                        <!-- Data inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeModal('modalHistory')"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Tutup</button>
        </div>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function applyFilter() {
        const kelas = document.getElementById('filterKelas').value;
        const url = new URL(window.location.href);
        if (kelas) url.searchParams.set('kelas', kelas);
        else url.searchParams.delete('kelas');
        window.location.href = url.toString();
    }

    // Search
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    function openBayarModal(tagihan) {
        document.getElementById('bayar_tagihan_id').value = tagihan.id;
        document.getElementById('bayar_judul').textContent = tagihan.judul_tagihan;

        // Format rupiah helper
        const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });
        document.getElementById('bayar_sisa').textContent = formatter.format(tagihan.sisa_tagihan);

        // Auto fill max amount
        // document.getElementById('bayar_jumlah').value = formatter.format(tagihan.sisa_tagihan).replace('Rp', '').trim(); // Optional: Autofill

        openModal('modalBayar');
    }

    function formatRupiahInput(input) {
        let value = input.value.replace(/[^0-9]/g, '');
        if (value) {
            input.value = new Intl.NumberFormat('id-ID').format(value);
        } else {
            input.value = '';
        }
    }

    function deleteTagihan(id) {
        if (confirm('Hapus tagihan ini? Data pembayaran terkait juga akan terhapus.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function viewHistory(id) {
        openModal('modalHistory');
        const loading = document.getElementById('historyLoading');
        const content = document.getElementById('historyContent');
        const tbody = document.getElementById('historyTableBody');

        loading.classList.remove('hidden');
        content.classList.add('hidden');
        tbody.innerHTML = '';

        fetch('?action=get_history&id=' + id)
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');

                if (data.success && data.data.length > 0) {
                    content.classList.remove('hidden');
                    const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 });

                    data.data.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-gray-50';
                        tr.innerHTML = `
                            <td class="px-4 py-3">${item.tanggal_bayar}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-green-600">${formatter.format(item.jumlah_bayar)}</td>
                            <td class="px-4 py-3 text-gray-600">${item.keterangan || '-'}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="/e-TU/modules/keuangan/kuitansi.php?id=${item.id}" target="_blank" 
                                   class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold hover:bg-indigo-200 transition">
                                    <i class="fas fa-print mr-1"></i> Kuitansi
                                </a>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    content.classList.remove('hidden');
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">Belum ada riwayat pembayaran.</td></tr>`;
                }
            })
            .catch(err => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-red-500">Gagal memuat data.</td></tr>`;
                console.error(err);
            });
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
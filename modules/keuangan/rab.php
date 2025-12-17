<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

checkPermission('keuangan');

$db = Database::getInstance()->getConnection();

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $tahun = isset($_GET['tahun']) ? (int) $_GET['tahun'] : date('Y');
    $kategori = isset($_GET['kategori']) ? clean($_GET['kategori']) : '';
    $status = isset($_GET['status']) ? clean($_GET['status']) : '';

    // Build query
    $query = "SELECT * FROM rab WHERE tahun = ?";
    $params = [$tahun];

    if (!empty($kategori)) {
        $query .= " AND kategori = ?";
        $params[] = $kategori;
    }

    if (!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
    }

    $query .= " ORDER BY kode ASC";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="RAB_Tahun_' . $tahun . '_' . date('Y-m-d') . '.csv"');

    // Clean output buffer
    if (ob_get_level())
        ob_end_clean();

    $output = fopen('php://output', 'w');
    
    // Add separator hint for Excel (tells Excel to use semicolon as delimiter)
    fwrite($output, "sep=;\n");
    
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

    // Header
    fputcsv($output, ['No', 'Kode', 'Uraian Kegiatan', 'Kategori', 'Volume', 'Satuan', 'Harga Satuan', 'Jumlah', 'Tahun', 'Status', 'Keterangan'], ';', '"', '\\');

    $no = 1;
    foreach ($data as $row) {
        fputcsv($output, [
            $no++,
            $row['kode'],
            $row['uraian'],
            $row['kategori'],
            $row['volume'],
            $row['satuan'],
            $row['harga_satuan'],
            $row['jumlah'],
            $row['tahun'],
            $row['status'],
            $row['keterangan'] ?? ''
        ], ';', '"', '\\');
    }

    fclose($output);
    exit;
}

// Handle Form Submission BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        try {
            $stmt = $db->prepare("DELETE FROM rab WHERE id = ?");
            $stmt->execute([$id]);
            logActivity('DELETE', 'Keuangan', "Menghapus item RAB ID: $id");
            Session::setFlash('success', 'Data RAB berhasil dihapus!');
        } catch (PDOException $e) {
            Session::setFlash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
        header('Location: rab.php');
        exit;
    }

    // Common fields for Create/Update
    $kode = clean($_POST['kode']);
    $uraian = clean($_POST['uraian']);
    $kategori = clean($_POST['kategori']);
    $volume = (int) $_POST['volume'];
    $satuan = clean($_POST['satuan']);
    $harga_satuan = (float) $_POST['harga_satuan'];
    $jumlah = $volume * $harga_satuan;
    $tahun = (int) $_POST['tahun'];
    $status = clean($_POST['status']);
    $keterangan = clean($_POST['keterangan']);

    if ($action === 'create') {
        try {
            $stmt = $db->prepare("INSERT INTO rab (kode, uraian, kategori, volume, satuan, harga_satuan, jumlah, tahun, status, keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$kode, $uraian, $kategori, $volume, $satuan, $harga_satuan, $jumlah, $tahun, $status, $keterangan]);

            logActivity('CREATE', 'Keuangan', "Menambahkan item RAB: $kode");
            Session::setFlash('success', 'Data RAB berhasil ditambahkan!');
        } catch (PDOException $e) {
            Session::setFlash('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        try {
            $stmt = $db->prepare("UPDATE rab SET kode=?, uraian=?, kategori=?, volume=?, satuan=?, harga_satuan=?, jumlah=?, tahun=?, status=?, keterangan=? WHERE id=?");
            $stmt->execute([$kode, $uraian, $kategori, $volume, $satuan, $harga_satuan, $jumlah, $tahun, $status, $keterangan, $id]);

            logActivity('UPDATE', 'Keuangan', "Mengupdate item RAB: $kode");
            Session::setFlash('success', 'Data RAB berhasil diperbarui!');
        } catch (PDOException $e) {
            Session::setFlash('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    header('Location: rab.php');
    exit;
}

// NOW safe to include header and output HTML
$pageTitle = 'RAB (Rencana Anggaran Biaya) - Keuangan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

// Filter Logic
$tahunFilter = isset($_GET['tahun']) ? (int) $_GET['tahun'] : date('Y');
$kategoriFilter = isset($_GET['kategori']) ? clean($_GET['kategori']) : '';
$statusFilter = isset($_GET['status']) ? clean($_GET['status']) : '';

// Build Query
$query = "SELECT * FROM rab WHERE tahun = ?";
$params = [$tahunFilter];

if (!empty($kategoriFilter)) {
    $query .= " AND kategori = ?";
    $params[] = $kategoriFilter;
}

if (!empty($statusFilter)) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$rabItems = $stmt->fetchAll();

// Calculate Summaries
$totalAnggaran = 0;
$terealisasi = 0;

foreach ($rabItems as $item) {
    $totalAnggaran += $item['jumlah'];
    if ($item['status'] === 'Terealisasi') {
        $terealisasi += $item['jumlah'];
    }
}

$sisaAnggaran = $totalAnggaran - $terealisasi;
$persentase = $totalAnggaran > 0 ? round(($terealisasi / $totalAnggaran) * 100, 1) : 0;
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/keuangan/index.php" class="hover:text-blue-600">Keuangan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">RAB</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-file-invoice-dollar text-white text-xl"></i>
                    </div>
                    RAB (Rencana Anggaran Biaya)
                </h2>
                <p class="text-gray-600 mt-2">Kelola anggaran kegiatan sekolah</p>
            </div>
            <button onclick="openModalAdd()"
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Item RAB
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Anggaran</label>
                <select name="tahun"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <?php for ($i = date('Y') - 1; $i <= date('Y') + 2; $i++): ?>
                        <option value="<?= $i ?>" <?= $tahunFilter == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                <select name="kategori"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua Kategori</option>
                    <option value="Operasional" <?= $kategoriFilter == 'Operasional' ? 'selected' : '' ?>>Operasional
                    </option>
                    <option value="Pembangunan" <?= $kategoriFilter == 'Pembangunan' ? 'selected' : '' ?>>Pembangunan
                    </option>
                    <option value="Kegiatan" <?= $kategoriFilter == 'Kegiatan' ? 'selected' : '' ?>>Kegiatan</option>
                    <option value="Belanja" <?= $kategoriFilter == 'Belanja' ? 'selected' : '' ?>>Belanja</option>
                    <option value="Gaji" <?= $kategoriFilter == 'Gaji' ? 'selected' : '' ?>>Gaji</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="">Semua</option>
                    <option value="Draft" <?= $statusFilter == 'Draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="Disetujui" <?= $statusFilter == 'Disetujui' ? 'selected' : '' ?>>Disetujui</option>
                    <option value="Terealisasi" <?= $statusFilter == 'Terealisasi' ? 'selected' : '' ?>>Terealisasi
                    </option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Total Anggaran</p>
            <p class="text-2xl font-bold text-purple-600"><?= formatRupiah($totalAnggaran) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Terealisasi</p>
            <p class="text-2xl font-bold text-green-600"><?= formatRupiah($terealisasi) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Sisa Anggaran</p>
            <p class="text-2xl font-bold text-blue-600"><?= formatRupiah($sisaAnggaran) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Persentase</p>
            <p class="text-2xl font-bold text-amber-600"><?= $persentase ?>%</p>
        </div>
    </div>

    <!-- RAB Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Daftar RAB Tahun <?= $tahunFilter ?></h3>
            <div class="flex gap-2">
                <button onclick="openPrintView()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </button>
                <button onclick="openPrintView()"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
                <a href="?action=export_csv&tahun=<?= $tahunFilter ?>&kategori=<?= $kategoriFilter ?>&status=<?= $statusFilter ?>"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm">
                    <i class="fas fa-file-excel mr-2"></i>Export CSV
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="tableRAB" class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Kode</th>
                        <th class="text-left">Uraian Kegiatan</th>
                        <th class="text-left">Kategori</th>
                        <th class="text-center">Volume</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-right">Harga Satuan</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-left">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rabItems) > 0): ?>
                        <?php foreach ($rabItems as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="font-semibold"><?= $item['kode'] ?></td>
                                <td><?= htmlspecialchars($item['uraian']) ?></td>
                                <td>
                                    <span class="text-xs px-2 py-1 bg-gray-100 rounded"><?= $item['kategori'] ?></span>
                                </td>
                                <td class="text-center"><?= $item['volume'] ?></td>
                                <td class="text-center"><?= $item['satuan'] ?></td>
                                <td class="text-right"><?= formatRupiah($item['harga_satuan']) ?></td>
                                <td class="text-right font-bold text-purple-600"><?= formatRupiah($item['jumlah']) ?></td>
                                <td>
                                    <span
                                        class="badge badge-<?= $item['status'] === 'Disetujui' ? 'success' : ($item['status'] === 'Draft' ? 'warning' : 'info') ?>">
                                        <?= $item['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='openModalEdit(<?= json_encode($item) ?>)'
                                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteItem(<?= $item['id'] ?>)"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada data RAB</p>
                                    <p class="text-sm">Klik "Tambah Item RAB" untuk menambah anggaran</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-purple-50 font-bold">
                    <tr>
                        <td colspan="7" class="text-right font-bold text-gray-800">TOTAL ANGGARAN</td>
                        <td class="text-right text-purple-600"><?= formatRupiah($totalAnggaran) ?></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add/Edit -->
<div id="modalForm" class="modal-overlay">
    <div class="modal-content max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <h3 id="modalTitle" class="text-2xl font-bold text-gray-800">Tambah Item RAB</h3>
            <button onclick="closeModal('modalForm')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formRAB">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="formId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kode RAB *</label>
                    <input type="text" name="kode" id="inputKode" required placeholder="Contoh: RAB-2025-001"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori *</label>
                    <select name="kategori" id="inputKategori" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="">Pilih...</option>
                        <option value="Operasional">Operasional Sekolah</option>
                        <option value="Pembangunan">Pembangunan & Renovasi</option>
                        <option value="Kegiatan">Kegiatan & Event</option>
                        <option value="Belanja">Belanja Barang</option>
                        <option value="Gaji">Gaji & Honorarium</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Uraian Kegiatan *</label>
                    <textarea name="uraian" id="inputUraian" required rows="2"
                        placeholder="Jelaskan detail kegiatan/penggunaan anggaran..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Volume *</label>
                    <input type="number" name="volume" id="inputVolume" required min="1" placeholder="Jumlah volume"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Satuan *</label>
                    <input type="text" name="satuan" id="inputSatuan" required
                        placeholder="Contoh: Unit, Paket, Buah, Orang"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Harga Satuan (Rp) *</label>
                    <input type="number" name="harga_satuan" id="inputHarga" required min="0"
                        placeholder="Harga per satuan"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Total (Rp)</label>
                    <input type="number" name="jumlah" id="inputJumlah" readonly
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
                        placeholder="Auto calculate">
                    <p class="text-xs text-gray-500 mt-1">Otomatis: Volume × Harga Satuan</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Anggaran *</label>
                    <input type="number" name="tahun" id="inputTahun" required value="<?= $tahunFilter ?>" min="2020"
                        max="2099"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
                    <select name="status" id="inputStatus" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="Draft">Draft</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Terealisasi">Terealisasi</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan Tambahan</label>
                    <textarea name="keterangan" id="inputKeterangan" rows="2"
                        placeholder="Catatan atau keterangan tambahan (opsional)"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalForm')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-700 text-white rounded-lg font-semibold">
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
    // Auto calculate jumlah
    document.getElementById('inputVolume')?.addEventListener('input', calculateTotal);
    document.getElementById('inputHarga')?.addEventListener('input', calculateTotal);

    function calculateTotal() {
        const volume = parseFloat(document.getElementById('inputVolume').value) || 0;
        const hargaSatuan = parseFloat(document.getElementById('inputHarga').value) || 0;
        const jumlah = volume * hargaSatuan;
        document.getElementById('inputJumlah').value = jumlah;
    }

    function openModalAdd() {
        document.getElementById('modalTitle').innerText = 'Tambah Item RAB';
        document.getElementById('formAction').value = 'create';
        document.getElementById('formId').value = '';
        document.getElementById('formRAB').reset();
        document.getElementById('inputTahun').value = '<?= $tahunFilter ?>';
        openModal('modalForm');
    }

    function openModalEdit(item) {
        document.getElementById('modalTitle').innerText = 'Edit Item RAB';
        document.getElementById('formAction').value = 'update';
        document.getElementById('formId').value = item.id;

        document.getElementById('inputKode').value = item.kode;
        document.getElementById('inputKategori').value = item.kategori;
        document.getElementById('inputUraian').value = item.uraian;
        document.getElementById('inputVolume').value = item.volume;
        document.getElementById('inputSatuan').value = item.satuan;
        document.getElementById('inputHarga').value = item.harga_satuan;
        document.getElementById('inputJumlah').value = item.jumlah;
        document.getElementById('inputTahun').value = item.tahun;
        document.getElementById('inputStatus').value = item.status;
        document.getElementById('inputKeterangan').value = item.keterangan || '';

        openModal('modalForm');
    }

    function deleteItem(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteForm').submit();
        }
    }

    function openPrintView() {
        const urlParams = new URLSearchParams(window.location.search);
        window.open('print_rab.php?' + urlParams.toString(), '_blank');
    }

    function exportToExcel() {
        const table = document.getElementById('tableRAB');
        if (table) {
            exportTableToCSV('tableRAB', 'RAB_Tahun_<?= $tahunFilter ?>.csv');
        } else {
            showToast('Tidak ada data untuk di-export', 'warning');
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
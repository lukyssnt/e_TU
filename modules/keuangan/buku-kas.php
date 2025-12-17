<?php
$pageTitle = 'Buku Kas - Keuangan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/TransaksiKas.php';

checkPermission('keuangan');

$kas = new TransaksiKas();

// Handle filters
$filterTanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filterBulan = $_GET['bulan'] ?? date('Y-m');
$filterTahun = $_GET['tahun'] ?? date('Y');
$filterJenis = $_GET['jenis'] ?? 'semua'; // semua, masuk, keluar
$filterPeriode = $_GET['periode'] ?? 'hari'; // hari, bulan, tahun

// Get data based on filter
if ($filterPeriode === 'hari') {
    $transaksi = $kas->getByPeriod($filterTanggal, $filterTanggal);
    $periodLabel = formatTanggal($filterTanggal, 'long');
} elseif ($filterPeriode === 'bulan') {
    $startDate = $filterBulan . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    $transaksi = $kas->getByPeriod($startDate, $endDate);
    $periodLabel = date('F Y', strtotime($startDate));
} else { // tahun
    $startDate = $filterTahun . '-01-01';
    $endDate = $filterTahun . '-12-31';
    $transaksi = $kas->getByPeriod($startDate, $endDate);
    $periodLabel = 'Tahun ' . $filterTahun;
}

// Filter by jenis
if ($filterJenis !== 'semua') {
    $jenis = $filterJenis === 'masuk' ? 'Masuk' : 'Keluar';
    $transaksi = array_filter($transaksi, fn($t) => $t['jenis_transaksi'] === $jenis);
}

// Calculate totals
$totalMasuk = 0;
$totalKeluar = 0;
foreach ($transaksi as $t) {
    if ($t['jenis_transaksi'] === 'Masuk') {
        $totalMasuk += $t['nominal'];
    } else {
        $totalKeluar += $t['nominal'];
    }
}
$selisih = $totalMasuk - $totalKeluar;
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/keuangan/index.php" class="hover:text-blue-600">Keuangan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Buku Kas</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-book text-white text-xl"></i>
                    </div>
                    Buku Kas
                </h2>
                <p class="text-gray-600 mt-2">Laporan transaksi kas dengan filter</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openPrintView()"
                    class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
                <button onclick="exportToExcel()"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Filter Laporan</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Periode</label>
                <select name="periode" id="filterPeriode" onchange="toggleDateInputs()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="hari" <?= $filterPeriode === 'hari' ? 'selected' : '' ?>>Per Hari</option>
                    <option value="bulan" <?= $filterPeriode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                    <option value="tahun" <?= $filterPeriode === 'tahun' ? 'selected' : '' ?>>Per Tahun</option>
                </select>
            </div>

            <div id="filterHari" style="display: <?= $filterPeriode === 'hari' ? 'block' : 'none' ?>">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
                <input type="date" name="tanggal" value="<?= $filterTanggal ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div id="filterBulan" style="display: <?= $filterPeriode === 'bulan' ? 'block' : 'none' ?>">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                <input type="month" name="bulan" value="<?= $filterBulan ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div id="filterTahun" style="display: <?= $filterPeriode === 'tahun' ? 'block' : 'none' ?>">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <input type="number" name="tahun" value="<?= $filterTahun ?>" min="2020" max="2099"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis</label>
                <select name="jenis"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="semua" <?= $filterJenis === 'semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="masuk" <?= $filterJenis === 'masuk' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="keluar" <?= $filterJenis === 'keluar' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-filter mr-2"></i>Terapkan
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Total Pemasukan</p>
            <p class="text-2xl font-bold text-green-600"><?= formatRupiah($totalMasuk) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Total Pengeluaran</p>
            <p class="text-2xl font-bold text-red-600"><?= formatRupiah($totalKeluar) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Selisih</p>
            <p class="text-2xl font-bold <?= $selisih >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                <?= formatRupiah($selisih) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <p class="text-gray-600 text-sm mb-2">Total Transaksi</p>
            <p class="text-2xl font-bold text-blue-600"><?= count($transaksi) ?></p>
        </div>
    </div>

    <!-- Buku Kas Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800">Buku Kas - <?= $periodLabel ?></h3>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="tableBukuKas">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Keterangan</th>
                        <th class="text-left">Kategori</th>
                        <th class="text-right">Masuk</th>
                        <th class="text-right">Keluar</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transaksi) > 0): ?>
                        <?php foreach ($transaksi as $index => $t): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= formatTanggal($t['tanggal'], 'short') ?></td>
                                <td><?= htmlspecialchars($t['keterangan']) ?></td>
                                <td>
                                    <span
                                        class="text-xs px-2 py-1 bg-gray-100 rounded"><?= htmlspecialchars($t['kategori']) ?></span>
                                </td>
                                <td class="text-right font-semibold text-green-600">
                                    <?= $t['jenis_transaksi'] === 'Masuk' ? formatRupiah($t['nominal']) : '-' ?>
                                </td>
                                <td class="text-right font-semibold text-red-600">
                                    <?= $t['jenis_transaksi'] === 'Keluar' ? formatRupiah($t['nominal']) : '-' ?>
                                </td>
                                <td class="text-right font-bold text-blue-600"><?= formatRupiah($t['saldo']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-gray-50 font-bold">
                            <td colspan="4" class="text-right">TOTAL</td>
                            <td class="text-right text-green-600"><?= formatRupiah($totalMasuk) ?></td>
                            <td class="text-right text-red-600"><?= formatRupiah($totalKeluar) ?></td>
                            <td class="text-right text-blue-600"><?= formatRupiah($selisih) ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Tidak ada transaksi</p>
                                    <p class="text-sm">Pada periode yang dipilih</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function toggleDateInputs() {
        const periode = document.getElementById('filterPeriode').value;
        document.getElementById('filterHari').style.display = periode === 'hari' ? 'block' : 'none';
        document.getElementById('filterBulan').style.display = periode === 'bulan' ? 'block' : 'none';
        document.getElementById('filterTahun').style.display = periode === 'tahun' ? 'block' : 'none';
    }

    function openPrintView() {
        const urlParams = new URLSearchParams(window.location.search);
        window.open('print_buku_kas.php?' + urlParams.toString(), '_blank');
    }

    function exportToExcel() {
        exportTableToCSV('tableBukuKas', 'Buku_Kas_<?= str_replace(' ', '_', $periodLabel) ?>.csv');
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
$pageTitle = 'Keuangan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

checkPermission('keuangan');

require_once __DIR__ . '/../../classes/TransaksiKas.php';
require_once __DIR__ . '/../../includes/functions.php';

$kas = new TransaksiKas();

// Calculate Date Range for "This Month"
$startDate = date('Y-m-01');
$endDate = date('Y-m-t');

$saldoKas = $kas->getSaldoAkhir();
$pemasukanBulanIni = $kas->getTotalPemasukan($startDate, $endDate);
$pengeluaranBulanIni = $kas->getTotalPengeluaran($startDate, $endDate);
$selisih = $pemasukanBulanIni - $pengeluaranBulanIni;
?>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-money-bill-wave text-white text-xl"></i>
            </div>
            Keuangan
        </h2>
        <p class="text-gray-600 mt-2">Kelola keuangan dan laporan kas sekolah</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="/e-TU/modules/keuangan/kas.php" class="block group">
            <div
                class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-cash-register text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Kas Masuk/Keluar</h3>
                <p class="text-emerald-100 text-sm">Input transaksi kas harian</p>
            </div>
        </a>

        <a href="/e-TU/modules/keuangan/buku-kas.php" class="block group">
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-book text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Buku Kas</h3>
                <p class="text-blue-100 text-sm">Laporan buku kas</p>
            </div>
        </a>

        <a href="/e-TU/modules/keuangan/rab.php" class="block group">
            <div
                class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-file-invoice-dollar text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">RAB</h3>
                <p class="text-purple-100 text-sm">Rencana Anggaran Biaya</p>
            </div>
        </a>

        <a href="/e-TU/modules/keuangan/laporan.php" class="block group">
            <div
                class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-chart-pie text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Laporan Keuangan</h3>
                <p class="text-amber-100 text-sm">Laporan & analisis</p>
            </div>
        </a>

        <a href="/e-TU/modules/keuangan/pembayaran.php" class="block group">
            <div
                class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-user-tag text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Pembayaran Siswa</h3>
                <p class="text-indigo-100 text-sm">SPP & administrasi siswa</p>
                <span
                    class="inline-block mt-3 px-3 py-1 bg-white/20 text-white text-xs font-bold rounded-full">NEW</span>
            </div>
        </a>

    </div>

    <!-- Stats Overview -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Saldo Kas</p>
                    <p class="text-2xl font-bold text-blue-600"><?= formatRupiah($saldoKas) ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pemasukan Bulan Ini</p>
                    <p class="text-2xl font-bold text-green-600"><?= formatRupiah($pemasukanBulanIni) ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pengeluaran Bulan Ini</p>
                    <p class="text-2xl font-bold text-red-600"><?= formatRupiah($pengeluaranBulanIni) ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Selisih</p>
                    <p class="text-2xl font-bold text-purple-600">
                        <?= ($selisih >= 0 ? '+' : '') . formatRupiah($selisih) ?>
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
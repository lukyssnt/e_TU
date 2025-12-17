<?php
$pageTitle = 'Sarana Prasarana';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Aset.php';
require_once __DIR__ . '/../../includes/functions.php';

checkPermission('sarpras');

$aset = new Aset();
$stats = $aset->getStats();
?>

<main class="lg:ml-72 min-h-screen p-6">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-warehouse text-white text-xl"></i>
            </div>
            Sarana Prasarana
        </h2>
        <p class="text-gray-600 mt-2">Kelola inventaris aset dan sarana prasarana sekolah</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/e-TU/modules/sarpras/aset.php" class="block">
            <div
                class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 text-white hover:shadow-2xl transform hover:-translate-y-2 transition">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-box text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Inventaris Aset</h3>
                <p class="text-amber-100 text-sm">Kelola aset sekolah</p>
            </div>
        </a>

        <a href="/e-TU/modules/sarpras/peminjaman.php" class="block">
            <div
                class="bg-gradient-to-br from-rose-500 to-red-600 rounded-xl p-6 text-white hover:shadow-2xl transform hover:-translate-y-2 transition">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-file-export text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Peminjaman Aset</h3>
                <p class="text-rose-100 text-sm">Tracking peminjaman</p>
            </div>
        </a>

        <a href="/e-TU/modules/sarpras/maintenance.php" class="block">
            <div
                class="bg-gradient-to-br from-gray-500 to-gray-700 rounded-xl p-6 text-white hover:shadow-2xl transform hover:-translate-y-2 transition">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-tools text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Maintenance</h3>
                <p class="text-gray-100 text-sm">Perbaikan & perawatan</p>
            </div>
        </a>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Inventaris</p>
                    <p class="text-3xl font-bold text-amber-600"><?= number_format($stats['total_items']) ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-boxes text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Nilai Aset</p>
                    <p class="text-2xl font-bold text-green-600">Rp
                        <?= number_format($stats['total_value'], 0, ',', '.') ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Sedang Dipinjam</p>
                    <p class="text-3xl font-bold text-rose-600"><?= number_format($stats['dipinjam']) ?></p>
                </div>
                <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hand-holding text-rose-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Kondisi Rusak</p>
                    <p class="text-3xl font-bold text-red-600"><?= number_format($stats['rusak']) ?></p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-tools text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
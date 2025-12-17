<?php
$pageTitle = 'Layanan Khusus';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Perpustakaan.php';
require_once __DIR__ . '/../../classes/UKS.php';

checkPermission('layanan_khusus');

$perpustakaan = new Perpustakaan();
$uks = new UKS();

$perpusStats = $perpustakaan->getStats();
$uksStats = $uks->getStats();
?>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Layanan Khusus</span>
        </nav>
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-concierge-bell text-white text-xl"></i>
            </div>
            Layanan Khusus
        </h2>
        <p class="text-gray-600 mt-2">Kelola perpustakaan dan unit kesehatan sekolah</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <a href="/e-TU/modules/layanan/perpustakaan.php" class="block group">
            <div
                class="bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-book text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Perpustakaan</h3>
                <p class="text-indigo-100 text-sm">Peminjaman buku & inventaris</p>
            </div>
        </a>

        <a href="/e-TU/modules/layanan/uks.php" class="block group">
            <div
                class="bg-gradient-to-br from-rose-500 to-red-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-heartbeat text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">UKS</h3>
                <p class="text-rose-100 text-sm">Catatan kesehatan & obat</p>
            </div>
        </a>

    </div>

    <!-- Stats Overview -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Buku</p>
                    <p class="text-3xl font-bold text-indigo-600"><?= $perpusStats['total_buku'] ?></p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Buku Dipinjam</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $perpusStats['dipinjam'] ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-hand-holding text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Stok Obat</p>
                    <p class="text-3xl font-bold text-rose-600"><?= $uksStats['total_obat'] ?></p>
                </div>
                <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-pills text-rose-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Pasien Bulan Ini</p>
                    <p class="text-3xl font-bold text-emerald-600"><?= $uksStats['pasien_bulan_ini'] ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-injured text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
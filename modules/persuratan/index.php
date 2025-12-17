<?php
$pageTitle = 'Persuratan & Kearsipan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Surat.php';

checkPermission('persuratan');

$surat = new Surat();
$stats = $surat->getStats();
?>

<!-- Main Content -->
<main class="lg:ml-72 min-h-screen p-6">

    <!-- Header -->
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-envelope text-white text-xl"></i>
            </div>
            Persuratan & Kearsipan
        </h2>
        <p class="text-gray-600 mt-2">Modul Kunci - Kelola surat menyurat dan arsip digital</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Surat Generator -->
        <a href="/e-TU/modules/persuratan/generator.php" class="block group">
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-3xl"></i>
                    </div>
                    <span class="px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-full">POPULAR</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Surat Generator</h3>
                <p class="text-blue-100 text-sm">Generate surat otomatis dengan template</p>
            </div>
        </a>

        <!-- Surat Masuk -->
        <a href="/e-TU/modules/persuratan/surat-masuk.php" class="block group">
            <div
                class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-inbox text-3xl"></i>
                    </div>
                    <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full">READY</span>
                </div>
                <h3 class="text-xl font-bold mb-2">Surat Masuk</h3>
                <p class="text-emerald-100 text-sm">Kelola surat masuk dan disposisi</p>
            </div>
        </a>

        <!-- Surat Keluar -->
        <a href="/e-TU/modules/persuratan/surat-keluar.php" class="block group">
            <div
                class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-paper-plane text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Surat Keluar</h3>
                <p class="text-amber-100 text-sm">Kelola surat keluar dengan nomor otomatis</p>
            </div>
        </a>

        <!-- Disposisi Digital -->
        <a href="/e-TU/modules/persuratan/disposisi.php" class="block group">
            <div
                class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-share text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Disposisi Digital</h3>
                <p class="text-purple-100 text-sm">Tracking alur disposisi surat masuk</p>
            </div>
        </a>

        <!-- Arsip Digital -->
        <a href="/e-TU/modules/persuratan/arsip.php" class="block group">
            <div
                class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-archive text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Arsip Digital</h3>
                <p class="text-cyan-100 text-sm">Repository dokumen terpusat</p>
            </div>
        </a>

        <!-- Template Surat -->
        <a href="/e-TU/modules/persuratan/template.php" class="block group">
            <div
                class="bg-gradient-to-br from-rose-500 to-red-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-file-code text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Template Surat</h3>
                <p class="text-rose-100 text-sm">Kelola template surat dengan variabel dinamis</p>
            </div>
        </a>

    </div>

    <!-- Stats Overview -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Surat Masuk (Bulan Ini)</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['masuk_bulan_ini'] ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-inbox text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Surat Keluar (Bulan Ini)</p>
                    <p class="text-3xl font-bold text-emerald-600"><?= $stats['keluar_bulan_ini'] ?></p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-paper-plane text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Disposisi Pending</p>
                    <p class="text-3xl font-bold text-amber-600"><?= $stats['disposisi_pending'] ?></p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Arsip</p>
                    <p class="text-3xl font-bold text-purple-600"><?= $stats['total_arsip'] ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-archive text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
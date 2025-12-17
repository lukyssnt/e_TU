<?php
$pageTitle = 'Kepegawaian';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Pegawai.php';

checkPermission('kepegawaian');

$pegawai = new Pegawai();
$stats = $pegawai->getStats();
?>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Kepegawaian</span>
        </nav>
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
            Kepegawaian
        </h2>
        <p class="text-gray-600 mt-2">Manajemen data pegawai, guru, dan staff</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <a href="/e-TU/modules/kepegawaian/pegawai.php" class="block group">
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-user-tie text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Data Pegawai</h3>
                <p class="text-blue-100 text-sm">Kelola data guru dan staff</p>
            </div>
        </a>

        <a href="/e-TU/modules/kepegawaian/cuti.php" class="block group">
            <div
                class="bg-gradient-to-br from-cyan-500 to-teal-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-calendar-minus text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Manajemen Cuti</h3>
                <p class="text-cyan-100 text-sm">Pengajuan dan persetujuan cuti</p>
            </div>
        </a>

    </div>

    <!-- Stats Overview -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Pegawai</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['total'] ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">PNS</p>
                    <p class="text-3xl font-bold text-green-600"><?= $stats['pns'] ?></p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-id-card text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Honorer</p>
                    <p class="text-3xl font-bold text-orange-600"><?= $stats['honorer'] ?></p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-clock text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">PPPK</p>
                    <p class="text-3xl font-bold text-purple-600"><?= $stats['pppk'] ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-contract text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
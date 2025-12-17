<?php
$pageTitle = 'Kesiswaan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Siswa.php';
require_once __DIR__ . '/../../classes/Kelas.php';

checkPermission('kesiswaan');

$siswa = new Siswa();
$kelas = new Kelas();

$totalSiswa = $siswa->getTotalCount();
$totalKelas = $kelas->getTotalCount();
$genderStats = $siswa->getStatsByGender();
$siswaLaki = $genderStats['L'] ?? 0;
$siswaPerempuan = $genderStats['P'] ?? 0;
?>

<main class="lg:ml-72 min-h-screen p-6">
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Kesiswaan</span>
        </nav>
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-user-graduate text-white text-xl"></i>
            </div>
            Kesiswaan
        </h2>
        <p class="text-gray-600 mt-2">Kelola data siswa dan administrasi kesiswaan</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="/e-TU/modules/kesiswaan/siswa.php" class="block group">
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-user-graduate text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Data Siswa</h3>
                <p class="text-blue-100 text-sm">Kelola data siswa aktif</p>
            </div>
        </a>

        <a href="/e-TU/modules/kesiswaan/kelas.php" class="block group">
            <div
                class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-chalkboard text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Manajemen Kelas</h3>
                <p class="text-purple-100 text-sm">Kelola kelas & wali kelas</p>
            </div>
        </a>

        <a href="/e-TU/modules/kesiswaan/mutasi.php" class="block group">
            <div
                class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-exchange-alt text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Mutasi Siswa</h3>
                <p class="text-amber-100 text-sm">Pindah & keluar masuk siswa</p>
            </div>
        </a>
    </div>

    <!-- Rekap / Statistics -->
    <div class="mt-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4 border-l-4 border-blue-600 pl-3">Rekapitulasi Data</h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Siswa Aktif</p>
                        <p class="text-3xl font-bold text-blue-600"><?= $totalSiswa ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Siswa Laki-laki</p>
                        <p class="text-3xl font-bold text-cyan-600"><?= $siswaLaki ?></p>
                    </div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-male text-cyan-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Siswa Perempuan</p>
                        <p class="text-3xl font-bold text-pink-600"><?= $siswaPerempuan ?></p>
                    </div>
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-female text-pink-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Jumlah Kelas</p>
                        <p class="text-3xl font-bold text-purple-600"><?= $totalKelas ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chalkboard text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
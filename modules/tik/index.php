<?php
$pageTitle = 'TIK & Pengaturan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

checkPermission('tik');
?>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-900 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-cog text-white text-xl"></i>
            </div>
            TIK & Pengaturan
        </h2>
        <p class="text-gray-600 mt-2">Konfigurasi sistem, manajemen user, dan log aktivitas</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="/e-TU/modules/tik/users.php" class="block group">
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-users-cog text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Manajemen User</h3>
                <p class="text-blue-100 text-sm">Kelola akun & password</p>
            </div>
        </a>

        <a href="/e-TU/modules/tik/roles.php" class="block group">
            <div
                class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-user-shield text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Role & Permission</h3>
                <p class="text-purple-100 text-sm">Hak akses pengguna</p>
            </div>
        </a>

        <a href="/e-TU/modules/tik/logs.php" class="block group">
            <div
                class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-history text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Log Aktivitas</h3>
                <p class="text-amber-100 text-sm">Audit trail & history</p>
            </div>
        </a>

        <a href="/e-TU/modules/tik/settings.php" class="block group">
            <div
                class="bg-gradient-to-br from-gray-600 to-gray-800 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-sliders-h text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Pengaturan</h3>
                <p class="text-gray-300 text-sm">Konfigurasi aplikasi</p>
            </div>
        </a>

    </div>

    <!-- Stats Overview -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total User</p>
                    <p class="text-3xl font-bold text-blue-600">15</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Role Tersedia</p>
                    <p class="text-3xl font-bold text-purple-600">3</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Aktivitas Hari Ini</p>
                    <p class="text-3xl font-bold text-amber-600">45</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bolt text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/classes/Dashboard.php';

// Fetch real data
$dashboard = new Dashboard();
$statsData = $dashboard->getStats();
$upcomingEvents = $dashboard->getUpcomingEvents();
$recentActivities = $dashboard->getRecentActivities();

// Format stats for display
$stats = [
    [
        'title' => 'Total Pegawai',
        'value' => $statsData['pegawai'],
        'icon' => 'fa-users',
        'color' => 'blue',
        'change' => 'Aktif',
        'percentage' => 'Data Terkini'
    ],
    [
        'title' => 'Total Siswa',
        'value' => $statsData['siswa'],
        'icon' => 'fa-user-graduate',
        'color' => 'emerald',
        'change' => 'Aktif',
        'percentage' => 'Data Terkini'
    ],
    [
        'title' => 'Surat Masuk',
        'value' => $statsData['surat_masuk'],
        'icon' => 'fa-envelope-open',
        'color' => 'amber',
        'change' => 'Baru',
        'percentage' => 'Minggu Ini'
    ],
    [
        'title' => 'Aset Inventaris',
        'value' => number_format($statsData['aset']),
        'icon' => 'fa-warehouse',
        'color' => 'purple',
        'change' => 'Item',
        'percentage' => 'Total Aset'
    ],
];


?>

<!-- Main Content -->
<main class="lg:ml-72 min-h-screen p-6">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white">Dashboard</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Selamat datang,
                    <strong><?= htmlspecialchars(Session::get('full_name')) ?></strong>!
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-md flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-600 dark:text-blue-400"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?= date('d F Y') ?></span>
                </div>
                <div class="bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-md flex items-center gap-2">
                    <i class="fas fa-clock text-emerald-600 dark:text-emerald-400"></i>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300" id="currentTime"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php foreach ($stats as $stat): ?>
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 hover:shadow-xl smooth-transition transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="w-12 h-12 bg-<?= $stat['color'] ?>-100 dark:bg-<?= $stat['color'] ?>-900 rounded-lg flex items-center justify-center">
                        <i
                            class="fas <?= $stat['icon'] ?> text-<?= $stat['color'] ?>-600 dark:text-<?= $stat['color'] ?>-400 text-xl"></i>
                    </div>
                    <div class="flex items-center gap-1 text-sm">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <span class="text-green-600 dark:text-green-400 font-semibold"><?= $stat['change'] ?></span>
                    </div>
                </div>
                <h3 class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1"><?= $stat['title'] ?></h3>
                <p class="text-3xl font-bold text-gray-800 dark:text-white"><?= $stat['value'] ?></p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2"><?= $stat['percentage'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts & Activities Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-history text-blue-600 dark:text-blue-400 mr-2"></i>
                    Aktivitas Terkini (Surat)
                </h3>
                <a href="/e-TU/modules/persuratan/surat-masuk.php"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold">Lihat
                    Semua →</a>
            </div>
            <div class="space-y-4">
                <?php if (empty($recentActivities)): ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada aktivitas surat.</p>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div
                            class="flex items-start gap-4 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 smooth-transition">
                            <div
                                class="w-10 h-10 bg-<?= $activity['color_type'] ?>-100 dark:bg-<?= $activity['color_type'] ?>-900 rounded-full flex items-center justify-center text-<?= $activity['color_type'] ?>-600 dark:text-<?= $activity['color_type'] ?>-400 font-bold text-sm flex-shrink-0">
                                <i class="fas <?= $activity['type'] === 'Surat Masuk' ? 'fa-inbox' : 'fa-paper-plane' ?>"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-800 dark:text-white font-medium"><?= htmlspecialchars($activity['type']) ?>
                                </p>
                                <p class="text-gray-600 dark:text-gray-400 text-sm"><?= htmlspecialchars($activity['action']) ?>
                                </p>
                                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">
                                    <i class="fas fa-clock mr-1"></i><?= timeAgo($activity['time']) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6">
                <i class="fas fa-calendar-check text-purple-600 dark:text-purple-400 mr-2"></i>
                Agenda Mendatang
            </h3>
            <div class="space-y-4">
                <?php if (empty($upcomingEvents)): ?>
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Tidak ada agenda mendatang.</p>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $event): ?>
                        <div class="border-l-4 border-purple-500 pl-4 py-2 bg-purple-50 dark:bg-purple-900/20 rounded-r-lg">
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm">
                                <?= htmlspecialchars($event['title']) ?></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                <i
                                    class="fas fa-calendar text-purple-600 dark:text-purple-400 mr-1"></i><?= date('d M Y', strtotime($event['date'])) ?>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                <i
                                    class="fas fa-clock text-purple-600 dark:text-purple-400 mr-1"></i><?= date('H:i', strtotime($event['time'])) ?>
                                WIB
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <a href="/e-TU/modules/kehumasan/agenda.php"
                class="block w-full mt-4 px-4 py-2 bg-purple-100 dark:bg-purple-900 hover:bg-purple-200 dark:hover:bg-purple-800 text-purple-700 dark:text-purple-200 rounded-lg font-semibold smooth-transition text-center">
                <i class="fas fa-plus mr-2"></i>Kelola Agenda
            </a>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-6">
            <i class="fas fa-bolt text-amber-600 dark:text-amber-400 mr-2"></i>
            Aksi Cepat
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="/e-TU/modules/persuratan/generator.php"
                class="flex flex-col items-center justify-center p-6 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white smooth-transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-file-alt text-3xl mb-3"></i>
                <span class="text-sm font-semibold text-center">Buat Surat</span>
            </a>
            <a href="/e-TU/modules/persuratan/surat-masuk.php"
                class="flex flex-col items-center justify-center p-6 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white smooth-transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-inbox text-3xl mb-3"></i>
                <span class="text-sm font-semibold text-center">Surat Masuk</span>
            </a>
            <a href="/e-TU/modules/kesiswaan/siswa.php"
                class="flex flex-col items-center justify-center p-6 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white smooth-transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-user-graduate text-3xl mb-3"></i>
                <span class="text-sm font-semibold text-center">Data Siswa</span>
            </a>
            <a href="/e-TU/modules/kepegawaian/pegawai.php"
                class="flex flex-col items-center justify-center p-6 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 hover:from-purple-600 hover:to-pink-700 text-white smooth-transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-users text-3xl mb-3"></i>
                <span class="text-sm font-semibold text-center">Data Pegawai</span>
            </a>
            <a href="/e-TU/modules/keuangan/kas.php"
                class="flex flex-col items-center justify-center p-6 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white smooth-transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-money-bill-wave text-3xl mb-3"></i>
                <span class="text-sm font-semibold text-center">Kas</span>
            </a>
            <a href="/e-TU/modules/sarpras/aset.php"
                class="flex flex-col items-center justify-center p-6 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white smooth-transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-warehouse text-3xl mb-3"></i>
                <span class="text-sm font-semibold text-center">Inventaris</span>
            </a>
        </div>
    </div>

</main>

<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds}`;
    }

    updateTime();
    setInterval(updateTime, 1000);
</script>

</body>

</html>
<?php
$pageTitle = 'Log Aktivitas';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/ActivityLog.php';

checkPermission('tik');

$log = new ActivityLog();
$allLogs = $log->getAll(100); // Limit 100 recent logs
$totalLogs = $log->getTotalCount();
$monthLogs = $log->getMonthCount();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">
    
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Log Aktivitas</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    Log Aktivitas
                </h2>
                <p class="text-gray-600 mt-2">Memantau aktivitas pengguna dalam sistem</p>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Aktivitas</p>
                    <p class="text-2xl font-bold text-gray-700"><?= number_format($totalLogs) ?></p>
                </div>
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-database text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Aktivitas Bulan Ini</p>
                    <p class="text-2xl font-bold text-blue-600"><?= number_format($monthLogs) ?></p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Aktivitas Terbaru</h3>
            <div class="flex gap-2">
                <input type="text" id="searchInput" placeholder="Cari log..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">Waktu</th>
                        <th class="text-left">User</th>
                        <th class="text-left">Module</th>
                        <th class="text-left">Action</th>
                        <th class="text-left">Deskripsi</th>
                        <th class="text-left">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allLogs) > 0): ?>
                        <?php foreach ($allLogs as $l): ?>
                            <tr class="hover:bg-gray-50 transition-colors text-sm">
                                <td class="whitespace-nowrap text-gray-600">
                                    <?= date('d/m/Y H:i', strtotime($l['created_at'])) ?>
                                </td>
                                <td class="font-semibold text-gray-800">
                                    <?= htmlspecialchars($l['full_name'] ?? 'Unknown') ?>
                                    <span class="block text-xs text-gray-500">@<?= htmlspecialchars($l['username'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">
                                        <?= htmlspecialchars($l['module']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 rounded text-xs font-bold 
                                        <?= match($l['action']) {
                                            'LOGIN' => 'bg-green-100 text-green-700',
                                            'LOGOUT' => 'bg-red-100 text-red-700',
                                            'CREATE' => 'bg-blue-100 text-blue-700',
                                            'UPDATE' => 'bg-amber-100 text-amber-700',
                                            'DELETE' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        } ?>">
                                        <?= htmlspecialchars($l['action']) ?>
                                    </span>
                                </td>
                                <td class="text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($l['description']) ?>">
                                    <?= htmlspecialchars($l['description']) ?>
                                </td>
                                <td class="text-gray-500 font-mono text-xs">
                                    <?= htmlspecialchars($l['ip_address']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                <div class="empty-state">
                                    <i class="fas fa-history text-4xl mb-3 text-gray-300"></i>
                                    <p>Belum ada data aktivitas</p>
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
    document.getElementById('searchInput')?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
$pageTitle = 'Buku Tamu - Portal Informasi';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Portal.php';

$portal = new Portal();

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $portal->deleteBukuTamu($_POST['id']);
    $message = "Data buku tamu dihapus.";
}

$bukuTamu = $portal->getAllBukuTamu();
?>

<main class="lg:ml-72 min-h-screen p-6">
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Portal Informasi</span>
        </nav>
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-900 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-address-book text-white text-xl"></i>
            </div>
            Buku Tamu
        </h2>
        <p class="text-gray-600 mt-2">Daftar tamu yang mengisi formulir di website</p>
    </div>

    <!-- Submenu Tabs -->
    <div class="flex gap-4 mb-6 border-b border-gray-200 pb-2">
        <a href="index.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Konten</a>
        <a href="buku-tamu.php" class="px-4 py-2 border-b-2 border-gray-800 text-gray-800 font-semibold">Buku Tamu</a>
        <a href="alumni.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Layanan Alumni</a>
    </div>

    <?php if (isset($message)): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b">
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Instansi</th>
                    <th class="p-3">Keperluan</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bukuTamu as $bt): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-sm text-gray-600">
                            <div class="font-semibold"><?= formatTanggal($bt['created_at'], 'short') ?></div>
                            <div class="text-xs text-gray-400"><?= date('H:i', strtotime($bt['created_at'])) ?> WIB</div>
                        </td>
                        <td class="p-3 font-semibold">
                            <?= htmlspecialchars($bt['nama']) ?>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($bt['email']) ?></div>
                        </td>
                        <td class="p-3"><?= htmlspecialchars($bt['instansi']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($bt['keperluan']) ?></td>
                        <td class="p-3">
                            <form method="POST" onsubmit="return confirm('Hapus data ini?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $bt['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800"><i
                                        class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($bukuTamu)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">Belum ada data buku tamu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
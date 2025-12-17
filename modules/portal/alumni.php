<?php
$pageTitle = 'Layanan Alumni - Portal Informasi';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Portal.php';

$portal = new Portal();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'update_status') {
        $portal->updateAlumniRequestStatus($_POST['id'], $_POST['status'], $_POST['keterangan_admin']);
        $message = "Status permohonan diperbarui.";
    } elseif ($_POST['action'] === 'delete') {
        $portal->deleteAlumniRequest($_POST['id']);
        $message = "Permohonan dihapus.";
    }
}

$requests = $portal->getAllAlumniRequests();
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
                <i class="fas fa-graduation-cap text-white text-xl"></i>
            </div>
            Layanan Umum
        </h2>
        <p class="text-gray-600 mt-2">Permohonan layanan Umum (Legalisir, Surat Keterangan, dll)</p>
    </div>

    <!-- Submenu Tabs -->
    <div class="flex gap-4 mb-6 border-b border-gray-200 pb-2">
        <a href="index.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Konten</a>
        <a href="buku-tamu.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Buku Tamu</a>
        <a href="alumni.php" class="px-4 py-2 border-b-2 border-gray-800 text-gray-800 font-semibold">Layanan Umum</a>
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
                    <th class="p-3">Tgl Request</th>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Layanan</th>
                    <th class="p-3">Kontak</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-sm text-gray-500"><?= date('d/m/Y', strtotime($req['created_at'])) ?></td>
                        <td class="p-3 font-semibold">
                            <?= htmlspecialchars($req['nama']) ?>
                            <div class="text-xs text-gray-400">Lulus: <?= $req['tahun_lulus'] ?> | NISN: <?= $req['nisn'] ?>
                            </div>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded-full cursor-help"
                                title="<?= htmlspecialchars($req['jenis_layanan']) ?>">
                                <?= htmlspecialchars($req['jenis_layanan']) ?>
                            </span>
                        </td>
                        <td class="p-3"><?= htmlspecialchars($req['no_hp']) ?></td>
                        <td class="p-3">
                            <?php
                            $statusClass = match ($req['status']) {
                                'Pending' => 'bg-yellow-100 text-yellow-700',
                                'Proses' => 'bg-blue-100 text-blue-700',
                                'Selesai' => 'bg-green-100 text-green-700',
                                'Ditolak' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700'
                            };
                            ?>
                            <span class="px-2 py-1 rounded-full text-xs font-bold <?= $statusClass ?>">
                                <?= $req['status'] ?>
                            </span>
                        </td>
                        <td class="p-3">
                            <button onclick='editStatus(<?= json_encode($req) ?>)'
                                class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" class="inline" onsubmit="return confirm('Hapus permohonan?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $req['id'] ?>">
                                <button type="submit" class="text-red-600 hover:text-red-800"><i
                                        class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">Belum ada permohonan layanan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Update Status -->
<div id="modalStatus" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Update Status Permohonan</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" id="status_id">

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Status</label>
                <select name="status" id="status_select" class="w-full border rounded px-3 py-2">
                    <option value="Pending">Pending</option>
                    <option value="Proses">Proses</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold mb-2">Catatan Admin</label>
                <textarea name="keterangan_admin" id="status_ket" rows="3" class="w-full border rounded px-3 py-2"
                    placeholder="Informasi pengambilan dokumen dll..."></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalStatus').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editStatus(req) {
        document.getElementById('status_id').value = req.id;
        document.getElementById('status_select').value = req.status;
        document.getElementById('status_ket').value = req.keterangan_admin || '';
        document.getElementById('modalStatus').classList.remove('hidden');
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
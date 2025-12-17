<?php
$pageTitle = 'Peminjaman Aset - Sarana Prasarana';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Aset.php';
require_once __DIR__ . '/../../classes/PeminjamanAset.php';

checkPermission('sarpras');

$aset = new Aset();
$peminjaman = new PeminjamanAset();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $data = [
                'aset_id' => $_POST['aset_id'],
                'nama_peminjam' => clean($_POST['nama']),
                'no_hp' => clean($_POST['no_hp']),
                'tanggal_pinjam' => $_POST['tanggal_pinjam'],
                'tanggal_kembali' => $_POST['tanggal_kembali'],
                'keperluan' => clean($_POST['keperluan']),
                'keterangan' => clean($_POST['keterangan']) ?? null
            ];

            if ($peminjaman->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Peminjaman berhasil diajukan!', 'success');
            }
        } elseif ($_POST['action'] === 'kembalikan') {
            if ($peminjaman->kembalikan($_POST['id'], date('Y-m-d'))) {
                redirect($_SERVER['PHP_SELF'], 'Aset berhasil dikembalikan!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($peminjaman->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Data peminjaman berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get daftar aset yang tersedia untuk dipinjam
$asetTersedia = $aset->getAvailableForBorrowing();
$allPeminjaman = $peminjaman->getAll();
$statusCounts = $peminjaman->getStatusCounts();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/sarpras/index.php" class="hover:text-blue-600">Sarpras</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Peminjaman Aset</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-rose-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-file-export text-white text-xl"></i>
                    </div>
                    Peminjaman Aset
                </h2>
                <p class="text-gray-600 mt-2">Kelola peminjaman aset sekolah</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-rose-600 to-red-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Ajukan Peminjaman
            </button>
        </div>
    </div>

    <!-- Alert Info -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Catatan:</strong> Hanya aset yang <strong>diperbolehkan dipinjam</strong>,
                    tidak sedang <strong>dipinjam</strong>, dan tidak dalam <strong>maintenance</strong> yang dapat
                    dipinjam.
                </p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total Peminjaman</p>
            <p class="text-2xl font-bold text-rose-600"><?= count($allPeminjaman) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Sedang Dipinjam</p>
            <p class="text-2xl font-bold text-amber-600"><?= $statusCounts['Dipinjam'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Dikembalikan</p>
            <p class="text-2xl font-bold text-green-600"><?= $statusCounts['Dikembalikan'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Aset Tersedia</p>
            <p class="text-2xl font-bold text-blue-600"><?= count($asetTersedia) ?></p>
        </div>
    </div>

    <!-- Peminjaman Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Peminjaman</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Nama Peminjam</th>
                        <th class="text-left">Aset</th>
                        <th class="text-left">Tanggal Pinjam</th>
                        <th class="text-left">Tanggal Kembali</th>
                        <th class="text-left">Keperluan</th>
                        <th class="text-left">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allPeminjaman) > 0): ?>
                        <?php foreach ($allPeminjaman as $index => $p): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($p['nama_peminjam']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($p['no_hp']) ?></p>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($p['nama_barang']) ?></p>
                                        <p class="text-xs text-gray-500"><?= htmlspecialchars($p['kode_aset']) ?></p>
                                    </div>
                                </td>
                                <td><?= formatTanggal($p['tanggal_pinjam'], 'short') ?></td>
                                <td><?= formatTanggal($p['tanggal_kembali'], 'short') ?></td>
                                <td><?= htmlspecialchars($p['keperluan']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $p['status'] === 'Dipinjam' ? 'warning' : 'success' ?>">
                                        <?= $p['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($p['status'] === 'Dipinjam'): ?>
                                            <button onclick="kembalikanAset(<?= $p['id'] ?>)"
                                                class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-sm">
                                                <i class="fas fa-check mr-1"></i>Kembalikan
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteP eminjaman(<?= $p['id'] ?>)"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-file-export"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada peminjaman</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Ajukan Peminjaman Aset</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Peminjam *</label>
                    <input type="text" name="nama" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">No. HP/Kontak *</label>
                    <input type="text" name="no_hp" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Aset *</label>
                    <select name="aset_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                        <option value="">Pilih Aset (Tersedia untuk Dipinjam)...</option>
                        <?php foreach ($asetTersedia as $a): ?>
                            <option value="<?= $a['id'] ?>">
                                <?= htmlspecialchars($a['kode_aset']) ?> - <?= htmlspecialchars($a['nama_barang']) ?>
                                (<?= htmlspecialchars($a['kategori']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (count($asetTersedia) === 0): ?>
                        <p class="text-xs text-red-500 mt-1">
                            <i class="fas fa-exclamation-triangle"></i> Tidak ada aset yang tersedia untuk dipinjam saat
                            ini.
                        </p>
                    <?php else: ?>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-check-circle text-green-500"></i> Hanya menampilkan aset yang
                            <strong>diperbolehkan dipinjam</strong>, tidak sedang dipinjam, dan tidak dalam
                            maintenance
                        </p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pinjam *</label>
                    <input type="date" name="tanggal_pinjam" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pengembalian *</label>
                    <input type="date" name="tanggal_kembali" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan *</label>
                    <textarea name="keperluan" required rows="3"
                        placeholder="Jelaskan untuk keperluan apa aset dipinjam..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-rose-600 to-red-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-paper-plane mr-2"></i>Ajukan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function kembalikanAset(id) {
        if (confirm('Apakah Anda yakin aset sudah dikembalikan?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="kembalikan">
            <input type="hidden" name="id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deletePeminjaman(id) {
        if (confirmDelete('Apakah Anda yakin ingin menghapus data peminjaman ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
$pageTitle = 'Inventaris Aset - Sarana Prasarana';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Aset.php';

checkPermission('sarpras');

$aset = new Aset();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $data = [
                'kode_aset' => clean($_POST['kode_aset']),
                'nama_barang' => clean($_POST['nama_barang']),
                'kategori' => $_POST['kategori'],
                'kondisi' => $_POST['kondisi'],
                'dapat_dipinjam' => $_POST['dapat_dipinjam'] ?? 'Ya',
                'lokasi' => clean($_POST['lokasi']) ?? null,
                'tanggal_perolehan' => $_POST['tanggal_perolehan'],
                'nilai_perolehan' => $_POST['nilai_perolehan'] ?? 0,
                'keterangan' => clean($_POST['keterangan']) ?? null
            ];

            if ($aset->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Aset berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $data = [
                'kode_aset' => clean($_POST['kode_aset']),
                'nama_barang' => clean($_POST['nama_barang']),
                'kategori' => $_POST['kategori'],
                'kondisi' => $_POST['kondisi'],
                'dapat_dipinjam' => $_POST['dapat_dipinjam'] ?? 'Ya',
                'lokasi' => clean($_POST['lokasi']) ?? null,
                'tanggal_perolehan' => $_POST['tanggal_perolehan'],
                'nilai_perolehan' => $_POST['nilai_perolehan'] ?? 0,
                'keterangan' => clean($_POST['keterangan']) ?? null
            ];

            if ($aset->update($id, $data)) {
                redirect($_SERVER['PHP_SELF'], 'Aset berhasil diperbarui!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($aset->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Aset berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

try {
    $allAset = $aset->getAll();
} catch (Exception $e) {
    $allAset = [];
    $message = 'Error mengambil data: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/sarpras/index.php" class="hover:text-blue-600">Sarpras</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Inventaris Aset</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box text-white text-xl"></i>
                    </div>
                    Inventaris Aset
                </h2>
                <p class="text-gray-600 mt-2">Kelola inventaris aset sekolah</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Aset
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total Aset</p>
            <p class="text-2xl font-bold text-amber-600"><?= count($allAset) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Kondisi Baik</p>
            <p class="text-2xl font-bold text-green-600">
                <?= count(array_filter($allAset, fn($a) => $a['kondisi'] === 'Baik')) ?>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Rusak</p>
            <p class="text-2xl font-bold text-red-600">
                <?= count(array_filter($allAset, fn($a) => in_array($a['kondisi'], ['Rusak Ringan', 'Rusak Berat']))) ?>
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Dapat Dipinjam</p>
            <p class="text-2xl font-bold text-blue-600">
                <?= count(array_filter($allAset, fn($a) => $a['kondisi'] === 'Baik')) ?>
            </p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Kategori</option>
                    <option>Elektronik</option>
                    <option>Furniture</option>
                    <option>Kendaraan</option>
                    <option>Alat Tulis</option>
                    <option>Lainnya</option>
                </select>
            </div>
            <div>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Kondisi</option>
                    <option>Baik</option>
                    <option>Rusak Ringan</option>
                    <option>Rusak Berat</option>
                </select>
            </div>
            <div>
                <input type="text" id="searchInput" placeholder="Cari aset..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <button class="w-full px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Aset Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Inventaris Aset</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Kode Aset</th>
                        <th class="text-left">Nama Barang</th>
                        <th class="text-left">Kategori</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Dapat Dipinjam</th>
                        <th class="text-left">Kondisi</th>
                        <th class="text-left">Lokasi</th>
                        <th class="text-right">Nilai</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allAset) > 0): ?>
                        <?php foreach ($allAset as $index => $a): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="font-semibold"><?= htmlspecialchars($a['kode_aset']) ?></td>
                                <td><?= htmlspecialchars($a['nama_barang']) ?></td>
                                <td>
                                    <span
                                        class="text-xs px-2 py-1 bg-gray-100 rounded"><?= htmlspecialchars($a['kategori'] ?? '-') ?></span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $availability = $aset->isAvailableForBorrowing($a['id']);
                                    $statusClass = $availability['available'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700';
                                    ?>
                                    <span class="text-xs px-2 py-1 rounded font-semibold <?= $statusClass ?>">
                                        <?= $availability['reason'] ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="text-xs px-2 py-1 rounded font-semibold <?= $a['dapat_dipinjam'] === 'Ya' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700' ?>">
                                        <?= $a['dapat_dipinjam'] ?? 'Ya' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?=
                                        $a['kondisi'] === 'Baik' ? 'success' :
                                        ($a['kondisi'] === 'Rusak Ringan' ? 'warning' : 'danger')
                                        ?>">
                                        <?= $a['kondisi'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($a['lokasi'] ?? '-') ?></td>
                                <td class="text-right"><?= formatRupiah($a['nilai_perolehan']) ?></td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='editAset(<?= json_encode($a) ?>)'
                                            class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            onclick="masukkanMaintenance(<?= $a['id'] ?>, '<?= htmlspecialchars($a['nama_barang']) ?>')"
                                            class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded text-sm"
                                            title="Masukkan ke Maintenance">
                                            <i class="fas fa-tools"></i>
                                        </button>
                                        <button onclick="deleteAset(<?= $a['id'] ?>)"
                                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada data aset</p>
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
    <div class="modal-content max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Tambah Inventaris Aset</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formAset">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="asetId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kode Aset *</label>
                    <input type="text" name="kode_aset" id="kode_aset" required placeholder="Contoh: AST-2025-001"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Barang *</label>
                    <input type="text" name="nama_barang" id="nama_barang" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori *</label>
                    <select name="kategori" id="kategori" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">Pilih...</option>
                        <option value="Elektronik">Elektronik</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Kendaraan">Kendaraan</option>
                        <option value="Alat Tulis">Alat Tulis</option>
                        <option value="Olahraga">Alat Olahraga</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Perolehan *</label>
                    <input type="date" name="tanggal_perolehan" id="tanggal_perolehan" required
                        value="<?= date('Y-m-d') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kondisi *</label>
                    <select name="kondisi" id="kondisi" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">Pilih...</option>
                        <option value="Baik">Baik</option>
                        <option value="Rusak Ringan">Rusak Ringan</option>
                        <option value="Rusak Berat">Rusak Berat</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi</label>
                    <input type="text" name="lokasi" id="lokasi" placeholder="Contoh: Ruang Kelas X-1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nilai Perolehan (Rp)</label>
                    <input type="number" name="nilai_perolehan" id="nilai_perolehan" min="0" placeholder="0"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dapat Dipinjam? *</label>
                    <div class="flex gap-4 mt-2">
                        <label class="flex items-center">
                            <input type="radio" name="dapat_dipinjam" id="dapat_dipinjam_ya" value="Ya" checked
                                class="mr-2">
                            <span>Ya</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="dapat_dipinjam" id="dapat_dipinjam_tidak" value="Tidak"
                                class="mr-2">
                            <span>Tidak</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">*Tentukan apakah aset ini boleh dipinjam/disewa</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-amber-600 to-orange-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function openModalAdd() {
        document.getElementById('modalTitle').textContent = 'Tambah Inventaris Aset';
        document.getElementById('formAction').value = 'create';
        document.getElementById('asetId').value = '';
        document.getElementById('formAset').reset();
        openModal('modalAdd');
    }

    function editAset(data) {
        document.getElementById('modalTitle').textContent = 'Edit Inventaris Aset';
        document.getElementById('formAction').value = 'update';
        document.getElementById('asetId').value = data.id;

        document.getElementById('kode_aset').value = data.kode_aset;
        document.getElementById('nama_barang').value = data.nama_barang;
        document.getElementById('kategori').value = data.kategori;
        document.getElementById('kondisi').value = data.kondisi;
        document.getElementById('lokasi').value = data.lokasi || '';
        document.getElementById('tanggal_perolehan').value = data.tanggal_perolehan;
        document.getElementById('nilai_perolehan').value = data.nilai_perolehan;
        document.getElementById('keterangan').value = data.keterangan || '';

        // Set dapat_dipinjam radio
        if (data.dapat_dipinjam === 'Tidak') {
            document.getElementById('dapat_dipinjam_tidak').checked = true;
        } else {
            document.getElementById('dapat_dipinjam_ya').checked = true;
        }

        openModal('modalAdd');
    }

    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function deleteAset(id) {
        if (confirm('Hapus aset ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function masukkanMaintenance(asetId, namaBarang) {
        if (confirm(`Masukkan "${namaBarang}" ke maintenance?\n\nAnda akan diarahkan ke halaman Maintenance untuk melengkapi data.`)) {
            // Redirect to maintenance page with pre-filled asset
            window.location.href = `/e-TU/modules/sarpras/maintenance.php?aset_id=${asetId}`;
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
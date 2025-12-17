<?php
$pageTitle = 'Agenda Kegiatan - Kehumasan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Agenda.php';

checkPermission('kehumasan');

$agenda = new Agenda();

// Update status otomatis
$agenda->updateStatusOtomatis();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $data = [
                'judul' => clean($_POST['judul']),
                'deskripsi' => clean($_POST['deskripsi']) ?? null,
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'] ?: $_POST['tanggal_mulai'],
                'waktu_mulai' => $_POST['waktu_mulai'] ?: null,
                'waktu_selesai' => $_POST['waktu_selesai'] ?: null,
                'lokasi' => clean($_POST['lokasi']) ?? null,
                'penanggungjawab' => clean($_POST['penanggungjawab']) ?? null,
                'status' => $_POST['status']
            ];

            if ($agenda->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Agenda berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($agenda->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Agenda berhasil dihapus!', 'success');
            }
        } elseif ($_POST['action'] === 'update_status') {
            $agendaData = $agenda->getById($_POST['id']);
            if ($agendaData) {
                $agendaData['status'] = $_POST['status'];
                if ($agenda->update($_POST['id'], $agendaData)) {
                    redirect($_SERVER['PHP_SELF'], 'Status agenda berhasil diupdate!', 'success');
                }
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$allAgenda = $agenda->getAll();
$agendaBulanIni = $agenda->getBulanIni();
$statusCounts = $agenda->getStatusCounts();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kehumasan/index.php" class="hover:text-blue-600">Kehumasan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Agenda Kegiatan</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                    Agenda Kegiatan
                </h2>
                <p class="text-gray-600 mt-2">Kelola jadwal dan agenda kegiatan sekolah</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-pink-600 to-rose-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Agenda
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total Agenda</p>
            <p class="text-2xl font-bold text-pink-600"><?= count($allAgenda) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Bulan Ini</p>
            <p class="text-2xl font-bold text-blue-600"><?= count($agendaBulanIni) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Akan Datang</p>
            <p class="text-2xl font-bold text-amber-600"><?= $statusCounts['Akan Datang'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Selesai</p>
            <p class="text-2xl font-bold text-green-600"><?= $statusCounts['Selesai'] ?></p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <div class="flex items-center gap-4 flex-wrap">
            <div>
                <select id="filterStatus" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Status</option>
                    <option value="Akan Datang">Akan Datang</option>
                    <option value="Berlangsung">Berlangsung</option>
                    <option value="Selesai">Selesai</option>
                </select>
            </div>
            <div>
                <input type="text" id="searchInput" placeholder="Cari agenda..."
                    class="px-4 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>
    </div>

    <!-- Agenda Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-800">Daftar Agenda</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Judul Kegiatan</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Waktu</th>
                        <th class="text-left">Lokasi</th>
                        <th class="text-left">PIC</th>
                        <th class="text-left">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allAgenda) > 0): ?>
                        <?php foreach ($allAgenda as $index => $a): ?>
                            <tr data-status="<?= $a['status'] ?>">
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($a['judul']) ?></p>
                                        <?php if ($a['deskripsi']): ?>
                                            <p class="text-xs text-gray-500">
                                                <?= htmlspecialchars(substr($a['deskripsi'], 0, 50)) ?>...</p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($a['tanggal_mulai'] === $a['tanggal_selesai']): ?>
                                        <?= formatTanggal($a['tanggal_mulai'], 'short') ?>
                                    <?php else: ?>
                                        <?= formatTanggal($a['tanggal_mulai'], 'short') ?> -
                                        <?= formatTanggal($a['tanggal_selesai'], 'short') ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($a['waktu_mulai']): ?>
                                        <?= date('H:i', strtotime($a['waktu_mulai'])) ?>
                                        <?php if ($a['waktu_selesai']): ?>
                                            - <?= date('H:i', strtotime($a['waktu_selesai'])) ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($a['lokasi'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($a['penanggungjawab'] ?? '-') ?></td>
                                <td>
                                    <span class="badge badge-<?=
                                        $a['status'] === 'Selesai' ? 'success' :
                                        ($a['status'] === 'Berlangsung' ? 'info' : 'warning')
                                        ?>">
                                        <?= $a['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($a['status'] !== 'Selesai'): ?>
                                            <button onclick="updateStatus(<?= $a['id'] ?>, 'Selesai')"
                                                class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-sm"
                                                title="Tandai Selesai">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button onclick="deleteAgenda(<?= $a['id'] ?>)"
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
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada agenda</p>
                                    <p class="text-sm">Klik "Tambah Agenda" untuk membuat jadwal kegiatan</p>
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
            <h3 class="text-2xl font-bold text-gray-800">Tambah Agenda Kegiatan</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul Kegiatan *</label>
                    <input type="text" name="judul" required placeholder="Contoh: Upacara Bendera"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika hanya 1 hari</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Mulai</label>
                    <input type="time" name="waktu_mulai" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Selesai</label>
                    <input type="time" name="waktu_selesai" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi</label>
                    <input type="text" name="lokasi" placeholder="Contoh: Aula Sekolah"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Penanggung Jawab</label>
                    <input type="text" name="penanggungjawab" placeholder="Nama PIC"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="Akan Datang">Akan Datang</option>
                        <option value="Berlangsung">Berlangsung</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="deskripsi" rows="3" placeholder="Keterangan tambahan tentang kegiatan..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-pink-600 to-rose-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    // Search
    document.getElementById('searchInput')?.addEventListener('input', function () {
        filterTable();
    });

    // Filter by status
    document.getElementById('filterStatus')?.addEventListener('change', function () {
        filterTable();
    });

    function filterTable() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('filterStatus').value;
        const rows = document.querySelectorAll('#dataTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const status = row.dataset.status;

            const matchSearch = text.includes(searchTerm);
            const matchStatus = !statusFilter || status === statusFilter;

            row.style.display = (matchSearch && matchStatus) ? '' : 'none';
        });
    }

    function updateStatus(id, status) {
        if (confirm('Update status agenda menjadi "' + status + '"?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="status" value="${status}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteAgenda(id) {
        if (confirm('Hapus agenda ini?')) {
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
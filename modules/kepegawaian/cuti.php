<?php
$pageTitle = 'Manajemen Cuti - Kepegawaian';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Cuti.php';
require_once __DIR__ . '/../../classes/Pegawai.php';

checkPermission('kepegawaian');

$cuti = new Cuti();
$pegawai = new Pegawai();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $data = [
                'pegawai_id' => $_POST['pegawai_id'],
                'jenis_cuti' => $_POST['jenis_cuti'],
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'],
                'jumlah_hari' => $_POST['jumlah_hari'],
                'keterangan' => clean($_POST['keterangan'])
            ];

            if ($cuti->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Pengajuan cuti berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $data = [
                'jenis_cuti' => $_POST['jenis_cuti'],
                'tanggal_mulai' => $_POST['tanggal_mulai'],
                'tanggal_selesai' => $_POST['tanggal_selesai'],
                'jumlah_hari' => $_POST['jumlah_hari'],
                'keterangan' => clean($_POST['keterangan'])
            ];

            if ($cuti->update($id, $data)) {
                redirect($_SERVER['PHP_SELF'], 'Data cuti berhasil diperbarui!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($cuti->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Data cuti berhasil dihapus!', 'success');
            }
        } elseif ($_POST['action'] === 'approve') {
            // TODO: Get current user ID from session
            $userId = 1; // Placeholder
            if ($cuti->updateStatus($_POST['id'], 'Disetujui', $userId)) {
                redirect($_SERVER['PHP_SELF'], 'Pengajuan cuti disetujui!', 'success');
            }
        } elseif ($_POST['action'] === 'reject') {
            $userId = 1; // Placeholder
            if ($cuti->updateStatus($_POST['id'], 'Ditolak', $userId, $_POST['alasan_penolakan'])) {
                redirect($_SERVER['PHP_SELF'], 'Pengajuan cuti ditolak!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$allCuti = $cuti->getAll();
$allPegawai = $pegawai->getAll();
$stats = $cuti->getStats();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kepegawaian/index.php" class="hover:text-blue-600">Kepegawaian</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Manajemen Cuti</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-calendar-minus text-white text-xl"></i>
                    </div>
                    Manajemen Cuti
                </h2>
                <p class="text-gray-600 mt-2">Kelola pengajuan cuti pegawai</p>
            </div>
            <button onclick="openModalAdd()"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Ajukan Cuti
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Menunggu Persetujuan</p>
                    <p class="text-2xl font-bold text-yellow-600"><?= $stats['menunggu'] ?></p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600"><?= $stats['disetujui'] ?></p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600"><?= $stats['ditolak'] ?></p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengajuan Cuti</h3>
            <input type="text" id="searchInput" placeholder="Cari nama pegawai..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Pegawai</th>
                        <th class="text-left">Jenis Cuti</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Durasi</th>
                        <th class="text-left">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allCuti) > 0): ?>
                        <?php foreach ($allCuti as $index => $c): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <?php if ($c['foto']): ?>
                                            <img src="/e-TU/<?= $c['foto'] ?>" class="w-8 h-8 rounded-full object-cover">
                                        <?php else: ?>
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                                <i class="fas fa-user text-xs text-gray-500"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <p class="font-semibold text-sm"><?= htmlspecialchars($c['nama_lengkap']) ?></p>
                                            <p class="text-xs text-gray-500"><?= htmlspecialchars($c['nip']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($c['jenis_cuti']) ?></td>
                                <td class="text-sm">
                                    <?= formatTanggal($c['tanggal_mulai'], 'short') ?> - 
                                    <?= formatTanggal($c['tanggal_selesai'], 'short') ?>
                                </td>
                                <td><?= $c['jumlah_hari'] ?> Hari</td>
                                <td>
                                    <?php
                                    $statusClass = match($c['status']) {
                                        'Disetujui' => 'success',
                                        'Ditolak' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge badge-<?= $statusClass ?>"><?= $c['status'] ?></span>
                                </td>
                                <td>
                                    <div class="flex items-center justify-center gap-2">
                                        <button onclick='viewCuti(<?= json_encode($c) ?>)'
                                            class="px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <?php if ($c['status'] === 'Menunggu'): ?>
                                            <button onclick='editCuti(<?= json_encode($c) ?>)'
                                                class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="approveCuti(<?= $c['id'] ?>)"
                                                class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded text-sm" title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="rejectCuti(<?= $c['id'] ?>)"
                                                class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-sm" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button onclick="deleteCuti(<?= $c['id'] ?>)"
                                            class="px-3 py-1.5 bg-gray-500 hover:bg-gray-600 text-white rounded text-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar-times"></i>
                                    </div>
                                    <p class="text-lg font-semibold">Belum ada data pengajuan cuti</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add/Edit -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Ajukan Cuti Baru</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formCuti">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="cutiId" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Pegawai *</label>
                    <select name="pegawai_id" id="pegawai_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($allPegawai as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_lengkap']) ?> - <?= htmlspecialchars($p['nip']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Cuti *</label>
                    <select name="jenis_cuti" id="jenis_cuti" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih...</option>
                        <option value="Cuti Tahunan">Cuti Tahunan</option>
                        <option value="Cuti Sakit">Cuti Sakit</option>
                        <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                        <option value="Cuti Besar">Cuti Besar</option>
                        <option value="Cuti Alasan Penting">Cuti Alasan Penting</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Hari</label>
                    <input type="number" name="jumlah_hari" id="jumlah_hari" required min="1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mulai *</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Selesai *</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan / Alasan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View -->
<div id="modalView" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Detail Pengajuan Cuti</h3>
            <button onclick="closeModal('modalView')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="viewContent" class="space-y-4">
            <!-- Content populated by JS -->
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="closeModal('modalView')" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                Tutup
            </button>
        </div>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Calculate days automatically
    const tglMulai = document.getElementById('tanggal_mulai');
    const tglSelesai = document.getElementById('tanggal_selesai');
    const jmlHari = document.getElementById('jumlah_hari');

    function calculateDays() {
        if (tglMulai.value && tglSelesai.value) {
            const start = new Date(tglMulai.value);
            const end = new Date(tglSelesai.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
            if (diffDays > 0) jmlHari.value = diffDays;
        }
    }

    tglMulai.addEventListener('change', calculateDays);
    tglSelesai.addEventListener('change', calculateDays);

    function openModalAdd() {
        document.getElementById('modalTitle').textContent = 'Ajukan Cuti Baru';
        document.getElementById('formAction').value = 'create';
        document.getElementById('cutiId').value = '';
        document.getElementById('formCuti').reset();
        document.getElementById('pegawai_id').disabled = false;
        openModal('modalAdd');
    }

    function editCuti(data) {
        document.getElementById('modalTitle').textContent = 'Edit Pengajuan Cuti';
        document.getElementById('formAction').value = 'update';
        document.getElementById('cutiId').value = data.id;
        
        document.getElementById('pegawai_id').value = data.pegawai_id;
        document.getElementById('pegawai_id').disabled = true; // Cannot change employee when editing
        
        document.getElementById('jenis_cuti').value = data.jenis_cuti;
        document.getElementById('tanggal_mulai').value = data.tanggal_mulai;
        document.getElementById('tanggal_selesai').value = data.tanggal_selesai;
        document.getElementById('jumlah_hari').value = data.jumlah_hari;
        document.getElementById('keterangan').value = data.keterangan;
        
        openModal('modalAdd');
    }

    function viewCuti(data) {
        const statusColors = {
            'Menunggu': 'bg-yellow-100 text-yellow-700',
            'Disetujui': 'bg-green-100 text-green-700',
            'Ditolak': 'bg-red-100 text-red-700'
        };
        
        const content = `
            <div class="flex items-center gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 flex-shrink-0">
                    ${data.foto ? `<img src="/e-TU/${data.foto}" class="w-full h-full object-cover">` : `<div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-user text-2xl"></i></div>`}
                </div>
                <div>
                    <h4 class="text-lg font-bold text-gray-800">${data.nama_lengkap}</h4>
                    <p class="text-gray-600 text-sm">${data.nip}</p>
                    <p class="text-gray-500 text-xs">${data.nama_jabatan || '-'}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="col-span-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold ${statusColors[data.status] || 'bg-gray-100'}">
                        ${data.status}
                    </span>
                </div>
                <div>
                    <p class="text-gray-500">Jenis Cuti</p>
                    <p class="font-semibold text-gray-800">${data.jenis_cuti}</p>
                </div>
                <div>
                    <p class="text-gray-500">Durasi</p>
                    <p class="font-semibold text-gray-800">${data.jumlah_hari} Hari</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Mulai</p>
                    <p class="font-semibold text-gray-800">${formatTanggal(data.tanggal_mulai)}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Selesai</p>
                    <p class="font-semibold text-gray-800">${formatTanggal(data.tanggal_selesai)}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500">Keterangan</p>
                    <p class="font-semibold text-gray-800 bg-gray-50 p-3 rounded mt-1">${data.keterangan}</p>
                </div>
                ${data.status === 'Ditolak' ? `
                <div class="col-span-2 mt-2">
                    <p class="text-red-500 font-semibold">Alasan Penolakan:</p>
                    <p class="text-red-700 bg-red-50 p-3 rounded mt-1">${data.alasan_penolakan || '-'}</p>
                </div>
                ` : ''}
            </div>
        `;
        
        // Helper function for date formatting in JS (simple version)
        function formatTanggal(dateStr) {
            if(!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        }
        
        document.getElementById('viewContent').innerHTML = content;
        openModal('modalView');
    }

    function deleteCuti(id) {
        if (confirm('Apakah Anda yakin ingin menghapus data pengajuan cuti ini?')) {
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

    function approveCuti(id) {
        if (confirm('Setujui pengajuan cuti ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function rejectCuti(id) {
        const alasan = prompt('Masukkan alasan penolakan:');
        if (alasan !== null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="alasan_penolakan" value="${alasan}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
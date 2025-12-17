<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Pegawai.php';
require_once __DIR__ . '/../../classes/RiwayatPegawai.php';

// Check permission
checkPermission('kepegawaian');

$pegawai = new Pegawai();
$riwayat = new RiwayatPegawai();

// Get pegawai ID (Support GET and POST)
$pegawaiId = $_REQUEST['id'] ?? $_REQUEST['pegawai_id'] ?? null;

if (!$pegawaiId) {
    redirect('/e-TU/modules/kepegawaian/pegawai.php', 'ID Pegawai tidak ditemukan.', 'error');
}

// Get pegawai data
$dataPegawai = $pegawai->getById($pegawaiId);
if (!$dataPegawai) {
    // Debug info if ID not found
    echo "<h1>Error: Data Pegawai Tidak Ditemukan</h1>";
    echo "<p>ID yang dicari: " . htmlspecialchars($pegawaiId) . "</p>";
    echo "<p>Silakan kembali dan coba lagi.</p>";
    echo "<a href='/e-TU/modules/kepegawaian/pegawai.php'>Kembali</a>";
    exit;
}

// Handle form submission (MUST BE BEFORE HEADER/SIDEBAR)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $data = [
                'pegawai_id' => $pegawaiId,
                'jenis_riwayat' => $_POST['jenis_riwayat'],
                'judul' => clean($_POST['judul']),
                'instansi_lokasi' => clean($_POST['instansi_lokasi']),
                'tahun_mulai' => $_POST['tahun_mulai'] ?: null,
                'tahun_selesai' => $_POST['tahun_selesai'] ?: null,
                'nomor_sk' => clean($_POST['nomor_sk']),
                'tanggal_sk' => $_POST['tanggal_sk'] ?: null,
                'keterangan' => clean($_POST['keterangan'])
            ];

            if ($riwayat->create($data)) {
                redirect("/e-TU/modules/kepegawaian/riwayat.php?id=$pegawaiId", 'Riwayat berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['riwayat_id'];
            $data = [
                'jenis_riwayat' => $_POST['jenis_riwayat'],
                'judul' => clean($_POST['judul']),
                'instansi_lokasi' => clean($_POST['instansi_lokasi']),
                'tahun_mulai' => $_POST['tahun_mulai'] ?: null,
                'tahun_selesai' => $_POST['tahun_selesai'] ?: null,
                'nomor_sk' => clean($_POST['nomor_sk']),
                'tanggal_sk' => $_POST['tanggal_sk'] ?: null,
                'keterangan' => clean($_POST['keterangan'])
            ];

            if ($riwayat->update($id, $data)) {
                redirect("/e-TU/modules/kepegawaian/riwayat.php?id=$pegawaiId", 'Riwayat berhasil diperbarui!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($riwayat->delete($_POST['riwayat_id'])) {
                redirect("/e-TU/modules/kepegawaian/riwayat.php?id=$pegawaiId", 'Riwayat berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
        // We can't use JS alert here easily because header isn't loaded yet.
        // But we can set a flash message and let the page load.
        // However, we need to prevent redirect loop if we just continue.
        // For now, we'll let it continue to load the page and show error there if possible, 
        // or just die with error.
        // Better: Set a variable to show error later.
        $errorMsg = $message;
    }
}

// Get history data
$allRiwayat = $riwayat->getByPegawai($pegawaiId);

// NOW load the view
$pageTitle = 'Riwayat Kepegawaian';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">
    
    <?php if (isset($errorMsg)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($errorMsg) ?></span>
        </div>
    <?php endif; ?>

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kepegawaian/index.php" class="hover:text-blue-600">Kepegawaian</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kepegawaian/pegawai.php" class="hover:text-blue-600">Data Pegawai</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Riwayat</span>
        </nav>
        
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    Riwayat Kepegawaian
                </h2>
                <p class="text-gray-600 mt-2">
                    Kelola riwayat pendidikan, jabatan, dan pangkat untuk <strong><?= htmlspecialchars($dataPegawai['nama_lengkap']) ?></strong>
                </p>
            </div>
            <div class="flex gap-2">
                <a href="/e-TU/modules/kepegawaian/pegawai.php" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <button onclick="openModalAdd()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Tambah Riwayat
                </button>
            </div>
        </div>
    </div>

    <!-- Timeline View -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <?php if (count($allRiwayat) > 0): ?>
            <div class="relative border-l-2 border-gray-200 ml-3 space-y-8">
                <?php foreach ($allRiwayat as $r): ?>
                    <div class="relative pl-8">
                        <!-- Timeline Dot -->
                        <div class="absolute -left-2.5 top-0 w-5 h-5 rounded-full border-4 border-white 
                            <?= match($r['jenis_riwayat']) {
                                'Pendidikan' => 'bg-blue-500',
                                'Jabatan' => 'bg-green-500',
                                'Pangkat' => 'bg-purple-500',
                                'Penghargaan' => 'bg-yellow-500',
                                'Sanksi' => 'bg-red-500',
                                default => 'bg-gray-500'
                            } ?>">
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider 
                                        <?= match($r['jenis_riwayat']) {
                                            'Pendidikan' => 'text-blue-600',
                                            'Jabatan' => 'text-green-600',
                                            'Pangkat' => 'text-purple-600',
                                            'Penghargaan' => 'text-yellow-600',
                                            'Sanksi' => 'text-red-600',
                                            default => 'text-gray-600'
                                        } ?>">
                                        <?= $r['jenis_riwayat'] ?>
                                    </span>
                                    <h3 class="text-lg font-bold text-gray-800 mt-1"><?= htmlspecialchars($r['judul']) ?></h3>
                                    <?php if ($r['instansi_lokasi']): ?>
                                        <p class="text-gray-600 text-sm"><i class="fas fa-building mr-1"></i><?= htmlspecialchars($r['instansi_lokasi']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="flex gap-4 mt-2 text-sm text-gray-500">
                                        <?php if ($r['tahun_mulai']): ?>
                                            <span><i class="far fa-calendar-alt mr-1"></i><?= $r['tahun_mulai'] ?> - <?= $r['tahun_selesai'] ?: 'Sekarang' ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if ($r['nomor_sk']): ?>
                                            <span><i class="fas fa-file-contract mr-1"></i>SK: <?= htmlspecialchars($r['nomor_sk']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($r['keterangan']): ?>
                                        <p class="text-gray-600 text-sm mt-2 italic">"<?= htmlspecialchars($r['keterangan']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex gap-2">
                                    <button onclick='editRiwayat(<?= json_encode($r) ?>)' class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteRiwayat(<?= $r['id'] ?>)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-history text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">Belum ada riwayat</h3>
                <p class="text-gray-500">Tambahkan riwayat pendidikan, jabatan, atau pangkat untuk pegawai ini.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- Modal Add/Edit -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Tambah Riwayat</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formRiwayat">
            <input type="hidden" name="action" id="formAction" value="create">
            <!-- ID Riwayat (untuk edit/delete) -->
            <input type="hidden" name="riwayat_id" id="riwayatId" value="">
            <!-- ID Pegawai (untuk create) -->
            <input type="hidden" name="pegawai_id" value="<?= $pegawaiId ?>">
            <!-- ID Pegawai (untuk redirect/URL) -->
            <input type="hidden" name="id" value="<?= $pegawaiId ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Riwayat *</label>
                    <select name="jenis_riwayat" id="jenis_riwayat" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih...</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Jabatan">Jabatan</option>
                        <option value="Pangkat">Pangkat/Golongan</option>
                        <option value="Pelatihan">Pelatihan/Workshop</option>
                        <option value="Penghargaan">Penghargaan</option>
                        <option value="Sanksi">Sanksi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul / Nama *</label>
                    <input type="text" name="judul" id="judul" required placeholder="Contoh: S1 Teknik Informatika"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Instansi / Lokasi</label>
                    <input type="text" name="instansi_lokasi" id="instansi_lokasi" placeholder="Contoh: Universitas Indonesia"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Mulai</label>
                    <input type="number" name="tahun_mulai" id="tahun_mulai" placeholder="YYYY" min="1900" max="2100"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun Selesai</label>
                    <input type="number" name="tahun_selesai" id="tahun_selesai" placeholder="YYYY (Kosongkan jika sekarang)" min="1900" max="2100"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor SK</label>
                    <input type="text" name="nomor_sk" id="nomor_sk"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal SK</label>
                    <input type="date" name="tanggal_sk" id="tanggal_sk"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
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

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function openModalAdd() {
        document.getElementById('modalTitle').textContent = 'Tambah Riwayat';
        document.getElementById('formAction').value = 'create';
        document.getElementById('riwayatId').value = '';
        document.getElementById('formRiwayat').reset();
        // Ensure hidden inputs are correct
        document.querySelector('input[name="pegawai_id"]').value = '<?= $pegawaiId ?>';
        document.querySelector('input[name="id"]').value = '<?= $pegawaiId ?>';
        openModal('modalAdd');
    }

    function editRiwayat(data) {
        document.getElementById('modalTitle').textContent = 'Edit Riwayat';
        document.getElementById('formAction').value = 'update';
        document.getElementById('riwayatId').value = data.id;
        
        document.getElementById('jenis_riwayat').value = data.jenis_riwayat;
        document.getElementById('judul').value = data.judul;
        document.getElementById('instansi_lokasi').value = data.instansi_lokasi || '';
        document.getElementById('tahun_mulai').value = data.tahun_mulai || '';
        document.getElementById('tahun_selesai').value = data.tahun_selesai || '';
        document.getElementById('nomor_sk').value = data.nomor_sk || '';
        document.getElementById('tanggal_sk').value = data.tanggal_sk || '';
        document.getElementById('keterangan').value = data.keterangan || '';
        
        openModal('modalAdd');
    }

    function deleteRiwayat(id) {
        if (confirm('Apakah Anda yakin ingin menghapus riwayat ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="riwayat_id" value="${id}">
            <input type="hidden" name="id" value="<?= $pegawaiId ?>">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
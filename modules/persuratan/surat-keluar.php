<?php
$pageTitle = 'Surat Keluar - Persuratan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/SuratKeluar.php';
require_once __DIR__ . '/../../classes/TemplateSurat.php';

checkPermission('persuratan');

$suratKeluar = new SuratKeluar();
$templateSurat = new TemplateSurat();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $fileSurat = null;
                if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === 0) {
                    $uploadDir = __DIR__ . '/../../uploads/surat-keluar/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $fileName = time() . '_' . basename($_FILES['file_surat']['name']);
                    if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $uploadDir . $fileName)) {
                        $fileSurat = $fileName;
                    }
                }
                
                $result = $suratKeluar->create([
                    'nomor_surat' => $_POST['nomor_surat'],
                    'tanggal_surat' => $_POST['tanggal_surat'],
                    'tujuan' => $_POST['tujuan'],
                    'perihal' => $_POST['perihal'],
                    'template_id' => $_POST['template_id'] ?: null,
                    'file_surat' => $fileSurat,
                    'created_by' => $_SESSION['user_id'] ?? null
                ]);
                
                if ($result) {
                    $message = 'Surat keluar berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan surat keluar!';
                    $messageType = 'error';
                }
                break;
                
            case 'update':
                $fileSurat = $_POST['existing_file'] ?? null;
                if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] === 0) {
                    $uploadDir = __DIR__ . '/../../uploads/surat-keluar/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $fileName = time() . '_' . basename($_FILES['file_surat']['name']);
                    if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $uploadDir . $fileName)) {
                        $fileSurat = $fileName;
                    }
                }
                
                $result = $suratKeluar->update($_POST['id'], [
                    'nomor_surat' => $_POST['nomor_surat'],
                    'tanggal_surat' => $_POST['tanggal_surat'],
                    'tujuan' => $_POST['tujuan'],
                    'perihal' => $_POST['perihal'],
                    'template_id' => $_POST['template_id'] ?: null,
                    'file_surat' => $fileSurat
                ]);
                
                if ($result) {
                    $message = 'Surat keluar berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui surat keluar!';
                    $messageType = 'error';
                }
                break;
                
            case 'delete':
                if ($suratKeluar->delete($_POST['id'])) {
                    $message = 'Surat keluar berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus surat keluar!';
                    $messageType = 'error';
                }
                break;
        }
    }
}

// Get data
$suratList = $suratKeluar->getAll();
$templates = $templateSurat->getAll();
$totalSurat = $suratKeluar->getTotalCount();
$bulanIni = $suratKeluar->getCountThisMonth();
$nomorBaru = $suratKeluar->generateNomorSurat();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">
    
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/persuratan/index.php" class="hover:text-blue-600">Persuratan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Surat Keluar</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-paper-plane text-white text-xl"></i>
                    </div>
                    Surat Keluar
                </h2>
                <p class="text-gray-600 mt-2">Kelola dan arsipkan surat keluar</p>
            </div>
            <div class="flex gap-3">
                <a href="generator.php" class="px-5 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-magic mr-2"></i>Generate Surat
                </a>
                <button onclick="openModal('modalAdd')" class="px-5 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Tambah Manual
                </button>
            </div>
        </div>
    </div>
    
    <?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
        <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Surat Keluar</p>
                    <p class="text-3xl font-bold text-orange-600"><?= $totalSurat ?></p>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-paper-plane text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Bulan Ini</p>
                    <p class="text-3xl font-bold text-green-600"><?= $bulanIni ?></p>
                </div>
                <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Nomor Berikutnya</p>
                    <p class="text-lg font-bold text-blue-600 font-mono"><?= $nomorBaru ?></p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-hashtag text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Surat Keluar</h3>
            <div class="flex gap-2">
                <input type="text" id="searchInput" placeholder="Cari nomor/tujuan/perihal..." 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 w-64">
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">No</th>
                        <th class="text-left">Nomor Surat</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-left">Tujuan</th>
                        <th class="text-left">Perihal</th>
                        <th class="text-left">File</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suratList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-paper-plane text-6xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-semibold">Belum ada surat keluar</p>
                                <p class="text-sm">Klik "Generate Surat" atau "Tambah Manual" untuk membuat surat</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($suratList as $i => $surat): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded"><?= htmlspecialchars($surat['nomor_surat']) ?></span></td>
                        <td><?= date('d/m/Y', strtotime($surat['tanggal_surat'])) ?></td>
                        <td><?= htmlspecialchars($surat['tujuan']) ?></td>
                        <td class="max-w-xs truncate"><?= htmlspecialchars($surat['perihal']) ?></td>
                        <td>
                            <?php if ($surat['file_surat']): ?>
                            <a href="/e-TU/uploads/surat-keluar/<?= $surat['file_surat'] ?>" target="_blank" class="text-blue-600 hover:underline">
                                <i class="fas fa-file-pdf mr-1"></i>Lihat
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <button onclick='editSurat(<?= json_encode($surat) ?>)' class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 mr-1">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteSurat(<?= $surat['id'] ?>)" class="px-3 py-1 bg-red-100 text-red-600 rounded-lg hover:bg-red-200">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
            <h3 class="text-2xl font-bold text-gray-800">Tambah Surat Keluar</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Surat *</label>
                    <input type="text" name="nomor_surat" value="<?= $nomorBaru ?>" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Surat *</label>
                    <input type="date" name="tanggal_surat" value="<?= date('Y-m-d') ?>" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tujuan *</label>
                    <input type="text" name="tujuan" required placeholder="Kepada siapa surat ditujukan..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perihal *</label>
                    <input type="text" name="perihal" required placeholder="Isi perihal surat..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Template</label>
                    <select name="template_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">-- Pilih Template --</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama_template']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload File</label>
                    <input type="file" name="file_surat" accept=".pdf,.doc,.docx"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>
            
            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-orange-600 to-orange-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Edit Surat Keluar</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="formEdit">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="existing_file" id="edit_existing_file">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Surat *</label>
                    <input type="text" name="nomor_surat" id="edit_nomor_surat" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Surat *</label>
                    <input type="date" name="tanggal_surat" id="edit_tanggal_surat" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tujuan *</label>
                    <input type="text" name="tujuan" id="edit_tujuan" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Perihal *</label>
                    <input type="text" name="perihal" id="edit_perihal" required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Template</label>
                    <select name="template_id" id="edit_template_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="">-- Pilih Template --</option>
                        <?php foreach ($templates as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nama_template']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ganti File</label>
                    <input type="file" name="file_surat" accept=".pdf,.doc,.docx"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1" id="current_file_info"></p>
                </div>
            </div>
            
            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalEdit')" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<form method="POST" id="formDelete">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

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

function editSurat(surat) {
    document.getElementById('edit_id').value = surat.id;
    document.getElementById('edit_nomor_surat').value = surat.nomor_surat;
    document.getElementById('edit_tanggal_surat').value = surat.tanggal_surat;
    document.getElementById('edit_tujuan').value = surat.tujuan;
    document.getElementById('edit_perihal').value = surat.perihal;
    document.getElementById('edit_template_id').value = surat.template_id || '';
    document.getElementById('edit_existing_file').value = surat.file_surat || '';
    document.getElementById('current_file_info').textContent = surat.file_surat ? 'File saat ini: ' + surat.file_surat : 'Belum ada file';
    openModal('modalEdit');
}

function deleteSurat(id) {
    if (confirm('Yakin ingin menghapus surat keluar ini?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('formDelete').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
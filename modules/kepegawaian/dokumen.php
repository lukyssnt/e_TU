<?php
$pageTitle = 'Dokumen Pegawai';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Pegawai.php';
require_once __DIR__ . '/../../classes/DokumenPegawai.php';

// Check permission
checkPermission('kepegawaian');

$pegawai = new Pegawai();
$dokumen = new DokumenPegawai();

// Get pegawai ID
$pegawaiId = $_GET['id'] ?? null;
if (!$pegawaiId) {
    redirect('/e-TU/modules/kepegawaian/pegawai.php', 'ID Pegawai tidak valid', 'error');
}

// Get pegawai data
$dataPegawai = $pegawai->getById($pegawaiId);
if (!$dataPegawai) {
    redirect('/e-TU/modules/kepegawaian/pegawai.php', 'Data pegawai tidak ditemukan', 'error');
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    try {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $jenis = $_POST['jenis_dokumen'];
            $namaDokumen = $_POST['nama_dokumen'] ?? null;
            $result = $dokumen->upload($pegawaiId, $jenis, $_FILES['file'], $namaDokumen);

            if ($result['success']) {
                redirect($_SERVER['REQUEST_URI'], $result['message'], 'success');
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        } else {
            $message = 'Pilih file terlebih dahulu';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        if ($dokumen->delete($_POST['id'])) {
            redirect($_SERVER['REQUEST_URI'], 'Dokumen berhasil dihapus', 'success');
        } else {
            $message = 'Gagal menghapus dokumen';
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get documents
$documents = $dokumen->getByPegawai($pegawaiId);
$kelengkapan = $dokumen->getKelengkapan($pegawaiId);
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kepegawaian/index.php" class="hover:text-blue-600">Kepegawaian</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kepegawaian/pegawai.php" class="hover:text-blue-600">Data Pegawai</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Dokumen</span>
        </nav>

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-folder-open text-white text-xl"></i>
                    </div>
                    Dokumen Pegawai
                </h2>
                <p class="text-gray-600 mt-2">
                    Kelola dokumen untuk <strong><?= htmlspecialchars($dataPegawai['nama_lengkap']) ?></strong> (NIP:
                    <?= htmlspecialchars($dataPegawai['nip'] ?? '-') ?>)
                </p>
            </div>
            <a href="/e-TU/modules/kepegawaian/pegawai.php"
                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Kelengkapan Dokumen -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Kelengkapan Dokumen Wajib</h3>
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div class="bg-blue-600 h-4 rounded-full transition-all duration-500"
                        style="width: <?= $kelengkapan['percentage'] ?>%"></div>
                </div>
            </div>
            <span class="font-bold text-blue-600"><?= $kelengkapan['percentage'] ?>%</span>
        </div>
        <p class="text-sm text-gray-600 mt-2">
            <?= $kelengkapan['complete'] ?> dari <?= $kelengkapan['required'] ?> dokumen wajib terpenuhi.
        </p>
    </div>

    <!-- Upload Form -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Upload Dokumen Baru</h3>
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="action" value="upload">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Dokumen</label>
                <select name="jenis_dokumen" id="jenis_dokumen" required onchange="toggleNamaDokumen()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Jenis --</option>
                    <?php foreach (DokumenPegawai::$jenisDoc as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="namaDokumenContainer" class="hidden">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="nama_dokumen" id="nama_dokumen" placeholder="Contoh: Sertifikat Pelatihan IT, dll"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">File (PDF/JPG/PNG, Max 5MB)</label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <button type="submit"
                    class="w-full px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800">
                    <i class="fas fa-upload mr-2"></i>Upload
                </button>
            </div>
        </form>
    </div>

    <script>
        function toggleNamaDokumen() {
            const jenisSelect = document.getElementById('jenis_dokumen');
            const namaContainer = document.getElementById('namaDokumenContainer');
            const namaInput = document.getElementById('nama_dokumen');
            
            if (jenisSelect.value === 'lainnya') {
                namaContainer.classList.remove('hidden');
                namaInput.setAttribute('required', 'required');
            } else {
                namaContainer.classList.add('hidden');
                namaInput.removeAttribute('required');
                namaInput.value = '';
            }
        }
    </script>

    <!-- Document List -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php 
        // Separate lainnya from other documents
        $lainnyaDocs = [];
        $otherDocs = [];
        foreach ($documents as $d) {
            if ($d['jenis_dokumen'] === 'lainnya') {
                $lainnyaDocs[] = $d;
            } else {
                $otherDocs[$d['jenis_dokumen']] = $d;
            }
        }
        
        // Display regular documents
        foreach (DokumenPegawai::$jenisDoc as $key => $label): 
            if ($key === 'lainnya') continue; // Skip lainnya for now
            $doc = $otherDocs[$key] ?? null;
        ?>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 <?= $doc ? 'border-green-500' : 'border-gray-300' ?>">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800"><?= $label ?></h4>
                        <?php if ($doc): ?>
                            <span class="text-xs text-green-600 font-semibold bg-green-100 px-2 py-1 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Tersedia
                            </span>
                        <?php else: ?>
                            <span class="text-xs text-gray-500 font-semibold bg-gray-100 px-2 py-1 rounded-full">
                                <i class="fas fa-times-circle mr-1"></i>Belum ada
                            </span>
                        <?php endif; ?>
                    </div>
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center <?= $doc ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-400' ?>">
                        <i class="fas <?= $doc ? 'fa-file-alt' : 'fa-file' ?> text-xl"></i>
                    </div>
                </div>

                <?php if ($doc): ?>
                    <div class="text-sm text-gray-600 mb-3">
                        <p class="truncate" title="<?= htmlspecialchars($doc['nama_file']) ?>">
                            <i class="fas fa-paperclip mr-1"></i><?= htmlspecialchars($doc['nama_file']) ?>
                        </p>
                        <p class="text-xs mt-1">
                            <i class="far fa-clock mr-1"></i><?= date('d M Y H:i', strtotime($doc['updated_at'])) ?>
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="/e-TU/uploads/dokumen-pegawai/<?= $pegawaiId ?>/<?= $doc['nama_file'] ?>" target="_blank"
                            class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold text-center hover:bg-blue-100">
                            <i class="fas fa-eye mr-1"></i>Lihat
                        </a>
                        <form method="POST" onsubmit="return confirm('Hapus dokumen ini?')" class="inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                            <button type="submit"
                                class="px-3 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-100">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-400 italic mb-4">Belum ada dokumen diupload.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <?php 
        // Display lainnya documents separately
        if (count($lainnyaDocs) > 0): 
            foreach ($lainnyaDocs as $doc): 
        ?>
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-500">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($doc['nama_dokumen'] ?? 'Dokumen Lainnya') ?></h4>
                        <span class="text-xs text-purple-600 font-semibold bg-purple-100 px-2 py-1 rounded-full">
                            <i class="fas fa-check-circle mr-1"></i>Dokumen Lain
                        </span>
                    </div>
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-purple-100 text-purple-600">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                </div>

                <div class="text-sm text-gray-600 mb-3">
                    <p class="truncate" title="<?= htmlspecialchars($doc['nama_file']) ?>">
                        <i class="fas fa-paperclip mr-1"></i><?= htmlspecialchars($doc['nama_file']) ?>
                    </p>
                    <p class="text-xs mt-1">
                        <i class="far fa-clock mr-1"></i><?= date('d M Y H:i', strtotime($doc['updated_at'])) ?>
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="/e-TU/uploads/dokumen-pegawai/<?= $pegawaiId ?>/<?= $doc['nama_file'] ?>" target="_blank"
                        class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold text-center hover:bg-blue-100">
                        <i class="fas fa-eye mr-1"></i>Lihat
                    </a>
                    <form method="POST" onsubmit="return confirm('Hapus dokumen ini?')" class="inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                        <button type="submit"
                            class="px-3 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-semibold hover:bg-red-100">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php 
            endforeach;
        endif; 
        ?>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
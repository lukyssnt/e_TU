<?php
$pageTitle = 'Dokumentasi Kegiatan - Kehumasan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Agenda.php';
require_once __DIR__ . '/../../classes/Dokumentasi.php';

checkPermission('kehumasan');

$agenda = new Agenda();
$dokumentasi = new Dokumentasi();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'upload') {
            if (!isset($_POST['agenda_id']) || empty($_POST['agenda_id'])) {
                throw new Exception('Pilih kegiatan terlebih dahulu');
            }

            if (!isset($_FILES['foto']) || $_FILES['foto']['error'][0] === UPLOAD_ERR_NO_FILE) {
                throw new Exception('Pilih minimal 1 foto untuk diupload');
            }

            $result = $dokumentasi->upload(
                $_POST['agenda_id'],
                $_FILES['foto'],
                clean($_POST['keterangan']) ?? null
            );

            if ($result['success']) {
                redirect($_SERVER['PHP_SELF'], $result['message'], 'success');
            } else {
                throw new Exception($result['message']);
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($dokumentasi->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Dokumentasi berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$allAgenda = $agenda->getAll();
$allDokumentasi = $dokumentasi->getAll();

// Group dokumentasi by agenda
$docsByAgenda = [];
foreach ($allDokumentasi as $doc) {
    $agendaId = $doc['agenda_id'];
    if (!isset($docsByAgenda[$agendaId])) {
        $docsByAgenda[$agendaId] = [
            'agenda' => [
                'judul' => $doc['agenda_judul'],
                'tanggal' => $doc['tanggal_mulai']
            ],
            'docs' => []
        ];
    }
    $docsByAgenda[$agendaId]['docs'][] = $doc;
}
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kehumasan/index.php" class="hover:text-blue-600">Kehumasan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Dokumentasi</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-images text-white text-xl"></i>
                    </div>
                    Dokumentasi Kegiatan
                </h2>
                <p class="text-gray-600 mt-2">Kelola foto dokumentasi kegiatan sekolah</p>
            </div>
            <?php if (count($allAgenda) > 0): ?>
                <button onclick="openModal('modalUpload')"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-violet-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-upload mr-2"></i>Upload Foto
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info -->
    <?php if (count($allAgenda) === 0): ?>
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-amber-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        <strong>Perhatian:</strong> Belum ada kegiatan/agenda.
                        <a href="agenda.php" class="underline font-semibold">Tambah agenda terlebih dahulu</a> sebelum
                        upload dokumentasi.
                    </p>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Info:</strong> Maksimal <strong>3 foto</strong> per kegiatan, ukuran maksimal
                        <strong>5MB</strong> per foto.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total Foto</p>
            <p class="text-2xl font-bold text-purple-600"><?= count($allDokumentasi) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Kegiatan Terdokumentasi</p>
            <p class="text-2xl font-bold text-blue-600"><?= count($docsByAgenda) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total Kegiatan</p>
            <p class="text-2xl font-bold text-green-600"><?= count($allAgenda) ?></p>
        </div>
    </div>

    <!-- Gallery by Agenda -->
    <div class="space-y-6">
        <?php if (count($docsByAgenda) > 0): ?>
            <?php foreach ($docsByAgenda as $agendaId => $group): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($group['agenda']['judul']) ?></h3>
                            <p class="text-sm text-gray-500"><?= formatTanggal($group['agenda']['tanggal'], 'long') ?></p>
                        </div>
                        <span class="text-sm text-gray-500"><?= count($group['docs']) ?>/3 foto</span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach ($group['docs'] as $doc): ?>
                            <div class="relative group">
                                <img src="/e-TU/<?= htmlspecialchars($doc['file_path']) ?>" alt="Dokumentasi"
                                    class="w-full h-40 object-cover rounded-lg cursor-pointer hover:opacity-90"
                                    onclick="viewImage('/e-TU/<?= htmlspecialchars($doc['file_path']) ?>')">
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="deleteDoc(<?= $doc['id'] ?>)"
                                        class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                                <?php if ($doc['keterangan']): ?>
                                    <p class="text-xs text-gray-500 mt-1 truncate"><?= htmlspecialchars($doc['keterangan']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <p class="text-lg font-semibold">Belum ada dokumentasi</p>
                    <?php if (count($allAgenda) > 0): ?>
                        <p class="text-sm text-gray-500">Klik "Upload Foto" untuk menambahkan dokumentasi kegiatan</p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">Tambah agenda kegiatan terlebih dahulu</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Upload -->
<div id="modalUpload" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Upload Dokumentasi</h3>
            <button onclick="closeModal('modalUpload')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kegiatan *</label>
                    <select name="agenda_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg"
                        onchange="checkAgendaLimit(this.value)">
                        <option value="">Pilih kegiatan...</option>
                        <?php foreach ($allAgenda as $a): ?>
                            <?php $docCount = $dokumentasi->countByAgenda($a['id']); ?>
                            <option value="<?= $a['id'] ?>" data-count="<?= $docCount ?>" <?= $docCount >= 3 ? 'disabled' : '' ?>>
                                <?= htmlspecialchars($a['judul']) ?> (<?= formatTanggal($a['tanggal_mulai'], 'short') ?>)
                                <?= $docCount >= 3 ? ' - Penuh' : " - $docCount/3 foto" ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Foto * (Maks. 3 foto, 5MB per
                        foto)</label>
                    <input type="file" name="foto[]" accept="image/*" multiple required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg" onchange="validateFiles(this)">
                    <p id="fileInfo" class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, WebP</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="2" placeholder="Keterangan foto (opsional)..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalUpload')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-violet-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-upload mr-2"></i>Upload
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View Image -->
<div id="modalView" class="modal-overlay" onclick="closeModal('modalView')">
    <div class="flex items-center justify-center min-h-screen p-4">
        <img id="viewImage" src="" alt="Preview" class="max-w-full max-h-[80vh] rounded-lg shadow-2xl">
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function validateFiles(input) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const files = input.files;
        let valid = true;
        let info = [];

        for (let i = 0; i < Math.min(files.length, 3); i++) {
            if (files[i].size > maxSize) {
                info.push(files[i].name + ' melebihi 5MB');
                valid = false;
            }
        }

        if (files.length > 3) {
            info.push('Hanya 3 foto pertama yang akan diupload');
        }

        document.getElementById('fileInfo').textContent = info.length > 0 ? info.join(', ') : files.length + ' file dipilih';
        document.getElementById('fileInfo').className = valid ? 'text-xs text-green-600 mt-1' : 'text-xs text-red-500 mt-1';
    }

    function checkAgendaLimit(agendaId) {
        const select = document.querySelector('select[name="agenda_id"]');
        const option = select.querySelector(`option[value="${agendaId}"]`);
        if (option) {
            const count = parseInt(option.dataset.count || 0);
            const remaining = 3 - count;
            if (remaining > 0) {
                document.getElementById('fileInfo').textContent = `Bisa upload maksimal ${remaining} foto lagi`;
            }
        }
    }

    function viewImage(src) {
        document.getElementById('viewImage').src = src;
        openModal('modalView');
    }

    function deleteDoc(id) {
        if (confirm('Hapus foto ini?')) {
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
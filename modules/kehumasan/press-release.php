<?php
$pageTitle = 'Press Release - Kehumasan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/PressRelease.php';

checkPermission('kehumasan');

$pressRelease = new PressRelease();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $gambarPath = null;
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['gambar'], 'uploads/press-release/', ['jpg', 'jpeg', 'png', 'webp']);
                if ($upload['success']) {
                    $gambarPath = $upload['path'];
                }
            }

            $data = [
                'judul' => clean($_POST['judul']),
                'ringkasan' => clean($_POST['ringkasan']) ?? null,
                'isi' => $_POST['isi'], // Allow HTML for rich content
                'kategori' => $_POST['kategori'],
                'tanggal_rilis' => $_POST['tanggal_rilis'],
                'penulis' => clean($_POST['penulis']) ?? null,
                'gambar' => $gambarPath,
                'status' => $_POST['status']
            ];

            if ($pressRelease->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Press Release berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'update_status') {
            if ($pressRelease->updateStatus($_POST['id'], $_POST['status'])) {
                redirect($_SERVER['PHP_SELF'], 'Status berhasil diupdate!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($pressRelease->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Press Release berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Filter
$filterStatus = $_GET['status'] ?? '';
$allPR = $pressRelease->getAll($filterStatus ?: null);
$statusCounts = $pressRelease->getStatusCounts();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/kehumasan/index.php" class="hover:text-blue-600">Kehumasan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Press Release</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-newspaper text-white text-xl"></i>
                    </div>
                    Press Release
                </h2>
                <p class="text-gray-600 mt-2">Kelola publikasi berita dan press release sekolah</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Buat Press Release
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total</p>
            <p class="text-2xl font-bold text-emerald-600"><?= count($allPR) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Draft</p>
            <p class="text-2xl font-bold text-gray-600"><?= $statusCounts['Draft'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Dipublikasi</p>
            <p class="text-2xl font-bold text-green-600"><?= $statusCounts['Dipublikasi'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Diarsipkan</p>
            <p class="text-2xl font-bold text-amber-600"><?= $statusCounts['Diarsipkan'] ?></p>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <form method="GET" class="flex items-center gap-4 flex-wrap">
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Semua Status</option>
                    <option value="Draft" <?= $filterStatus === 'Draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="Dipublikasi" <?= $filterStatus === 'Dipublikasi' ? 'selected' : '' ?>>Dipublikasi
                    </option>
                    <option value="Diarsipkan" <?= $filterStatus === 'Diarsipkan' ? 'selected' : '' ?>>Diarsipkan</option>
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold">
                <i class="fas fa-filter mr-2"></i>Filter
            </button>
            <input type="text" id="searchInput" placeholder="Cari..."
                class="px-4 py-2 border border-gray-300 rounded-lg">
        </form>
    </div>

    <!-- Press Release Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (count($allPR) > 0): ?>
            <?php foreach ($allPR as $pr): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden card-item" data-status="<?= $pr['status'] ?>">
                    <?php if ($pr['gambar']): ?>
                        <img src="/e-TU/<?= htmlspecialchars($pr['gambar']) ?>" alt="Cover" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center">
                            <i class="fas fa-newspaper text-white text-5xl opacity-50"></i>
                        </div>
                    <?php endif; ?>

                    <div class="p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs px-2 py-1 bg-gray-100 rounded"><?= htmlspecialchars($pr['kategori']) ?></span>
                            <span class="badge badge-<?=
                                $pr['status'] === 'Dipublikasi' ? 'success' :
                                ($pr['status'] === 'Diarsipkan' ? 'warning' : 'secondary')
                                ?>">
                                <?= $pr['status'] ?>
                            </span>
                        </div>

                        <h3 class="font-bold text-lg text-gray-800 mb-2 line-clamp-2"><?= htmlspecialchars($pr['judul']) ?></h3>

                        <?php if ($pr['ringkasan']): ?>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= htmlspecialchars($pr['ringkasan']) ?></p>
                        <?php endif; ?>

                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                            <span><i class="fas fa-calendar mr-1"></i><?= formatTanggal($pr['tanggal_rilis'], 'short') ?></span>
                            <?php if ($pr['penulis']): ?>
                                <span><i class="fas fa-user mr-1"></i><?= htmlspecialchars($pr['penulis']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2">
                            <button onclick="viewDetail(<?= $pr['id'] ?>)"
                                class="flex-1 px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm">
                                <i class="fas fa-eye mr-1"></i>Lihat
                            </button>

                            <?php if ($pr['status'] === 'Draft'): ?>
                                <button onclick="updateStatus(<?= $pr['id'] ?>, 'Dipublikasi')"
                                    class="px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-sm" title="Publikasi">
                                    <i class="fas fa-globe"></i>
                                </button>
                            <?php elseif ($pr['status'] === 'Dipublikasi'): ?>
                                <button onclick="updateStatus(<?= $pr['id'] ?>, 'Diarsipkan')"
                                    class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded text-sm" title="Arsipkan">
                                    <i class="fas fa-archive"></i>
                                </button>
                            <?php endif; ?>

                            <button onclick="deletePR(<?= $pr['id'] ?>)"
                                class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded text-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <p class="text-lg font-semibold">Belum ada press release</p>
                    <p class="text-sm text-gray-500">Klik "Buat Press Release" untuk membuat publikasi baru</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Add -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Buat Press Release</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul *</label>
                    <input type="text" name="judul" required placeholder="Judul press release..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori</label>
                    <select name="kategori" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="Umum">Umum</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Prestasi">Prestasi</option>
                        <option value="Kegiatan">Kegiatan</option>
                        <option value="Pengumuman">Pengumuman</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Rilis *</label>
                    <input type="date" name="tanggal_rilis" required value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Penulis</label>
                    <input type="text" name="penulis" placeholder="Nama penulis..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <option value="Draft">Draft</option>
                        <option value="Dipublikasi">Dipublikasi</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ringkasan</label>
                    <textarea name="ringkasan" rows="2" placeholder="Ringkasan singkat..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Isi/Konten *</label>
                    <textarea name="isi" rows="8" required placeholder="Tulis isi press release di sini..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar Cover</label>
                    <input type="file" name="gambar" accept="image/*"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, WebP. Maks 5MB</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div id="modalDetail" class="modal-overlay">
    <div class="modal-content max-w-3xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800" id="detailTitle">Detail Press Release</h3>
            <button onclick="closeModal('modalDetail')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div id="detailContent" class="prose max-w-none">
            <!-- Content loaded by JS -->
        </div>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    // Search
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.card-item');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Store PR data for detail view
    const prData = <?= json_encode($allPR) ?>;

    function viewDetail(id) {
        const pr = prData.find(p => p.id == id);
        if (pr) {
            document.getElementById('detailTitle').textContent = pr.judul;
            document.getElementById('detailContent').innerHTML = `
            <div class="mb-4 flex items-center gap-4 text-sm text-gray-500">
                <span><i class="fas fa-calendar mr-1"></i>${pr.tanggal_rilis}</span>
                <span><i class="fas fa-folder mr-1"></i>${pr.kategori}</span>
                ${pr.penulis ? `<span><i class="fas fa-user mr-1"></i>${pr.penulis}</span>` : ''}
            </div>
            ${pr.gambar ? `<img src="/e-TU/${pr.gambar}" alt="Cover" class="w-full rounded-lg mb-4">` : ''}
            ${pr.ringkasan ? `<p class="text-gray-600 italic mb-4">${pr.ringkasan}</p>` : ''}
            <div class="whitespace-pre-wrap">${pr.isi}</div>
        `;
            openModal('modalDetail');
        }
    }

    function updateStatus(id, status) {
        const statusLabel = status === 'Dipublikasi' ? 'publikasi' : 'arsipkan';
        if (confirm(`Yakin ingin ${statusLabel} press release ini?`)) {
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

    function deletePR(id) {
        if (confirm('Hapus press release ini?')) {
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

<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
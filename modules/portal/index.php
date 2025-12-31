<?php
$pageTitle = 'Portal Informasi';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Portal.php';

// checkPermission('admin'); // Only super admin/admin

$portal = new Portal();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Handle Text Content
    if (isset($_POST['content'])) {
        foreach ($_POST['content'] as $key => $value) {
            $portal->updateContent($key, $value);
        }
    }

    // 2. Handle File Uploads
    if (isset($_FILES['images'])) {
        foreach ($_FILES['images']['name'] as $key => $fileName) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$key],
                    'type' => $_FILES['images']['type'][$key],
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];

                $uploadPath = $portal->uploadImage($file);
                if ($uploadPath) {
                    $portal->updateContent($key, $uploadPath);
                }
            }
        }
    }

    $message = "Konten berhasil diperbarui!";
    $messageType = "success";
}

$contents = $portal->getAllContent();
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
                <i class="fas fa-globe text-white text-xl"></i>
            </div>
            Portal Informasi
        </h2>
        <p class="text-gray-600 mt-2">Kelola konten landing page publik</p>
    </div>

    <!-- Submenu Tabs -->
    <div class="flex gap-4 mb-6 border-b border-gray-200 pb-2">
        <a href="index.php" class="px-4 py-2 border-b-2 border-gray-800 text-gray-800 font-semibold">Konten</a>
        <a href="buku-tamu.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Buku Tamu</a>
        <a href="alumni.php" class="px-4 py-2 text-gray-600 hover:text-gray-800">Layanan Alumni</a>
    </div>

    <?php if (isset($message)): ?>
        <div class="mb-6 p-4 rounded-lg bg-green-100 text-green-800 border border-green-300">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="POST" enctype="multipart/form-data">
            <h3 class="text-lg font-bold mb-4">Edit Konten Landing Page</h3>

            <div class="grid grid-cols-1 gap-6">
                <?php foreach ($contents as $key => $data): ?>
                    <div>
                        <?php
                        $label = ucwords(str_replace('_', ' ', $key));
                        if ($key === 'finance_feature_status') {
                            $label = 'Status Layanan Cek Tagihan (Publik)';
                        } elseif ($key === 'hero_title') {
                            $label = 'Judul Utama (Hero)';
                        } elseif ($key === 'about_text') {
                            $label = 'Deskripsi Tentang Sekolah';
                        }
                        ?>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <?= $label ?>
                            <?php if ($key === 'finance_feature_status'): ?>
                                <span class="block text-xs font-normal text-gray-500 mt-1">
                                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                    Atur ke <strong>"Dibuka / Aktif"</strong> agar wali murid bisa mengecek tagihan di halaman
                                    depan.
                                    Pilih <strong>"Ditutup"</strong> jika sedang ada maintenance atau tidak ingin menampilkan
                                    fitur ini.
                                </span>
                            <?php endif; ?>
                        </label>

                        <?php if ($data['input_type'] === 'textarea'): ?>
                            <textarea name="content[<?= $key ?>]" rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg"><?= htmlspecialchars($data['content_value']) ?></textarea>

                        <?php elseif ($data['input_type'] === 'image'): ?>
                            <div class="flex items-center gap-4">
                                <?php if (!empty($data['content_value'])): ?>
                                    <img src="<?= (strpos($data['content_value'], 'http') === 0) ? $data['content_value'] : '/e-TU/' . $data['content_value'] ?>"
                                        class="w-32 h-20 object-cover rounded-lg border border-gray-200">
                                <?php endif; ?>
                                <input type="file" name="images[<?= $key ?>]" accept="image/*"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                            </div>
                            <!-- Hidden input to keep old value if no new file -->
                            <input type="hidden" name="content[<?= $key ?>]"
                                value="<?= htmlspecialchars($data['content_value']) ?>">

                        <?php elseif ($key === 'finance_feature_status'): ?>
                            <!-- Toggle Switch for Finance Status -->
                            <div class="flex items-center mt-2">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="content[<?= $key ?>]" value="0"> <!-- Fallback for unchecked -->
                                    <input type="checkbox" name="content[<?= $key ?>]" value="1" class="sr-only peer"
                                        <?= $data['content_value'] == '1' ? 'checked' : '' ?>>
                                    <div
                                        class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600">
                                    </div>
                                    <span class="ml-3 text-sm font-medium text-gray-900 status-text">
                                        <?= $data['content_value'] == '1' ? 'Layanan AKTIF' : 'Layanan NON-AKTIF' ?>
                                    </span>
                                </label>
                            </div>
                            <script>
                                document.querySelector('input[name="content[<?= $key ?>]"]').addEventListener('change', function () {
                                    this.parentElement.querySelector('.status-text').textContent = this.checked ? 'Layanan AKTIF' : 'Layanan NON-AKTIF';
                                });
                            </script>

                        <?php elseif ($data['input_type'] === 'select'): ?>
                            <select name="content[<?= $key ?>]" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                                <option value="<?= htmlspecialchars($data['content_value']) ?>">
                                    <?= htmlspecialchars($data['content_value']) ?>
                                </option>
                            </select>

                        <?php else: ?>
                            <input type="text" name="content[<?= $key ?>]"
                                value="<?= htmlspecialchars($data['content_value']) ?>"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="px-6 py-3 bg-gray-800 hover:bg-gray-900 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
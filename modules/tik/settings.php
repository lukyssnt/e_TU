<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Setting.php';

// Check Permission First
if (!Session::isLoggedIn()) {
    header('Location: /e-TU/login.php');
    exit;
}
checkPermission('tik');

$settingObj = new Setting();

// Handle Backup Action (Must be before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'backup_db') {
    require_once __DIR__ . '/../../classes/DatabaseBackup.php';
    $backup = new DatabaseBackup();
    $sqlContent = $backup->generateBackup();

    $filename = 'e_admin_tu_backup_' . date('Y-m-d_H-i-s') . '.sql';

    // Clean output buffer to ensure no whitespace/errors are sent
    if (ob_get_level())
        ob_end_clean();

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($sqlContent));
    echo $sqlContent;
    exit;
}

// Handle Form Submission (Settings Update) - MOVED TO TOP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $data = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $realKey = substr($key, 8); // Remove 'setting_' prefix
            $data[$realKey] = clean($value);
        }
    }

    // Handle File Upload (Logo)
    if (isset($_FILES['setting_app_logo']) && $_FILES['setting_app_logo']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFile($_FILES['setting_app_logo'], 'assets/img/', ['jpg', 'jpeg', 'png']);
        if ($upload['success']) {
            $data['app_logo'] = $upload['path'];
        } else {
            Session::setFlash('error', $upload['message']);
        }
    }

    // Handle Checkbox (Maintenance Mode)
    $data['maintenance_mode'] = isset($_POST['setting_maintenance_mode']) ? '1' : '0';

    if ($settingObj->updateBatch($data)) {
        Session::setFlash('success', 'Pengaturan berhasil disimpan!');
        header('Location: settings.php');
        exit;
    } else {
        Session::setFlash('error', 'Gagal menyimpan pengaturan.');
    }
}

$pageTitle = 'Pengaturan Sistem';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';

$settings = $settingObj->getAll();
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s;
}
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Pengaturan Sistem</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-cogs text-white text-xl"></i>
                    </div>
                    Pengaturan Sistem
                </h2>
                <p class="text-gray-600 mt-2">Konfigurasi umum aplikasi</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <form method="POST" enctype="multipart/form-data">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Identitas Sekolah -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Identitas Sekolah & Aplikasi</h3>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Aplikasi</label>
                    <input type="text" name="setting_app_name"
                        value="<?= htmlspecialchars($settingsMap['app_name']['setting_value'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
                    <p class="text-xs text-gray-500 mt-1"><?= $settingsMap['app_name']['description'] ?? '' ?></p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Sekolah</label>
                    <input type="text" name="setting_school_name"
                        value="<?= htmlspecialchars($settingsMap['school_name']['setting_value'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Sekolah</label>
                    <textarea name="setting_school_address" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500"><?= htmlspecialchars($settingsMap['school_address']['setting_value'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="text" name="setting_school_phone"
                        value="<?= htmlspecialchars($settingsMap['school_phone']['setting_value'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Sekolah</label>
                    <input type="email" name="setting_school_email"
                        value="<?= htmlspecialchars($settingsMap['school_email']['setting_value'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500">
                </div>

                <!-- Logo & Lainnya -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Logo & Konfigurasi Lain</h3>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Logo Aplikasi</label>
                    <div class="flex items-center gap-4">
                        <?php if (!empty($settingsMap['app_logo']['setting_value'])): ?>
                            <img src="/e-TU/<?= htmlspecialchars($settingsMap['app_logo']['setting_value']) ?>" alt="Logo"
                                class="h-16 w-auto object-contain bg-gray-50 rounded p-1 border">
                        <?php endif; ?>
                        <input type="file" name="setting_app_logo" accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Upload file baru untuk mengganti (JPG/PNG, Max 2MB)</p>
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="setting_maintenance_mode" value="1"
                            <?= ($settingsMap['maintenance_mode']['setting_value'] ?? '0') == '1' ? 'checked' : '' ?>
                            class="w-5 h-5 text-red-600 border-gray-300 rounded focus:ring-red-500">
                        <span class="ml-3 text-gray-700 font-semibold">Aktifkan Mode Maintenance</span>
                    </label>
                </div>

                <!-- Backup Data -->
                <div class="md:col-span-2 mt-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Backup & Restore Data</h3>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-blue-800">Backup Database</h4>
                            <p class="text-sm text-blue-600">Unduh salinan penuh database (SQL) untuk keamanan data.</p>
                        </div>
                        <button type="submit" name="action" value="backup_db" formtarget="_parent"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md transition font-semibold">
                            <i class="fas fa-download mr-2"></i>Download Backup
                        </button>
                    </div>
                </div>

            </div>

            <div class="mt-8 flex justify-end border-t pt-6">
                <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-semibold shadow-lg transform hover:-translate-y-0.5 transition-all">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </div>

        </form>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
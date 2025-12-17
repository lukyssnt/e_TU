<?php
$pageTitle = 'Pengaturan Akademik - TIK';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/AcademicSettings.php';
require_once __DIR__ . '/../../classes/TahunAjaran.php';

checkPermission('admin');

$academicSettings = new AcademicSettings();
$tahunAjaran = new TahunAjaran();

$message = '';
$messageType = 'success';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        switch ($action) {
            case 'add_year':
                $tahunAjaran->create(
                    clean($_POST['tahun_ajaran']),
                    clean($_POST['tanggal_mulai']),
                    clean($_POST['tanggal_akhir'])
                );
                $message = 'Tahun ajaran berhasil ditambahkan!';
                break;

            case 'set_active':
                $tahunAjaran->setActive($_POST['year_id']);
                $message = 'Tahun ajaran aktif berhasil diperbarui!';
                break;

            case 'delete_year':
                $tahunAjaran->delete($_POST['year_id']);
                $message = 'Tahun ajaran berhasil dihapus!';
                break;

            case 'update_settings':
                foreach ($_POST['settings'] as $key => $value) {
                    $academicSettings->set($key, $value);
                }
                $message = 'Pengaturan akademik berhasil diperbarui!';
                break;
        }

        $messageType = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all settings and years
$settings = $academicSettings->getAll();
$academicYearOptions = $academicSettings->getAcademicYearOptions();
$allYears = $tahunAjaran->getAll();
?>

<main class="lg:ml-72 min-h-screen p-6">
    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/tik/index.php" class="hover:text-blue-600">TIK & Pengaturan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Pengaturan Akademik</span>
        </nav>

        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-purple-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-graduation-cap text-white text-xl"></i>
            </div>
            Pengaturan Akademik
        </h2>
        <p class="text-gray-600 mt-2">Kelola tahun ajaran, semester, dan pengaturan akademik lainnya</p>
    </div>

    <?php if ($message): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?> flex items-center shadow-sm">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3 text-xl"></i>
            <span class="font-medium"><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Current Academic Year Card -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Tahun Ajaran Aktif</p>
                    <h3 class="text-3xl font-bold mt-2">
                        <?= htmlspecialchars($academicSettings->getCurrentAcademicYear()) ?>
                    </h3>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Current Semester Card -->
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Semester Aktif</p>
                    <h3 class="text-3xl font-bold mt-2">Semester <?= $academicSettings->getCurrentSemester() ?></h3>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-book-open text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Academic Period Card -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Periode Akademik</p>
                    <h3 class="text-lg font-bold mt-2">
                        <?= date('d M Y', strtotime($academicSettings->getAcademicYearStart())) ?><br>
                        <span class="text-sm">s/d</span>
                        <?= date('d M Y', strtotime($academicSettings->getAcademicYearEnd())) ?>
                    </h3>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tahun Ajaran Management Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-calendar-plus text-indigo-600"></i>
                Manajemen Tahun Ajaran
            </h3>
            <button onclick="openModal('modalAddYear')"
                class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 shadow-md">
                <i class="fas fa-plus mr-2"></i>Tambah Tahun Ajaran
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b-2 border-gray-200">
                        <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700">Tahun Ajaran</th>
                        <th class="text-left px-4 py-3 text-sm font-semibold text-gray-700">Periode</th>
                        <th class="text-center px-4 py-3 text-sm font-semibold text-gray-700">Status</th>
                        <th class="text-center px-4 py-3 text-sm font-semibold text-gray-700">Penggunaan</th>
                        <th class="text-center px-4 py-3 text-sm font-semibold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allYears as $year): ?>
                        <?php $usage = $tahunAjaran->getUsageStats($year['tahun_ajaran']); ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span
                                    class="font-bold text-gray-800 text-lg"><?= htmlspecialchars($year['tahun_ajaran']) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-600">
                                    <?= date('d M Y', strtotime($year['tanggal_mulai'])) ?> -
                                    <?= date('d M Y', strtotime($year['tanggal_akhir'])) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($year['is_active']): ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        <i class="fas fa-check-circle mr-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        <i class="fas fa-minus-circle mr-1"></i> Tidak Aktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-3 text-sm">
                                    <span class="inline-flex items-center text-blue-600">
                                        <i class="fas fa-chalkboard mr-1"></i> <?= $usage['kelas'] ?> kelas
                                    </span>
                                    <span class="inline-flex items-center text-emerald-600">
                                        <i class="fas fa-user-graduate mr-1"></i> <?= $usage['siswa'] ?> siswa
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <?php if (!$year['is_active']): ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="set_active">
                                            <input type="hidden" name="year_id" value="<?= $year['id'] ?>">
                                            <button type="submit"
                                                class="px-3 py-1 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 text-sm font-medium"
                                                title="Set sebagai Aktif">
                                                <i class="fas fa-check-circle mr-1"></i>Set Aktif
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-green-50 text-green-600 rounded-lg text-sm font-medium">
                                            <i class="fas fa-lock mr-1"></i>Sedang Aktif
                                        </span>
                                    <?php endif; ?>

                                    <?php
                                    $canDelete = $tahunAjaran->canDelete($year['id']);
                                    if ($canDelete['can_delete']):
                                        ?>
                                        <form method="POST" class="inline"
                                            onsubmit="return confirm('Yakin ingin menghapus tahun ajaran <?= $year['tahun_ajaran'] ?>?')">
                                            <input type="hidden" name="action" value="delete_year">
                                            <input type="hidden" name="year_id" value="<?= $year['id'] ?>">
                                            <button type="submit"
                                                class="px-3 py-1 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 text-sm font-medium"
                                                title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 text-gray-400 rounded-lg text-sm cursor-not-allowed"
                                            title="<?= htmlspecialchars($canDelete['reason']) ?>">
                                            <i class="fas fa-trash"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Settings Form -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <i class="fas fa-cog text-purple-600"></i>
            Pengaturan Akademik
        </h3>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="action" value="update_settings">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Current Academic Year -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Tahun Ajaran Aktif
                    </label>
                    <select name="settings[current_academic_year]" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <?php foreach ($academicYearOptions as $year): ?>
                            <option value="<?= $year ?>" <?= $year === $academicSettings->getCurrentAcademicYear() ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Tahun ajaran yang sedang berjalan saat ini</p>
                </div>

                <!-- Current Semester -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-book-open text-green-600 mr-2"></i>Semester Aktif
                    </label>
                    <select name="settings[semester]" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        <option value="1" <?= $academicSettings->getCurrentSemester() == 1 ? 'selected' : '' ?>>Semester 1
                            (Ganjil)</option>
                        <option value="2" <?= $academicSettings->getCurrentSemester() == 2 ? 'selected' : '' ?>>Semester 2
                            (Genap)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Semester yang sedang berjalan</p>
                </div>

                <!-- Academic Year Start -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-play-circle text-purple-600 mr-2"></i>Tanggal Mulai Tahun Ajaran
                    </label>
                    <input type="date" name="settings[academic_year_start]"
                        value="<?= htmlspecialchars($academicSettings->getAcademicYearStart()) ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Tanggal dimulainya tahun ajaran baru</p>
                </div>

                <!-- Academic Year End -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-stop-circle text-red-600 mr-2"></i>Tanggal Akhir Tahun Ajaran
                    </label>
                    <input type="date" name="settings[academic_year_end]"
                        value="<?= htmlspecialchars($academicSettings->getAcademicYearEnd()) ?>" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Tanggal berakhirnya tahun ajaran</p>
                </div>

                <!-- Show Previous Debts -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave text-amber-600 mr-2"></i>Tampilkan Tunggakan Tahun Lalu
                    </label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="settings[show_previous_debts]" value="1"
                                <?= $academicSettings->shouldShowPreviousDebts() ? 'checked' : '' ?>
                                class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-gray-700">Ya, tampilkan terpisah</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="settings[show_previous_debts]" value="0"
                                <?= !$academicSettings->shouldShowPreviousDebts() ? 'checked' : '' ?>
                                class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-gray-700">Tidak</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Jika aktif, tunggakan dari tahun ajaran sebelumnya akan
                        ditampilkan terpisah dengan warna berbeda</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 text-xl mt-1 mr-3"></i>
            <div>
                <h4 class="font-bold text-blue-800 mb-2">Informasi Penting</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Perubahan tahun ajaran akan mempengaruhi semua modul yang menggunakan filter tahun ajaran</li>
                    <li>• Pastikan tanggal mulai dan akhir tahun ajaran sudah sesuai dengan kalender akademik sekolah
                    </li>
                    <li>• Fitur "Tunggakan Tahun Lalu" akan menampilkan tagihan yang belum lunas dari tahun ajaran
                        sebelumnya</li>
                </ul>
            </div>
        </div>
    </div>
</main>

<!-- Modal Add Tahun Ajaran -->
<div id="modalAddYear" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6 pb-4 border-b">
            <h3 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-calendar-plus text-indigo-600"></i>
                Tambah Tahun Ajaran Baru
            </h3>
            <button onclick="closeModal('modalAddYear')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="add_year">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>Tahun Ajaran
                    </label>
                    <input type="text" name="tahun_ajaran" required pattern="\d{4}/\d{4}" placeholder="2025/2026"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Format: YYYY/YYYY (contoh: 2025/2026)</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-play-circle text-green-600 mr-2"></i>Tanggal Mulai
                        </label>
                        <input type="date" name="tanggal_mulai" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-stop-circle text-red-600 mr-2"></i>Tanggal Akhir
                        </label>
                        <input type="date" name="tanggal_akhir" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-1">Info:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Tahun ajaran baru akan ditambahkan dengan status <strong>Tidak Aktif</strong></li>
                                <li>Klik tombol "Set Aktif" untuk mengaktifkan tahun ajaran</li>
                                <li>Hanya 1 tahun ajaran yang bisa aktif di satu waktu</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end mt-6 pt-4 border-t">
                <button type="button" onclick="closeModal('modalAddYear')"
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Tambah Tahun Ajaran
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
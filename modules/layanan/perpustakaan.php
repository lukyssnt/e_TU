<?php
$pageTitle = 'Perpustakaan - Layanan Khusus';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Perpustakaan.php';
require_once __DIR__ . '/../../classes/Siswa.php';

checkPermission('layanan');

$perpustakaan = new Perpustakaan();
$siswa = new Siswa();

$message = '';
$messageType = '';
$activeTab = $_GET['tab'] ?? 'buku';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_buku':
                $result = $perpustakaan->createBuku([
                    'kode_buku' => $_POST['kode_buku'],
                    'judul' => $_POST['judul'],
                    'pengarang' => $_POST['pengarang'],
                    'penerbit' => $_POST['penerbit'],
                    'tahun_terbit' => $_POST['tahun_terbit'],
                    'kategori' => $_POST['kategori'],
                    'stok' => $_POST['stok'],
                    'lokasi_rak' => $_POST['lokasi_rak']
                ]);
                if ($result) {
                    $message = 'Buku berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menambahkan buku!';
                    $messageType = 'error';
                }
                $activeTab = 'buku';
                break;

            case 'update_buku':
                $result = $perpustakaan->updateBuku($_POST['id'], [
                    'kode_buku' => $_POST['kode_buku'],
                    'judul' => $_POST['judul'],
                    'pengarang' => $_POST['pengarang'],
                    'penerbit' => $_POST['penerbit'],
                    'tahun_terbit' => $_POST['tahun_terbit'],
                    'kategori' => $_POST['kategori'],
                    'stok' => $_POST['stok'],
                    'lokasi_rak' => $_POST['lokasi_rak']
                ]);
                if ($result) {
                    $message = 'Data buku berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui data buku!';
                    $messageType = 'error';
                }
                $activeTab = 'buku';
                break;

            case 'delete_buku':
                if ($perpustakaan->deleteBuku($_POST['id'])) {
                    $message = 'Buku berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus buku!';
                    $messageType = 'error';
                }
                $activeTab = 'buku';
                break;

            case 'pinjam_buku':
                $result = $perpustakaan->createPeminjaman([
                    'buku_id' => $_POST['buku_id'],
                    'siswa_id' => $_POST['siswa_id'],
                    'tanggal_pinjam' => $_POST['tanggal_pinjam'],
                    'tanggal_kembali_rencana' => $_POST['tanggal_kembali_rencana']
                ]);
                if ($result) {
                    $message = 'Peminjaman berhasil dicatat!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal mencatat peminjaman! Cek stok buku.';
                    $messageType = 'error';
                }
                $activeTab = 'peminjaman';
                break;

            case 'kembalikan_buku':
                if ($perpustakaan->kembalikanBuku($_POST['id'], date('Y-m-d'))) {
                    $message = 'Buku berhasil dikembalikan!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal mengembalikan buku!';
                    $messageType = 'error';
                }
                $activeTab = 'peminjaman';
                break;

            case 'create_kunjungan':
                $siswaId = $_POST['siswa_id'] ?? null;
                // If ID not set but NISN is provided (e.g. from scanner), find student
                if (!$siswaId && !empty($_POST['nisn_kunjungan'])) {
                    $s = $siswa->getByNISN($_POST['nisn_kunjungan']);
                    if ($s)
                        $siswaId = $s['id'];
                }

                if ($siswaId) {
                    if ($perpustakaan->createKunjungan($siswaId, $_POST['keperluan'])) {
                        $message = 'Kunjungan berhasil dicatat!';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal mencatat kunjungan!';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Siswa tidak ditemukan! Pastikan NISN benar.';
                    $messageType = 'error';
                }
                $activeTab = 'kunjungan';
                break;

            case 'delete_kunjungan':
                if ($perpustakaan->deleteKunjungan($_POST['id'])) {
                    $message = 'Data kunjungan berhasil dihapus!';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus data kunjungan!';
                    $messageType = 'error';
                }
                $activeTab = 'kunjungan';
                break;
        }
    }
}

// Get data
// Get data
$bukuList = $perpustakaan->getAllBuku();
$peminjamanList = $perpustakaan->getAllPeminjaman();
$tglKunjungan = $_GET['tgl_kunjungan'] ?? null;
$kunjunganList = $perpustakaan->getAllKunjungan($tglKunjungan);
$siswaList = $siswa->getAll();
$stats = $perpustakaan->getStats();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/layanan/index.php" class="hover:text-blue-600">Layanan Khusus</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Perpustakaan</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-book text-white text-xl"></i>
                    </div>
                    Perpustakaan
                </h2>
                <p class="text-gray-600 mt-2">Manajemen buku dan sirkulasi peminjaman</p>
            </div>
            <div class="flex gap-2">
                <button onclick="openModal('modalAddBuku')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold shadow hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Tambah Buku
                </button>
                <button onclick="openModal('modalPinjam')"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold shadow hover:bg-green-700">
                    <i class="fas fa-hand-holding-book mr-2"></i>Pinjam Buku
                </button>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div
            class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300' ?>">
            <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Judul Buku</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['total_buku'] ?></p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Sedang Dipinjam</p>
                    <p class="text-3xl font-bold text-orange-600"><?= $stats['dipinjam'] ?></p>
                </div>
                <div class="w-14 h-14 bg-orange-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book-reader text-orange-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Kunjungan (Hari Ini / Total)</p>
                    <p class="text-3xl font-bold text-purple-600">
                        <?= $stats['kunjungan_hari_ini'] ?> <span class="text-lg text-gray-400 font-normal">/
                            <?= $stats['total_kunjungan'] ?></span>
                    </p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent"
            role="tablist">
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 <?= $activeTab === 'buku' ? 'border-blue-600 text-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' ?>"
                    id="buku-tab" data-tabs-target="#buku" type="button" role="tab" aria-controls="buku"
                    aria-selected="<?= $activeTab === 'buku' ? 'true' : 'false' ?>" onclick="switchTab('buku')">
                    <i class="fas fa-book mr-2"></i>Data Buku
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 <?= $activeTab === 'peminjaman' ? 'border-blue-600 text-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' ?>"
                    id="peminjaman-tab" data-tabs-target="#peminjaman" type="button" role="tab"
                    aria-controls="peminjaman" aria-selected="<?= $activeTab === 'peminjaman' ? 'true' : 'false' ?>"
                    onclick="switchTab('peminjaman')">
                    <i class="fas fa-history mr-2"></i>Riwayat Peminjaman
                </button>
            </li>
            <li class="mr-2" role="presentation">
                <button
                    class="inline-block p-4 rounded-t-lg border-b-2 <?= $activeTab === 'kunjungan' ? 'border-blue-600 text-blue-600' : 'border-transparent hover:text-gray-600 hover:border-gray-300' ?>"
                    id="kunjungan-tab" data-tabs-target="#kunjungan" type="button" role="tab" aria-controls="kunjungan"
                    aria-selected="<?= $activeTab === 'kunjungan' ? 'true' : 'false' ?>"
                    onclick="switchTab('kunjungan')">
                    <i class="fas fa-user-check mr-2"></i>Absensi Kunjungan
                </button>
            </li>
        </ul>
    </div>

    <!-- Tab Content -->
    <div id="myTabContent">
        <!-- Tab Buku -->
        <div class="<?= $activeTab === 'buku' ? '' : 'hidden' ?>" id="buku" role="tabpanel" aria-labelledby="buku-tab">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Koleksi Buku</h3>
                    <input type="text" id="searchBuku" placeholder="Cari judul/pengarang..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-64">
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table w-full" id="tableBuku">
                        <thead>
                            <tr>
                                <th class="text-left">Kode</th>
                                <th class="text-left">Judul</th>
                                <th class="text-left">Pengarang/Penerbit</th>
                                <th class="text-left">Tahun</th>
                                <th class="text-center">Stok</th>
                                <th class="text-left">Rak</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bukuList as $b): ?>
                                <tr>
                                    <td class="font-mono text-sm"><?= htmlspecialchars($b['kode_buku']) ?></td>
                                    <td class="font-semibold"><?= htmlspecialchars($b['judul']) ?></td>
                                    <td class="text-sm">
                                        <div><?= htmlspecialchars($b['pengarang']) ?></div>
                                        <div class="text-gray-500"><?= htmlspecialchars($b['penerbit']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($b['tahun_terbit']) ?></td>
                                    <td class="text-center">
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-bold <?= $b['stok'] > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                            <?= $b['stok'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($b['lokasi_rak']) ?></td>
                                    <td class="text-center">
                                        <button onclick='editBuku(<?= json_encode($b) ?>)'
                                            class="px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteBuku(<?= $b['id'] ?>)"
                                            class="px-2 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Peminjaman -->
        <div class="<?= $activeTab === 'peminjaman' ? '' : 'hidden' ?>" id="peminjaman" role="tabpanel"
            aria-labelledby="peminjaman-tab">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Daftar Peminjaman</h3>
                    <input type="text" id="searchPinjam" placeholder="Cari siswa/buku..."
                        class="px-4 py-2 border border-gray-300 rounded-lg w-64">
                </div>
                <div class="overflow-x-auto">
                    <table class="data-table w-full" id="tablePinjam">
                        <thead>
                            <tr>
                                <th class="text-left">Tanggal</th>
                                <th class="text-left">Siswa</th>
                                <th class="text-left">Buku</th>
                                <th class="text-left">Jadwal Kembali</th>
                                <th class="text-left">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peminjamanList as $p): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($p['tanggal_pinjam'])) ?></td>
                                    <td>
                                        <div class="font-semibold"><?= htmlspecialchars($p['nama_siswa']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($p['nisn']) ?></div>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($p['judul']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($p['kode_buku']) ?></div>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($p['tanggal_kembali_rencana'])) ?>
                                        <?php if ($p['status'] === 'Dipinjam' && strtotime($p['tanggal_kembali_rencana']) < time()): ?>
                                            <span class="text-red-500 text-xs font-bold">(Terlambat)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span
                                            class="px-2 py-1 rounded-full text-xs font-bold <?= $p['status'] === 'Dipinjam' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700' ?>">
                                            <?= $p['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($p['status'] === 'Dipinjam'): ?>
                                            <button onclick="kembalikanBuku(<?= $p['id'] ?>)"
                                                class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                                Kembali
                                            </button>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-500">
                                                Selesai<br>
                                                <?= date('d/m/Y', strtotime($p['tanggal_kembali_realisasi'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tab Kunjungan -->
        <div class="<?= $activeTab === 'kunjungan' ? '' : 'hidden' ?>" id="kunjungan" role="tabpanel"
            aria-labelledby="kunjungan-tab">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Form Input Kunjungan -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-qrcode text-purple-600"></i> Catat Kunjungan
                        </h3>
                        <form method="POST" id="formKunjungan">
                            <input type="hidden" name="action" value="create_kunjungan">

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Scan NISN</label>
                                <input type="text" name="nisn_kunjungan" id="nisn_kunjungan"
                                    placeholder="Scan kartu / ketik NISN"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                    autofocus autocomplete="off">
                                <p class="text-xs text-gray-500 mt-1">Tekan Enter setelah scan</p>
                            </div>

                            <div class="relative flex py-2 items-center">
                                <div class="flex-grow border-t border-gray-200"></div>
                                <span class="flex-shrink-0 mx-4 text-gray-400 text-xs">ATAU PILIH MANUAL</span>
                                <div class="flex-grow border-t border-gray-200"></div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Siswa</label>
                                <select name="siswa_id" id="select_siswa_kunjungan"
                                    class="w-full border rounded px-3 py-2 select2">
                                    <option value="">-- Cari Nama Siswa --</option>
                                    <?php foreach ($siswaList as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap']) ?>
                                            (<?= $s['nisn'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan</label>
                                <select name="keperluan" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                                    <option value="Membaca">Membaca Buku</option>
                                    <option value="Meminjam">Meminjam Buku</option>
                                    <option value="Mengembalikan">Mengembalikan Buku</option>
                                    <option value="Wifi / Internet">Wifi / Internet</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-bold shadow-lg hover:opacity-90">
                                <i class="fas fa-check-circle mr-2"></i>Kirim Absensi
                            </button>
                        </form>
                    </div>
                </div>

                <!-- List Kunjungan -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-xl shadow-lg p-6 h-full">
                        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
                            <h3 class="text-lg font-bold text-gray-800">
                                Riwayat Kunjungan
                                <?= $tglKunjungan ? '(' . date('d/m/Y', strtotime($tglKunjungan)) . ')' : '(Terbaru)' ?>
                            </h3>
                            <div class="flex items-center gap-2">
                                <form method="GET" class="flex gap-2">
                                    <input type="hidden" name="tab" value="kunjungan">
                                    <input type="date" name="tgl_kunjungan" value="<?= $tglKunjungan ?>"
                                        onchange="this.form.submit()" class="px-3 py-1 border rounded text-sm w-36"
                                        title="Filter Tanggal">
                                    <?php if ($tglKunjungan): ?>
                                        <a href="?tab=kunjungan"
                                            class="px-3 py-1 bg-gray-200 text-gray-600 rounded text-sm hover:bg-gray-300">Reset</a>
                                    <?php endif; ?>
                                </form>
                                <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-bold">
                                    <?= count($kunjunganList) ?>
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                                    <tr>
                                        <th class="py-3 px-4">Waktu</th>
                                        <th class="py-3 px-4">Siswa</th>
                                        <th class="py-3 px-4">Kelas</th>
                                        <th class="py-3 px-4">Keperluan</th>
                                        <th class="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php if (empty($kunjunganList)): ?>
                                        <tr>
                                            <td colspan="5" class="py-8 text-center text-gray-500 italic">Belum ada
                                                data kunjungan</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($kunjunganList as $k): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="py-3 px-4 font-mono text-purple-600">
                                                    <?= date('H:i', strtotime($k['waktu'])) ?>
                                                </td>
                                                <td class="py-3 px-4">
                                                    <div class="font-bold text-gray-800">
                                                        <?= htmlspecialchars($k['nama_siswa']) ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($k['nisn']) ?>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4"><?= htmlspecialchars($k['nama_kelas'] ?? '-') ?></td>
                                                <td class="py-3 px-4">
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs">
                                                        <?= htmlspecialchars($k['keperluan']) ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <button onclick="deleteKunjungan(<?= $k['id'] ?>)"
                                                        class="px-2 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200"
                                                        title="Hapus">
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
                </div>
            </div>
        </div>
    </div>
    </div>
</main>

<!-- Modal Add Buku -->
<div id="modalAddBuku" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Tambah Buku Baru</h3>
            <button onclick="closeModal('modalAddBuku')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="create_buku">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Kode Buku</label>
                    <input type="text" name="kode_buku" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Judul Buku</label>
                    <input type="text" name="judul" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Pengarang</label>
                    <input type="text" name="pengarang" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Penerbit</label>
                    <input type="text" name="penerbit" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <select name="kategori" class="w-full border rounded px-3 py-2">
                        <option value="Pelajaran">Pelajaran</option>
                        <option value="Fiksi">Fiksi</option>
                        <option value="Referensi">Referensi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Stok</label>
                    <input type="number" name="stok" required min="1" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalAddBuku')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Buku -->
<div id="modalEditBuku" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Edit Buku</h3>
            <button onclick="closeModal('modalEditBuku')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="update_buku">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Kode Buku</label>
                    <input type="text" name="kode_buku" id="edit_kode_buku" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Judul Buku</label>
                    <input type="text" name="judul" id="edit_judul" required class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Pengarang</label>
                    <input type="text" name="pengarang" id="edit_pengarang" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Penerbit</label>
                    <input type="text" name="penerbit" id="edit_penerbit" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" id="edit_tahun_terbit" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Kategori</label>
                    <select name="kategori" id="edit_kategori" class="w-full border rounded px-3 py-2">
                        <option value="Pelajaran">Pelajaran</option>
                        <option value="Fiksi">Fiksi</option>
                        <option value="Referensi">Referensi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Stok</label>
                    <input type="number" name="stok" id="edit_stok" required min="0"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Lokasi Rak</label>
                    <input type="text" name="lokasi_rak" id="edit_lokasi_rak" class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalEditBuku')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pinjam -->
<div id="modalPinjam" class="modal-overlay">
    <div class="modal-content max-w-lg">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Pinjam Buku</h3>
            <button onclick="closeModal('modalPinjam')"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="pinjam_buku">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Siswa</label>
                    <select name="siswa_id" required class="w-full border rounded px-3 py-2 select2">
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswaList as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama_lengkap']) ?> (<?= $s['nisn'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Buku</label>
                    <select name="buku_id" required class="w-full border rounded px-3 py-2 select2">
                        <option value="">-- Pilih Buku --</option>
                        <?php foreach ($bukuList as $b): ?>
                            <?php if ($b['stok'] > 0): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['judul']) ?> (<?= $b['kode_buku'] ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tanggal Pinjam</label>
                    <input type="date" name="tanggal_pinjam" value="<?= date('Y-m-d') ?>" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Rencana Kembali</label>
                    <input type="date" name="tanggal_kembali_rencana" value="<?= date('Y-m-d', strtotime('+7 days')) ?>"
                        required class="w-full border rounded px-3 py-2">
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modalPinjam')"
                    class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Pinjam</button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete & Return -->
<form method="POST" id="formAction">
    <input type="hidden" name="action" id="action_type">
    <input type="hidden" name="id" id="action_id">
</form>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    function switchTab(tab) {
        document.querySelectorAll('[role="tabpanel"]').forEach(el => el.classList.add('hidden'));
        document.getElementById(tab).classList.remove('hidden');

        document.querySelectorAll('[role="tab"]').forEach(el => {
            el.classList.remove('border-blue-600', 'text-blue-600');
            el.classList.add('border-transparent');
        });
        document.getElementById(tab + '-tab').classList.add('border-blue-600', 'text-blue-600');
        document.getElementById(tab + '-tab').classList.remove('border-transparent');

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);
    }

    function editBuku(b) {
        document.getElementById('edit_id').value = b.id;
        document.getElementById('edit_kode_buku').value = b.kode_buku;
        document.getElementById('edit_judul').value = b.judul;
        document.getElementById('edit_pengarang').value = b.pengarang;
        document.getElementById('edit_penerbit').value = b.penerbit;
        document.getElementById('edit_tahun_terbit').value = b.tahun_terbit;
        document.getElementById('edit_kategori').value = b.kategori;
        document.getElementById('edit_stok').value = b.stok;
        document.getElementById('edit_lokasi_rak').value = b.lokasi_rak || '';
        openModal('modalEditBuku');
    }

    function deleteBuku(id) {
        if (confirm('Yakin ingin menghapus buku ini?')) {
            document.getElementById('action_type').value = 'delete_buku';
            document.getElementById('action_id').value = id;
            document.getElementById('formAction').submit();
        }
    }

    function kembalikanBuku(id) {
        if (confirm('Proses pengembalian buku ini?')) {
            document.getElementById('action_type').value = 'kembalikan_buku';
            document.getElementById('action_id').value = id;
            document.getElementById('formAction').submit();
        }
    }

    function deleteKunjungan(id) {
        if (confirm('Hapus data kunjungan ini?')) {
            document.getElementById('action_type').value = 'delete_kunjungan';
            document.getElementById('action_id').value = id;
            document.getElementById('formAction').submit();
        }
    }

    // Search functionality
    document.getElementById('searchBuku')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#tableBuku tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });

    document.getElementById('searchPinjam')?.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#tablePinjam tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
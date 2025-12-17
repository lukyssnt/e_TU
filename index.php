<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/classes/Portal.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/classes/Setting.php';

// Fetch Settings
$settingObj = new Setting();
$allSettings = $settingObj->getAll();
$settings = [];
foreach ($allSettings as $s) {
    $settings[$s['setting_key']] = $s['setting_value'];
}

$schoolName = $settings['school_name'] ?? 'MA AL IHSAN';
$appLogo = !empty($settings['app_logo']) ? '/e-TU/' . $settings['app_logo'] : null;

// Fetch Portal Content
$portal = new Portal();
$cmsContent = $portal->getAllContent();
$heroTitle = $cmsContent['hero_title']['content_value'] ?? 'Layanan Administrasi Lebih Cerdas & Cepat';
$heroSubtitle = $cmsContent['hero_subtitle']['content_value'] ?? 'Sistem Informasi Tata Usaha Terpadu';
$aboutText = $cmsContent['about_text']['content_value'] ?? 'Platform digital resmi Tata Usaha untuk mempermudah pelayanan administrasi, persuratan, dan informasi publik secara transparan dan efisien.';

// Handle Image Paths (Check if they are URLs or local uploads)
$heroImage = $cmsContent['hero_image']['content_value'] ?? 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
if (strpos($heroImage, 'http') === false)
    $heroImage = '/e-TU/' . $heroImage;

$aboutImage = $cmsContent['about_image']['content_value'] ?? 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
if (strpos($aboutImage, 'http') === false)
    $aboutImage = '/e-TU/' . $aboutImage;


$statusMsg = '';
$statusType = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'buku_tamu') {
        $data = [
            'nama' => clean($_POST['nama']),
            'email' => clean($_POST['email'] ?? ''),
            'no_hp' => clean($_POST['no_hp']),
            'instansi' => clean($_POST['instansi'] ?? ''),
            'keperluan' => clean($_POST['keperluan'])
        ];

        if ($portal->createBukuTamu($data)) {
            $statusMsg = 'Terima kasih! Data kunjungan Anda telah berhasil disimpan.';
            $statusType = 'success';
        } else {
            $statusMsg = 'Gagal menyimpan data buku tamu.';
            $statusType = 'error';
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'alumni') {
        $data = [
            'nama' => clean($_POST['nama']),
            'tahun_lulus' => clean($_POST['tahun_lulus']),
            'nisn' => clean($_POST['nisn']),
            'no_hp' => clean($_POST['no_hp']),
            'jenis_layanan' => clean($_POST['jenis_layanan']),
            'deskripsi' => clean($_POST['deskripsi'])
        ];

        if ($portal->createAlumniRequest($data)) {
            $statusMsg = 'Permohonan layanan alumni berhasil dikirim.';
            $statusType = 'success';
        } else {
            $statusMsg = 'Gagal mengirim permohonan.';
            $statusType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($schoolName) ?> - Portal Informasi</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        .hero-pattern {
            background-color: #f0fdf4;
            /* Emerald 50 */
            opacity: 0.5;
            background-image: radial-gradient(#10b981 0.5px, transparent 0.5px), radial-gradient(#10b981 0.5px, #f0fdf4 0.5px);
            background-size: 20px 20px;
        }

        /* Custom Scrollbar for Autocomplete */
        .suggestions-list::-webkit-scrollbar {
            width: 8px;
        }

        .suggestions-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .suggestions-list::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <!-- Navbar -->
    <nav
        class="fixed w-full z-50 transition-all duration-300 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <?php if ($appLogo): ?>
                        <img src="<?= htmlspecialchars($appLogo) ?>" alt="Logo" class="w-10 h-10 object-contain">
                    <?php else: ?>
                        <div
                            class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg text-white">
                            <i class="fas fa-school text-xl"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="font-bold text-xl text-gray-900 tracking-tight"><?= htmlspecialchars($schoolName) ?>
                        </h1>
                        <p class="text-xs text-emerald-600 font-medium tracking-wide">TATA USAHA</p>
                    </div>
                </div>

                <!-- Menu (Responsive hidden on small) -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#beranda"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Beranda</a>
                    <a href="#keuangan" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Cek
                        Tagihan</a>
                    <a href="#alumni"
                        class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Layanan Surat</a>
                    <a href="#bukutamu" class="text-sm font-medium text-gray-600 hover:text-emerald-600 transition">Buku
                        Tamu</a>
                </div>

                <!-- No Login Button as requested -->
                <div>
                    <!-- <span class="text-xs text-gray-400 italic">Selamat Datang</span> -->
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 hero-pattern z-0"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <?php if ($statusMsg): ?>
                <div
                    class="mb-8 p-4 rounded-xl <?= $statusType === 'success' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-red-100 text-red-800 border border-red-300' ?> text-center shadow-lg animate-bounce">
                    <?= $statusMsg ?>
                </div>
            <?php endif; ?>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-8 order-2 lg:order-1">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-medium">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <?= htmlspecialchars($heroSubtitle) ?>
                    </div>
                    <h1 class="text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight">
                        <?= $heroTitle ?>
                    </h1>
                    <p class="text-lg text-gray-600 max-w-xl leading-relaxed">
                        <?= htmlspecialchars($aboutText) ?>
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="#keuangan"
                            class="px-7 py-3.5 rounded-full bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition shadow-xl hover:shadow-emerald-500/30">
                            <i class="fas fa-search-dollar mr-2"></i> Cek Keuangan
                        </a>
                        <a href="#alumni"
                            class="px-7 py-3.5 rounded-full bg-white text-gray-700 border border-gray-200 font-semibold hover:bg-gray-50 transition shadow-sm">
                            Permohonan Surat
                        </a>
                    </div>
                </div>
                <div class="relative order-1 lg:order-2">
                    <div
                        class="absolute inset-0 bg-gradient-to-tr from-emerald-500 to-green-500 rounded-2xl rotate-3 opacity-20 blur-2xl">
                    </div>
                    <img src="<?= htmlspecialchars($heroImage) ?>" alt="Hero Image"
                        class="relative rounded-2xl shadow-2xl w-full object-cover h-[500px]">
                </div>
            </div>
        </div>
    </section>

    <!-- Keuangan (Cek SPP) Section -->
    <section id="keuangan" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-emerald-600 font-bold tracking-wide uppercase text-sm">Layanan Keuangan Siswa</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-4">Cek Status Pembayaran</h2>
                <p class="text-gray-600">Pantau status pembayaran administrasi siswa secara real-time. Masukkan data
                    siswa untuk melihat rincian.</p>
            </div>

            <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-100 p-8 relative">
                <form id="financeForm" class="space-y-6">
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Siswa</label>
                        <input type="text" id="searchName" autocomplete="off"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition"
                            placeholder="Ketik nama siswa...">
                        <input type="hidden" id="selectedSiswaId">

                        <!-- Suggestions List -->
                        <div id="suggestions"
                            class="hidden absolute w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto z-20">
                        </div>
                    </div>

                    <button type="submit" id="btnCek"
                        class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-emerald-500/30 transition transform hover:-translate-y-1">
                        Cek Status Tagihan
                    </button>

                    <div id="financeResult"
                        class="hidden mt-6 p-6 bg-gray-50 rounded-xl border border-gray-200 text-center space-y-3">
                        <h3 class="font-bold text-gray-800 text-lg" id="resName"></h3>
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <div class="p-2 bg-blue-50 rounded text-blue-700">
                                <div class="font-semibold text-xs text-gray-500 uppercase">Total Tagihan</div>
                                <div class="font-bold text-lg" id="resTotal"></div>
                            </div>
                            <div class="p-2 bg-green-50 rounded text-green-700">
                                <div class="font-semibold text-xs text-gray-500 uppercase">Terbayar</div>
                                <div class="font-bold text-lg" id="resPaid"></div>
                            </div>
                            <div class="p-2 bg-red-50 rounded text-red-700">
                                <div class="font-semibold text-xs text-gray-500 uppercase">Kekurangan</div>
                                <div class="font-bold text-lg" id="resSisa"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Layanan Surat (Alumni) -->
    <section id="alumni" class="py-20 bg-emerald-900 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10"
            style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16">
                <div>
                    <span class="text-emerald-300 font-bold tracking-wide uppercase text-sm">Layanan Terpadu</span>
                    <h2 class="text-3xl font-bold text-white mt-2 mb-6">Layanan Permohonan Surat & Administrasi Online
                    </h2>
                    <p class="text-emerald-100 text-lg leading-relaxed mb-6">
                        Layanan ini dapat digunakan untuk berbagai kebutuhan dokumen seperti legalisir ijazah, surat
                        keterangan, rekomendasi, dan layanan administrasi lain. Terbuka untuk alumni, siswa, wali, guru,
                        dan masyarakat umum.
                    </p>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div
                                class="w-10 h-10 bg-emerald-800 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="font-bold text-lg">1</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Isi Formulir</h4>
                                <p class="text-sm text-emerald-200">Lengkapi data diri sesuai identitas dan jelaskan
                                    kebutuhan layanan secara detail.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-10 h-10 bg-emerald-800 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="font-bold text-lg">2</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Proses Verifikasi</h4>
                                <p class="text-sm text-emerald-200">Staf Tata Usaha akan memeriksa dan memverifikasi
                                    permohonan.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-10 h-10 bg-emerald-800 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="font-bold text-lg">3</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Konfirmasi dari Staf TU</h4>
                                <p class="text-sm text-emerald-200">Pemohon akan dihubungi staf TU ketika permohonan
                                    telah diproses dan siap ditindaklanjuti.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div
                                class="w-10 h-10 bg-emerald-800 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="font-bold text-lg">4</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Penyerahan Dokumen</h4>
                                <p class="text-sm text-emerald-200">Dokumen dapat diambil langsung ke sekolah atau
                                    dikirimkan sesuai kesepakatan.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20 shadow-2xl">
                    <form method="POST" class="space-y-5">
                        <input type="hidden" name="action" value="alumni">
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-emerald-100 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" required
                                    class="w-full px-4 py-3 bg-white/10 border border-emerald-400/30 rounded-lg text-white placeholder-emerald-200/50 focus:outline-none focus:bg-white/20 focus:border-emerald-300 transition"
                                    placeholder="Sesuai Identitas">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-emerald-100 mb-1">No. WhatsApp</label>
                                <input type="tel" name="no_hp" required
                                    class="w-full px-4 py-3 bg-white/10 border border-emerald-400/30 rounded-lg text-white placeholder-emerald-200/50 focus:outline-none focus:bg-white/20 focus:border-emerald-300 transition"
                                    placeholder="08...">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-emerald-100 mb-1">Tahun Lulus (untuk
                                    alumni)</label>
                                <input type="number" name="tahun_lulus"
                                    class="w-full px-4 py-3 bg-white/10 border border-emerald-400/30 rounded-lg text-white placeholder-emerald-200/50 focus:outline-none focus:bg-white/20 focus:border-emerald-300 transition"
                                    placeholder="Contoh: 2020">
                            </div>

                        </div>

                        <div>
                            <label class="block text-sm font-medium text-emerald-100 mb-1">Jenis Layanan /
                                Keperluan</label>
                            <input type="text" name="jenis_layanan" required
                                class="w-full px-4 py-3 bg-white/10 border border-emerald-400/30 rounded-lg text-white placeholder-emerald-200/50 focus:outline-none focus:bg-white/20 focus:border-emerald-300 transition"
                                placeholder="Contoh: Legalisir Ijazah">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-emerald-100 mb-1">Deskripsi & Keterangan
                                Tambahan</label>
                            <textarea name="deskripsi" rows="3" required
                                class="w-full px-4 py-3 bg-white/10 border border-emerald-400/30 rounded-lg text-white placeholder-emerald-200/50 focus:outline-none focus:bg-white/20 focus:border-emerald-300 transition"
                                placeholder="Jelaskan detail permintaan, jumlah lembar, dll..."></textarea>
                        </div>

                        <button type="submit"
                            class="w-full py-4 bg-emerald-500 hover:bg-emerald-400 text-white font-bold rounded-xl shadow-lg hover:shadow-emerald-500/50 transition transform hover:-translate-y-1">
                            Kirim Permohonan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Buku Tamu Section -->
    <section id="bukutamu" class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">Buku Tamu Digital</h2>
                <p class="text-gray-600 mt-2">Kunjungan ke sekolah wajib mengisi buku tamu.</p>
            </div>

            <div class="bg-white rounded-2xl p-8 shadow-md border-l-4 border-emerald-500">
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="buku_tamu">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Lengkap</label>
                            <input type="text" name="nama" required
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">No. WhatsApp</label>
                            <input type="tel" name="no_hp" required
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Keperluan</label>
                        <input type="text" name="keperluan" required
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Instansi (Opsional)</label>
                        <input type="text" name="instansi"
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded focus:bg-white focus:ring-2 focus:ring-emerald-500 outline-none transition">
                    </div>
                    <button type="submit"
                        class="w-full py-3 bg-gray-800 hover:bg-gray-900 text-white font-bold rounded shadow transition">
                        Catat Kunjungan
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white text-gray-600 py-8 border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-sm">&copy; <?= date('Y') ?> <?= htmlspecialchars($schoolName) ?>. All rights reserved.</p>
        </div>
    </footer>



    <!-- Scripts -->
    <script>
        // Autocomplete Logic
        const searchInput = document.getElementById('searchName');
        const suggestionsBox = document.getElementById('suggestions');
        const selectedSiswaId = document.getElementById('selectedSiswaId');

        let debounceTimer;

        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            const query = this.value;

            if (query.length < 2) {
                suggestionsBox.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch(`/e-TU/api/get_siswa.php?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if (data.length > 0) {
                            suggestionsBox.classList.remove('hidden');
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-2 hover:bg-emerald-50 cursor-pointer text-sm border-b border-gray-100 last:border-0';
                                div.textContent = item.label;
                                div.onclick = function () {
                                    searchInput.value = item.value;
                                    selectedSiswaId.value = item.id;
                                    suggestionsBox.classList.add('hidden');
                                };
                                suggestionsBox.appendChild(div);
                            });
                        } else {
                            suggestionsBox.classList.add('hidden');
                        }
                    });
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function (e) {
            if (e.target !== searchInput && e.target !== suggestionsBox) {
                suggestionsBox.classList.add('hidden');
            }
        });

        // Financial Check Logic
        document.getElementById('financeForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const id = selectedSiswaId.value;
            if (!id) {
                alert('Silakan pilih siswa dari daftar rekomendasi.');
                return;
            }

            const btn = document.getElementById('btnCek');
            const originalText = btn.innerText;
            btn.innerText = 'Memuat...';
            btn.disabled = true;

            const resDiv = document.getElementById('financeResult');
            resDiv.classList.add('hidden'); // Hide previous result

            fetch('/e-TU/api/check_tagihan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ siswa_id: id })
            })
                .then(res => res.json())
                .then(data => {
                    btn.innerText = originalText;
                    btn.disabled = false;

                    if (data.success) {
                        resDiv.classList.remove('hidden');

                        // --- 1. Basic Info & Summary ---
                        document.getElementById('resName').innerText = data.student;

                        const fmt = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                        document.getElementById('resTotal').innerText = fmt(data.summary.total_tagihan);
                        document.getElementById('resPaid').innerText = fmt(data.summary.total_terbayar);
                        document.getElementById('resSisa').innerText = fmt(data.summary.total_sisa);

                        // --- 2. Add Timestamp & Details ---
                        // Clear previous details if any
                        const existingDetails = document.getElementById('billDetailsList');
                        if (existingDetails) existingDetails.remove();
                        const existingTime = document.getElementById('dataTime');
                        if (existingTime) existingTime.remove();

                        // Timestamp
                        const timeP = document.createElement('p');
                        timeP.id = 'dataTime';
                        timeP.className = 'text-xs text-gray-400 mt-2 italic';
                        timeP.innerText = 'Data per tanggal: ' + data.server_time + ' WIB';
                        resDiv.appendChild(timeP);

                        // Bill List
                        if (data.details && data.details.length > 0) {
                            const listDiv = document.createElement('div');
                            listDiv.id = 'billDetailsList';
                            listDiv.className = 'mt-4 text-left border-t border-gray-200 pt-4';

                            let html = '<h4 class="font-bold text-gray-700 text-sm mb-2">Rincian Tagihan Belum Lunas:</h4>';
                            html += '<ul class="space-y-2">';

                            data.details.forEach(bill => {
                                html += `
                                <li class="flex justify-between items-center bg-white p-3 border border-gray-100 rounded-lg shadow-sm">
                                    <div>
                                        <div class="font-semibold text-sm text-gray-800">${bill.judul_tagihan}</div>
                                        <div class="text-xs text-gray-400 mb-1">
                                            <i class="far fa-calendar-alt mr-1"></i> ${new Date(bill.created_at).toLocaleDateString('id-ID')}
                                        </div>
                                        <div class="text-xs text-gray-500">Total: ${fmt(bill.total_tagihan)}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">Sisa</div>
                                        <div class="font-bold text-red-600 text-sm">${fmt(bill.sisa_tagihan)}</div>
                                    </div>
                                </li>
                            `;
                            });
                            html += '</ul>';
                            listDiv.innerHTML = html;
                            resDiv.appendChild(listDiv);
                        } else if (data.summary.total_sisa == 0) {
                            const listDiv = document.createElement('div');
                            listDiv.id = 'billDetailsList';
                            listDiv.className = 'mt-4 pt-4 border-t border-gray-200';
                            listDiv.innerHTML = '<div class="text-emerald-600 font-bold"><i class="fas fa-check-circle"></i> Tidak ada tunggakan.</div>';
                            resDiv.appendChild(listDiv);
                        }

                    } else {
                        // Handle Error / Service Closed
                        alert(data.message || 'Gagal mengambil data.');
                    }
                })
                .catch(err => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    console.error(err);
                    alert('Terjadi kesalahan koneksi.');
                });
        });
    </script>

</body>

</html>
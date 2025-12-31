<?php
// AJAX handlers MUST be at the very top before any HTML output
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/TemplateSurat.php';
require_once __DIR__ . '/../../classes/Siswa.php';

// Now load the page content
$pageTitle = 'Surat Generator - Persuratan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';

checkPermission('persuratan');

// Handle AJAX requests AFTER permission check
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');

    $templateSurat = new TemplateSurat();
    $siswa = new Siswa();

    try {
        if ($_GET['ajax'] === 'get_template' && isset($_GET['id'])) {
            $template = $templateSurat->getById($_GET['id']);
            if ($template) {
                $variabel = $templateSurat->parseVariabel($template['konten_template']);
                $nomorSurat = $templateSurat->generateNomorSurat($template['kode_surat']);
                echo json_encode([
                    'success' => true,
                    'template' => $template,
                    'variabel' => $variabel,
                    'nomor_surat' => $nomorSurat
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Template tidak ditemukan']);
            }
            exit;
        }

        if ($_GET['ajax'] === 'search_siswa' && isset($_GET['q'])) {
            $results = $siswa->search($_GET['q']);
            $data = array_map(function ($s) {
                return [
                    'id' => $s['id'] ?? '',
                    'nisn' => $s['nisn'] ?? '',
                    'nama_lengkap' => $s['nama_lengkap'] ?? '',
                    'kelas_nama' => $s['nama_kelas'] ?? '-',
                    'tempat_lahir' => $s['tempat_lahir'] ?? '',
                    'tanggal_lahir' => $s['tanggal_lahir'] ?? '',
                    'alamat' => $s['alamat'] ?? '',
                    'jenis_kelamin' => $s['jenis_kelamin'] ?? ''
                ];
            }, $results);
            echo json_encode(['success' => true, 'data' => array_slice($data, 0, 10)]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid ajax request']);
        exit;

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

$templateSurat = new TemplateSurat();
$templates = $templateSurat->getAll();
?>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/persuratan/index.php" class="hover:text-blue-600">Persuratan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Surat Generator</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-file-alt text-white text-xl"></i>
                    </div>
                    Surat Generator
                </h2>
                <p class="text-gray-600 mt-2">Buat dan download surat otomatis dari template</p>
            </div>
            <a href="template.php"
                class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Kelola Template
            </a>
        </div>
    </div>

    <?php if (count($templates) === 0): ?>
        <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-8 text-center">
            <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-signature text-amber-500 text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-amber-800 mb-2">Belum Ada Template Surat!</h3>
            <p class="text-amber-700 mb-6">Buat template surat terlebih dahulu.</p>
            <a href="template.php"
                class="inline-flex items-center px-8 py-4 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-bold text-lg">
                <i class="fas fa-plus mr-2"></i>Buat Template
            </a>
        </div>
    <?php else: ?>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            <div class="xl:col-span-3 space-y-6">

                <!-- STEP 1 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-blue-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</span>
                        Pilih Template Surat
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach ($templates as $t): ?>
                            <div class="template-card p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all"
                                data-id="<?= $t['id'] ?>" onclick="selectTemplate(<?= $t['id'] ?>)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($t['nama_template']) ?></h4>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($t['kode_surat']) ?></p>
                                    </div>
                                    <i class="fas fa-check-circle text-blue-600 text-2xl hidden selected-icon"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-green-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold">2</span>
                        Isi Data Surat
                    </h3>
                    <div id="formPlaceholder"
                        class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <i class="fas fa-hand-pointer text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-500 font-semibold">Klik template di atas</p>
                    </div>
                    <div id="dataForm" class="space-y-4 hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Surat</label>
                                <input type="text" id="field_NOMOR_SURAT"
                                    class="w-full px-4 py-3 border rounded-lg font-mono"
                                    placeholder="Ketik nomor surat manual...">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Surat</label>
                                <input type="date" id="field_TANGGAL_SURAT" value="<?= date('Y-m-d') ?>"
                                    class="w-full px-4 py-3 border rounded-lg" onchange="updatePreview()">
                            </div>
                        </div>
                        <div id="dynamicFields"></div>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="rounded-xl shadow-lg p-6"
                    style="background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-white text-purple-600 rounded-full flex items-center justify-center font-bold">3</span>
                        Download / Cetak
                    </h3>
                    <div id="downloadPlaceholder" class="text-center py-4 text-purple-200">
                        <p><i class="fas fa-clock mr-2"></i>Pilih template dan isi data dulu</p>
                    </div>
                    <div id="downloadActions" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button onclick="downloadAsWord()"
                            class="px-4 py-5 bg-white hover:bg-blue-50 text-blue-700 rounded-xl font-bold shadow-lg flex flex-col items-center gap-2">
                            <i class="fas fa-file-word text-4xl"></i>
                            <span>Download Word</span>
                        </button>
                        <button onclick="downloadAsPDF()"
                            class="px-4 py-5 bg-white hover:bg-red-50 text-red-600 rounded-xl font-bold shadow-lg flex flex-col items-center gap-2">
                            <i class="fas fa-file-pdf text-4xl"></i>
                            <span>Download PDF</span>
                        </button>
                        <button onclick="printSurat()"
                            class="px-4 py-5 bg-white hover:bg-gray-50 text-gray-700 rounded-xl font-bold shadow-lg flex flex-col items-center gap-2">
                            <i class="fas fa-print text-4xl"></i>
                            <span>Cetak</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Preview -->
            <div class="xl:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-eye text-purple-600 mr-2"></i>Preview
                        Surat</h3>
                    <div id="previewContainer" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg"
                        style="height: 600px; overflow-y: auto;">
                        <div id="previewContent" class="p-6 preview-content">
                            <div class="text-center text-gray-400 py-16">
                                <i class="fas fa-file-alt text-6xl mb-4 opacity-30"></i>
                                <p>Pilih template dan isi data</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    let currentTemplate = null;

    function selectTemplate(id) {
        document.querySelectorAll('.template-card').forEach(card => {
            card.classList.remove('border-blue-600', 'bg-blue-100');
            card.classList.add('border-gray-200');
            card.querySelector('.selected-icon').classList.add('hidden');
        });

        const selected = document.querySelector(`.template-card[data-id="${id}"]`);
        if (selected) {
            selected.classList.remove('border-gray-200');
            selected.classList.add('border-blue-600', 'bg-blue-100');
            selected.querySelector('.selected-icon').classList.remove('hidden');
        }

        fetch(`?ajax=get_template&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentTemplate = data.template;
                    document.getElementById('field_NOMOR_SURAT').value = data.nomor_surat;
                    generateDynamicFields(data.variabel);

                    document.getElementById('formPlaceholder').classList.add('hidden');
                    document.getElementById('dataForm').classList.remove('hidden');
                    document.getElementById('downloadPlaceholder').classList.add('hidden');
                    document.getElementById('downloadActions').classList.remove('hidden');
                    document.getElementById('previewContainer').classList.remove('bg-gray-50', 'border-dashed');
                    document.getElementById('previewContainer').classList.add('bg-white', 'border-solid');

                    updatePreview();
                } else {
                    alert('Error: ' + (data.message || 'Gagal load template'));
                }
            })
            .catch(err => alert('Error: ' + err.message));
    }

    function generateDynamicFields(variabel) {
        const container = document.getElementById('dynamicFields');
        container.innerHTML = '';

        const skipFields = ['NOMOR_SURAT', 'TANGGAL_SURAT'];
        const siswaFields = ['NAMA_SISWA', 'NISN', 'KELAS', 'TEMPAT_LAHIR', 'TANGGAL_LAHIR', 'ALAMAT_SISWA', 'JENIS_KELAMIN'];

        // Check if any student fields are used in this template
        const usedSiswaFields = siswaFields.filter(f => variabel.includes(f));

        if (usedSiswaFields.length > 0) {
            container.innerHTML += `
            <div class="p-4 bg-blue-50 rounded-lg border-2 border-blue-300 mb-4">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-blue-800 mb-2">
                        <i class="fas fa-user-graduate mr-2"></i>Isi Data
                    </label>
                    <div class="relative">
                        <input type="text" id="searchSiswa" 
                            placeholder="Ketik Nama/NISN untuk isi otomatis..." 
                            class="w-full px-4 py-2 border-2 border-blue-300 rounded-lg focus:outline-none focus:border-blue-500" 
                            onkeyup="searchSiswa(this.value)">
                        <div id="siswaResults" class="absolute z-50 w-full mt-1"></div>
                    </div>
                    <p class="text-xs text-blue-600 mt-1">* Cari siswa untuk mengisi otomatis, atau ketik manual di bawah ini.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-white p-3 rounded border border-blue-200">
                    ${usedSiswaFields.map(f => {
                const label = f.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
                return `
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">${label}</label>
                            <input type="text" id="field_${f}" class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" oninput="updatePreview()">
                        </div>`;
            }).join('')}
                </div>
            </div>
        `;
        }

        variabel.forEach(v => {
            if (skipFields.includes(v) || siswaFields.includes(v)) return;
            const label = v.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
            container.innerHTML += `
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">${label}</label>
                <input type="text" id="field_${v}" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg" placeholder="Masukkan ${label.toLowerCase()}..." oninput="updatePreview()">
            </div>
        `;
        });
    }

    function searchSiswa(query) {
        const resultsContainer = document.getElementById('siswaResults');
        if (query.length < 2) { resultsContainer.innerHTML = ''; return; }

        resultsContainer.innerHTML = '<p class="text-blue-600 bg-white p-2 border rounded shadow"><i class="fas fa-spinner fa-spin mr-2"></i>Mencari...</p>';

        fetch(`?ajax=search_siswa&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    let html = '<div class="bg-white border-2 border-blue-300 rounded-lg divide-y shadow-lg max-h-48 overflow-y-auto">';
                    data.data.forEach(s => {
                        html += `<div class="p-3 hover:bg-blue-100 cursor-pointer" onclick='selectSiswa(${JSON.stringify(s)})'><p class="font-bold">${s.nama_lengkap}</p><p class="text-sm text-gray-600">NISN: ${s.nisn}</p></div>`;
                    });
                    html += '</div>';
                    resultsContainer.innerHTML = html;
                } else {
                    resultsContainer.innerHTML = '<p class="text-gray-500 bg-white p-2 border rounded shadow">Tidak ditemukan</p>';
                }
            });
    }

    function selectSiswa(siswa) {
        document.getElementById('siswaResults').innerHTML = '';
        document.getElementById('searchSiswa').value = '';

        const fields = {
            'field_NAMA_SISWA': siswa.nama_lengkap,
            'field_NISN': siswa.nisn,
            'field_KELAS': siswa.kelas_nama || '-',
            'field_TEMPAT_LAHIR': siswa.tempat_lahir || '-',
            'field_TANGGAL_LAHIR': siswa.tanggal_lahir || '-',
            'field_ALAMAT_SISWA': siswa.alamat || '-',
            'field_JENIS_KELAMIN': siswa.jenis_kelamin || '-'
        };

        for (const [id, val] of Object.entries(fields)) {
            const el = document.getElementById(id);
            if (el) {
                el.value = val;
                // Trigger animation or highlight to show it changed
                el.classList.add('bg-green-50', 'border-green-400');
                setTimeout(() => el.classList.remove('bg-green-50', 'border-green-400'), 1000);
            }
        }
        updatePreview();
    }

    function updatePreview() {
        if (!currentTemplate) return;
        let konten = currentTemplate.konten_template;

        document.querySelectorAll('[id^="field_"]').forEach(field => {
            const varName = field.id.replace('field_', '');
            let value = field.value || `<mark style="background:#fef3c7;padding:1px 4px;">[${varName}]</mark>`;
            if (varName === 'TANGGAL_SURAT' && field.value) {
                const d = new Date(field.value);
                value = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            }
            konten = konten.replace(new RegExp(`<span[^>]*>\\{\\{${varName}\\}\\}</span>`, 'gi'), value);
            konten = konten.replace(new RegExp(`\\{\\{${varName}\\}\\}`, 'gi'), value);
        });
        document.getElementById('previewContent').innerHTML = konten;
    }

    function getDocContent() {
        updatePreview();
        return `<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Surat</title>
<style>
@page { size: 210mm 330mm; margin: 2cm; } /* F4 Size */
@media print {
    html, body { width: 210mm; height: 330mm; margin: 0; padding: 0; }
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; margin: 0; }
table { border-collapse: collapse; width: 100%; }
mark { background: transparent !important; }
</style>
</head><body>
${document.getElementById('previewContent').innerHTML}
</body></html>`;
    }

    function downloadAsWord() {
        const content = getDocContent();
        const blob = new Blob(['\ufeff', content], { type: 'application/msword' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `Surat_${currentTemplate.kode_surat}_${Date.now()}.doc`;
        a.click();
    }

    function downloadAsPDF() {
        printSurat();
        alert('💡 Pada dialog print, pilih "Save as PDF" untuk menyimpan sebagai PDF.');
    }

    function printSurat() {
        const content = getDocContent();
        const win = window.open('', '_blank');
        win.document.write(content);
        win.document.close();
        setTimeout(() => { win.focus(); win.print(); }, 500);
    }
</script>

<style>
    .preview-content {
        font-family: 'Times New Roman', serif;
        font-size: 12pt;
        line-height: 1.6;
    }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<?php
$pageTitle = 'Template Surat - Persuratan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/TemplateSurat.php';

checkPermission('persuratan');

$templateSurat = new TemplateSurat();

// Handle image upload for editor
if (isset($_POST['upload_image']) && isset($_FILES['image'])) {
    header('Content-Type: application/json');

    $uploadDir = __DIR__ . '/../../uploads/surat-images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed) && $file['size'] <= 5 * 1024 * 1024) {
        $newName = 'img_' . time() . '_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            echo json_encode(['url' => '/e-TU/uploads/surat-images/' . $newName]);
        } else {
            echo json_encode(['error' => 'Upload gagal']);
        }
    } else {
        echo json_encode(['error' => 'File tidak valid atau terlalu besar']);
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'create') {
            $data = [
                'nama_template' => clean($_POST['nama_template']),
                'kode_surat' => strtoupper(clean($_POST['kode_surat'])),
                'kategori' => $_POST['kategori'],
                'konten_template' => $_POST['konten_template'],
                'variabel' => json_encode($templateSurat->parseVariabel($_POST['konten_template']))
            ];

            if ($templateSurat->create($data)) {
                redirect($_SERVER['PHP_SELF'], 'Template berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'update') {
            $data = [
                'nama_template' => clean($_POST['nama_template']),
                'kode_surat' => strtoupper(clean($_POST['kode_surat'])),
                'kategori' => $_POST['kategori'],
                'konten_template' => $_POST['konten_template'],
                'variabel' => json_encode($templateSurat->parseVariabel($_POST['konten_template']))
            ];

            if ($templateSurat->update($_POST['id'], $data)) {
                redirect($_SERVER['PHP_SELF'], 'Template berhasil diupdate!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($templateSurat->delete($_POST['id'])) {
                redirect($_SERVER['PHP_SELF'], 'Template berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

$templates = $templateSurat->getAll();
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/persuratan/index.php" class="hover:text-blue-600">Persuratan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Template Surat</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-file-signature text-white text-xl"></i>
                    </div>
                    Template Surat
                </h2>
                <p class="text-gray-600 mt-2">Buat template surat profesional dengan kop surat & tanda tangan</p>
            </div>
            <button onclick="openTemplateModal()"
                class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-700 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Template Baru
            </button>
        </div>
    </div>

    <!-- Quick Tips -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 text-sm text-blue-800">
        <div class="flex items-start">
            <div class="flex-shrink-0 mt-0.5">
                <i class="fas fa-lightbulb text-blue-500 text-lg"></i>
            </div>
            <div class="ml-3 w-full">
                <h4 class="font-bold mb-2">Panduan Penggunaan Template:</h4>
                <p class="mb-2">Gunakan placeholder dengan format <code>{{NAMA_VARIABEL}}</code>. Saat surat dibuat,
                    placeholder otomatis diganti data asli.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 bg-white p-3 rounded border border-blue-100">
                    <div>
                        <span class="font-semibold block text-blue-600 mb-1">Variabel Surat:</span>
                        <ul class="list-disc ml-4 space-y-1 text-xs text-gray-600">
                            <li><code class="bg-gray-100 px-1 rounded">{{NOMOR_SURAT}}</code> : Nomor otomatis/manual yg
                                diinput</li>
                            <li><code class="bg-gray-100 px-1 rounded">{{TANGGAL_SURAT}}</code> : Tanggal surat (format
                                Indo)</li>
                        </ul>
                    </div>
                    <div>
                        <span class="font-semibold block text-blue-600 mb-1">Variabel Siswa (Otomatis dr
                            Database):</span>
                        <ul class="list-disc ml-4 space-y-1 text-xs text-gray-600">
                            <li><code class="bg-gray-100 px-1 rounded">{{NAMA_SISWA}}</code>, <code
                                    class="bg-gray-100 px-1 rounded">{{NISN}}</code></li>
                            <li><code class="bg-gray-100 px-1 rounded">{{KELAS}}</code>, <code
                                    class="bg-gray-100 px-1 rounded">{{TEMPAT_LAHIR}}</code></li>
                            <li><code class="bg-gray-100 px-1 rounded">{{TANGGAL_LAHIR}}</code>, <code
                                    class="bg-gray-100 px-1 rounded">{{ALAMAT}}</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Total Template</p>
            <p class="text-2xl font-bold text-indigo-600"><?= count($templates) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Kesiswaan</p>
            <p class="text-2xl font-bold text-blue-600">
                <?= count(array_filter($templates, fn($t) => $t['kategori'] === 'Kesiswaan')) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Kepegawaian</p>
            <p class="text-2xl font-bold text-green-600">
                <?= count(array_filter($templates, fn($t) => $t['kategori'] === 'Kepegawaian')) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-4">
            <p class="text-gray-600 text-sm">Umum</p>
            <p class="text-2xl font-bold text-amber-600">
                <?= count(array_filter($templates, fn($t) => $t['kategori'] === 'Umum')) ?></p>
        </div>
    </div>

    <!-- Template List -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Daftar Template</h3>
            <input type="text" id="searchInput" placeholder="Cari template..."
                class="px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <?php if (count($templates) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="templateGrid">
                <?php foreach ($templates as $t): ?>
                    <div class="template-item border rounded-lg overflow-hidden hover:shadow-lg transition-all">
                        <div class="bg-gray-100 p-4 h-48 overflow-hidden border-b">
                            <div class="bg-white p-3 rounded shadow-sm h-full overflow-hidden text-xs">
                                <?= $t['konten_template'] ?>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="font-bold text-gray-800"><?= htmlspecialchars($t['nama_template']) ?></h4>
                                    <p class="text-sm text-gray-500">Kode: <?= htmlspecialchars($t['kode_surat']) ?></p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full <?=
                                    $t['kategori'] === 'Kesiswaan' ? 'bg-blue-100 text-blue-700' :
                                    ($t['kategori'] === 'Kepegawaian' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700')
                                    ?>">
                                    <?= $t['kategori'] ?>
                                </span>
                            </div>

                            <div class="flex gap-2 mt-3">
                                <button onclick="editTemplate(<?= $t['id'] ?>)"
                                    class="flex-1 px-3 py-2 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded text-sm font-semibold">
                                    <i class="fas fa-edit mr-1"></i>Edit
                                </button>
                                <button onclick="previewTemplate(<?= $t['id'] ?>)"
                                    class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded text-sm">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="deleteTemplate(<?= $t['id'] ?>)"
                                    class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-file-alt text-5xl mb-4 opacity-50"></i>
                <p class="text-lg font-semibold">Belum ada template</p>
                <p class="text-sm">Klik "Template Baru" untuk membuat template surat profesional</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Modal Editor (Full Screen) -->
<div id="modalEditor" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="absolute inset-4 bg-white rounded-xl shadow-2xl flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Buat Template Baru</h3>
            <div class="flex items-center gap-2">
                <div class="dropdown relative">
                    <button type="button" onclick="toggleDropdown('insertDropdown')"
                        class="px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg text-sm font-semibold">
                        <i class="fas fa-code mr-1"></i>Sisipkan Template
                    </button>
                    <div id="insertDropdown"
                        class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border z-50">
                        <button onclick="insertKopSurat()"
                            class="w-full text-left px-4 py-3 hover:bg-gray-100 border-b">
                            <i class="fas fa-building mr-2 text-blue-600"></i>Kop Surat + Logo
                        </button>
                        <button onclick="insertTTD1()" class="w-full text-left px-4 py-3 hover:bg-gray-100 border-b">
                            <i class="fas fa-signature mr-2 text-green-600"></i>TTD Kepala Sekolah
                        </button>
                        <button onclick="insertTTD2()" class="w-full text-left px-4 py-3 hover:bg-gray-100 border-b">
                            <i class="fas fa-users mr-2 text-amber-600"></i>TTD 2 Kolom
                        </button>
                        <button onclick="insertVariable()" class="w-full text-left px-4 py-3 hover:bg-gray-100">
                            <i class="fas fa-brackets-curly mr-2 text-purple-600"></i>Variabel {{...}}
                        </button>
                    </div>
                </div>
                <button type="button" onclick="closeEditorModal()" class="text-gray-500 hover:text-gray-700 ml-2">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" id="templateForm" class="flex-1 flex flex-col overflow-hidden">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="templateId" value="">

            <!-- Top fields -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 border-b bg-gray-50">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Template *</label>
                    <input type="text" name="nama_template" id="namaTemplate" required
                        placeholder="Surat Keterangan Siswa"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Surat *</label>
                    <input type="text" name="kode_surat" id="kodeSurat" required placeholder="SKS"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg uppercase text-sm"
                        style="text-transform: uppercase;">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kategori</label>
                    <select name="kategori" id="kategori"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="Umum">Umum</option>
                        <option value="Kesiswaan">Kesiswaan</option>
                        <option value="Kepegawaian">Kepegawaian</option>
                        <option value="Keuangan">Keuangan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Variabel Terdeteksi</label>
                    <div id="detectedVars" class="px-3 py-2 bg-gray-100 rounded-lg text-sm text-gray-600 truncate">-
                    </div>
                </div>
            </div>

            <!-- Editor Area -->
            <div class="flex-1 p-4 overflow-auto bg-gray-100">
                <textarea name="konten_template" id="kontenTemplate"></textarea>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3 p-4 border-t bg-gray-50">
                <button type="button" onclick="closeEditorModal()"
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan Template
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Variable Picker -->
<div id="modalVariable" class="modal-overlay">
    <div class="modal-content max-w-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Sisipkan Variabel</h3>

        <div class="grid grid-cols-2 gap-2 mb-4">
            <button onclick="insertVar('NOMOR_SURAT')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{NOMOR_SURAT}}</button>
            <button onclick="insertVar('TANGGAL_SURAT')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{TANGGAL_SURAT}}</button>
            <button onclick="insertVar('NAMA_SISWA')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{NAMA_SISWA}}</button>
            <button onclick="insertVar('NISN')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{NISN}}</button>
            <button onclick="insertVar('KELAS')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{KELAS}}</button>
            <button onclick="insertVar('NAMA_PEGAWAI')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{NAMA_PEGAWAI}}</button>
            <button onclick="insertVar('NIP')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{NIP}}</button>
            <button onclick="insertVar('JABATAN')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{JABATAN}}</button>
            <button onclick="insertVar('KEPERLUAN')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{KEPERLUAN}}</button>
            <button onclick="insertVar('ALAMAT')"
                class="p-2 bg-gray-100 hover:bg-blue-100 rounded text-sm text-left">{{ALAMAT}}</button>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Variabel Custom:</label>
            <div class="flex gap-2">
                <input type="text" id="customVar" placeholder="NAMA_VARIABEL"
                    class="flex-1 px-3 py-2 border rounded-lg uppercase text-sm">
                <button onclick="insertCustomVar()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Sisipkan</button>
            </div>
        </div>

        <button onclick="closeModal('modalVariable')" class="w-full px-4 py-2 bg-gray-200 rounded-lg">Tutup</button>
    </div>
</div>

<!-- Modal Preview -->
<div id="modalPreview" class="modal-overlay">
    <div class="modal-content max-w-4xl">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold">Preview Template</h3>
            <button onclick="closeModal('modalPreview')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="previewContent" class="bg-white border rounded-lg p-8 min-h-96"
            style="font-family: 'Times New Roman', serif;">
        </div>
    </div>
</div>

<!-- Summernote JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    const templatesData = <?= json_encode($templates) ?>;
    let editorInitialized = false;

    function initEditor(content = '') {
        if (editorInitialized) {
            $('#kontenTemplate').summernote('destroy');
        }

        $('#kontenTemplate').summernote({
            height: 500,
            placeholder: 'Tulis konten template surat di sini...',
            fontNames: ['Arial', 'Times New Roman', 'Courier New', 'Georgia', 'Verdana'],
            fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28', '32', '36'],
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5'],
            callbacks: {
                onImageUpload: function (files) {
                    for (let i = 0; i < files.length; i++) {
                        uploadImage(files[i]);
                    }
                },
                onChange: function (contents) {
                    updateDetectedVars(contents);
                }
            }
        });

        if (content) {
            $('#kontenTemplate').summernote('code', content);
        }

        editorInitialized = true;
    }

    function uploadImage(file) {
        const formData = new FormData();
        formData.append('upload_image', '1');
        formData.append('image', file);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.url) {
                    $('#kontenTemplate').summernote('insertImage', data.url);
                } else {
                    alert(data.error || 'Upload gagal');
                }
            });
    }

    function updateDetectedVars(content) {
        const matches = content.match(/\{\{([A-Z_]+)\}\}/g) || [];
        const vars = [...new Set(matches)];
        document.getElementById('detectedVars').textContent = vars.length > 0 ? vars.join(', ') : '-';
    }

    function openTemplateModal(templateData = null) {
        document.getElementById('modalEditor').classList.remove('hidden');

        setTimeout(() => {
            if (templateData) {
                document.getElementById('modalTitle').textContent = 'Edit Template';
                document.getElementById('formAction').value = 'update';
                document.getElementById('templateId').value = templateData.id;
                document.getElementById('namaTemplate').value = templateData.nama_template;
                document.getElementById('kodeSurat').value = templateData.kode_surat;
                document.getElementById('kategori').value = templateData.kategori;
                initEditor(templateData.konten_template);
            } else {
                document.getElementById('modalTitle').textContent = 'Buat Template Baru';
                document.getElementById('formAction').value = 'create';
                document.getElementById('templateId').value = '';
                document.getElementById('templateForm').reset();
                initEditor('');
            }
        }, 100);
    }

    function closeEditorModal() {
        document.getElementById('modalEditor').classList.add('hidden');
        if (editorInitialized) {
            $('#kontenTemplate').summernote('destroy');
            editorInitialized = false;
        }
    }

    function editTemplate(id) {
        const template = templatesData.find(t => t.id == id);
        if (template) {
            openTemplateModal(template);
        }
    }

    function previewTemplate(id) {
        const template = templatesData.find(t => t.id == id);
        if (template) {
            document.getElementById('previewContent').innerHTML = template.konten_template;
            openModal('modalPreview');
        }
    }

    function deleteTemplate(id) {
        if (confirm('Hapus template ini?')) {
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

    function toggleDropdown(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown > div').forEach(d => d.classList.add('hidden'));
        }
    });

    function insertKopSurat() {
        const kop = `
<table width="100%" style="border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px;">
    <tr>
        <td width="100" style="text-align: center; vertical-align: middle;">
            <img src="/e-TU/assets/images/logo.png" alt="Logo" style="max-height: 80px; max-width: 80px;">
        </td>
        <td style="text-align: center;">
            <p style="margin: 0; font-size: 14pt;">YAYASAN PENDIDIKAN AL IHSAN</p>
            <p style="margin: 0; font-size: 18pt; font-weight: bold;">MA AL IHSAN</p>
            <p style="margin: 2px 0; font-size: 10pt;">Jl. Contoh Alamat No. 123, Kota, Provinsi</p>
            <p style="margin: 2px 0; font-size: 10pt;">Telp: (021) 123456 | Email: info@maalihsan.sch.id</p>
        </td>
        <td width="100"></td>
    </tr>
</table>
<p>&nbsp;</p>`;
        $('#kontenTemplate').summernote('pasteHTML', kop);
        toggleDropdown('insertDropdown');
    }

    function insertTTD1() {
        const ttd = `
<p>&nbsp;</p>
<table width="100%">
    <tr>
        <td width="60%"></td>
        <td style="text-align: center;">
            <p style="margin: 0;">Kota, {{TANGGAL_SURAT}}</p>
            <p style="margin: 0;">Kepala Madrasah,</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">Nama Kepala Sekolah</p>
            <p style="margin: 0;">NIP. 123456789</p>
        </td>
    </tr>
</table>`;
        $('#kontenTemplate').summernote('pasteHTML', ttd);
        toggleDropdown('insertDropdown');
    }

    function insertTTD2() {
        const ttd = `
<p>&nbsp;</p>
<table width="100%">
    <tr>
        <td width="50%" style="text-align: center;">
            <p style="margin: 0;">Mengetahui,</p>
            <p style="margin: 0;">Kepala Madrasah</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">Nama Kepala</p>
            <p style="margin: 0;">NIP. 123456789</p>
        </td>
        <td width="50%" style="text-align: center;">
            <p style="margin: 0;">Kota, {{TANGGAL_SURAT}}</p>
            <p style="margin: 0;">Kepala Tata Usaha</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0;">&nbsp;</p>
            <p style="margin: 0; font-weight: bold; text-decoration: underline;">Nama Ka. TU</p>
            <p style="margin: 0;">NIP. 987654321</p>
        </td>
    </tr>
</table>`;
        $('#kontenTemplate').summernote('pasteHTML', ttd);
        toggleDropdown('insertDropdown');
    }

    function insertVariable() {
        toggleDropdown('insertDropdown');
        openModal('modalVariable');
    }

    function insertVar(varName) {
        $('#kontenTemplate').summernote('pasteHTML', `<span style="background-color: #e3f2fd; padding: 2px 4px; border-radius: 3px; color: #1976d2;">{{${varName}}}</span>`);
        closeModal('modalVariable');
    }

    function insertCustomVar() {
        const varName = document.getElementById('customVar').value.toUpperCase().replace(/\s+/g, '_');
        if (varName) {
            insertVar(varName);
            document.getElementById('customVar').value = '';
        }
    }

    // Search
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.template-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Submit - sync editor content
    document.getElementById('templateForm').addEventListener('submit', function (e) {
        const content = $('#kontenTemplate').summernote('code');
        document.getElementById('kontenTemplate').value = content;
    });
</script>

<style>
    .note-editor {
        border: 1px solid #ddd !important;
        border-radius: 8px !important;
    }

    .note-editing-area {
        background: white !important;
    }

    .note-editable {
        font-family: 'Times New Roman', serif !important;
        font-size: 12pt !important;
        line-height: 1.6 !important;
    }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
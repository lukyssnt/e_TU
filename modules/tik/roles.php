<?php
$pageTitle = 'Role & Permission';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Role.php';

checkPermission('tik');

$role = new Role();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

        // If 'all' is selected, just save 'all'
        if (in_array('all', $permissions)) {
            $permissions = ['all'];
        }

        if ($_POST['action'] === 'create') {
            $data = [
                'role_name' => clean($_POST['role_name']),
                'permissions' => $permissions,
                'description' => clean($_POST['description'])
            ];
            if ($role->create($data)) {
                redirect($_SERVER['REQUEST_URI'], 'Role berhasil ditambahkan!', 'success');
            }
        } elseif ($_POST['action'] === 'update') {
            $id = $_POST['id'];
            $data = [
                'role_name' => clean($_POST['role_name']),
                'permissions' => $permissions,
                'description' => clean($_POST['description'])
            ];
            if ($role->update($id, $data)) {
                redirect($_SERVER['REQUEST_URI'], 'Role berhasil diperbarui!', 'success');
            }
        } elseif ($_POST['action'] === 'delete') {
            if ($role->delete($_POST['id'])) {
                redirect($_SERVER['REQUEST_URI'], 'Role berhasil dihapus!', 'success');
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "');</script>";
    }
}

$allRoles = $role->getAll();
$totalRoles = count($allRoles);

// Define available modules for permissions
$modules = [
    'dashboard' => 'Dashboard',
    'kepegawaian' => 'Kepegawaian',
    'keuangan' => 'Keuangan',
    'sarpras' => 'Sarana Prasarana',
    'kehumasan' => 'Kehumasan',
    'persuratan' => 'Persuratan',
    'kesiswaan' => 'Kesiswaan',
    'layanan_khusus' => 'Layanan Khusus',
    'tik' => 'TIK & Pengaturan'
];
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Role & Permission</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-shield text-white text-xl"></i>
                    </div>
                    Role & Permission
                </h2>
                <p class="text-gray-600 mt-2">Kelola hak akses pengguna sistem</p>
            </div>
            <button onclick="openModalAdd()"
                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-lg font-semibold shadow-lg">
                <i class="fas fa-plus mr-2"></i>Tambah Role
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Role</p>
                    <p class="text-2xl font-bold text-purple-600"><?= $totalRoles ?></p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Role</h3>
            <div class="flex gap-2">
                <input type="text" id="searchInput" placeholder="Cari role..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Nama Role</th>
                        <th class="text-left">Deskripsi</th>
                        <th class="text-left">Permissions</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($allRoles) > 0): ?>
                        <?php foreach ($allRoles as $index => $r):
                            $perms = json_decode($r['permissions'], true) ?? [];
                            $isSuperAdmin = $r['role_name'] === 'Super Admin';
                            ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="font-medium text-gray-900"><?= $index + 1 ?></td>
                                <td class="font-semibold text-purple-700"><?= htmlspecialchars($r['role_name']) ?></td>
                                <td class="text-gray-600"><?= htmlspecialchars($r['description']) ?></td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        <?php if (in_array('all', $perms)): ?>
                                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold">ALL
                                                ACCESS</span>
                                        <?php else: ?>
                                            <?php foreach ($perms as $p): ?>
                                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">
                                                    <?= isset($modules[$p]) ? $modules[$p] : $p ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick='editRole(<?= json_encode($r) ?>)'
                                            class="p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if (!$isSuperAdmin): ?>
                                            <button onclick="deleteRole(<?= $r['id'] ?>)"
                                                class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                <div class="empty-state">
                                    <i class="fas fa-user-shield text-4xl mb-3 text-gray-300"></i>
                                    <p>Belum ada data role</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Modal Add/Edit -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800" id="modalTitle">Tambah Role</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST" id="formRole">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="roleId" value="">

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Role *</label>
                    <input type="text" name="role_name" id="role_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" id="description" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Permissions (Hak Akses)</label>
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="flex items-center mb-3 p-2 bg-red-50 rounded hover:bg-red-100 cursor-pointer">
                            <input type="checkbox" name="permissions[]" value="all" id="perm_all"
                                class="w-4 h-4 text-red-600 rounded focus:ring-red-500">
                            <span class="ml-2 font-bold text-red-700">FULL ACCESS (Super Admin)</span>
                        </label>

                        <div class="grid grid-cols-2 gap-2" id="module_perms">
                            <?php foreach ($modules as $key => $label):
                                if ($key === 'dashboard')
                                    continue; // Dashboard usually for everyone or specific
                                ?>
                                <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="<?= $key ?>"
                                        class="perm-check w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                                    <span class="ml-2 text-gray-700"><?= $label ?></span>
                                </label>
                            <?php endforeach; ?>
                            <!-- Always include dashboard implicitly or explicitly? Let's make it explicit -->
                            <label class="flex items-center p-2 hover:bg-white rounded cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="dashboard"
                                    class="perm-check w-4 h-4 text-purple-600 rounded focus:ring-purple-500">
                                <span class="ml-2 text-gray-700">Dashboard</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function openModalAdd() {
        document.getElementById('modalTitle').textContent = 'Tambah Role';
        document.getElementById('formAction').value = 'create';
        document.getElementById('roleId').value = '';
        document.getElementById('formRole').reset();

        // Uncheck all
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);

        openModal('modalAdd');
    }

    function editRole(data) {
        document.getElementById('modalTitle').textContent = 'Edit Role';
        document.getElementById('formAction').value = 'update';
        document.getElementById('roleId').value = data.id;

        document.getElementById('role_name').value = data.role_name;
        document.getElementById('description').value = data.description || '';

        // Handle permissions
        const perms = JSON.parse(data.permissions || '[]');
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
            cb.checked = perms.includes(cb.value);
        });

        openModal('modalAdd');
    }

    function deleteRole(id) {
        if (confirm('Apakah Anda yakin ingin menghapus role ini? User dengan role ini mungkin akan kehilangan akses.')) {
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

    // Handle "All Access" checkbox logic
    document.getElementById('perm_all').addEventListener('change', function () {
        const checks = document.querySelectorAll('.perm-check');
        if (this.checked) {
            checks.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
        } else {
            checks.forEach(cb => {
                cb.checked = false;
                cb.disabled = false;
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
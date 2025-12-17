<?php
$pageTitle = 'Manajemen User - TIK & Pengaturan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/User.php';

checkPermission('tik');

$userObj = new User();
$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $permissions = isset($_POST['permissions']) ? implode(',', $_POST['permissions']) : '';

        // If role is Administrator, force 'all' permission or handle as needed
        if ($_POST['role'] === 'Administrator') {
            $permissions = 'all';
        }

        switch ($_POST['action']) {
            case 'create':
                $result = $userObj->create([
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'full_name' => $_POST['full_name'],
                    'role' => $_POST['role'],
                    'permissions' => $permissions
                ]);

                if ($result === true) {
                    $message = 'User berhasil ditambahkan!';
                    $messageType = 'success';
                } else {
                    $message = $result; // Error message from class
                    $messageType = 'error';
                }
                break;

            case 'update':
                $result = $userObj->update($_POST['id'], [
                    'username' => $_POST['username'],
                    'password' => $_POST['password'], // Empty if not changing
                    'full_name' => $_POST['full_name'],
                    'role' => $_POST['role'],
                    'permissions' => $permissions
                ]);

                if ($result === true) {
                    $message = 'Data user berhasil diperbarui!';
                    $messageType = 'success';
                } else {
                    $message = is_string($result) ? $result : 'Gagal memperbarui data user!';
                    $messageType = 'error';
                }
                break;

            case 'delete':
                // Prevent deleting self
                if ($_POST['id'] == Session::get('user_id')) {
                    $message = 'Tidak dapat menghapus akun sendiri!';
                    $messageType = 'error';
                } else {
                    if ($userObj->delete($_POST['id'])) {
                        $message = 'User berhasil dihapus!';
                        $messageType = 'success';
                    } else {
                        $message = 'Gagal menghapus user!';
                        $messageType = 'error';
                    }
                }
                break;
        }
    }
}

// Get data
$users = $userObj->getAll();
$stats = $userObj->getStats();

// Define available permissions
$availablePermissions = [
    'kepegawaian' => 'Kepegawaian',
    'keuangan' => 'Keuangan',
    'sarpras' => 'Sarana Prasarana',
    'kehumasan' => 'Kehumasan',
    'persuratan' => 'Persuratan & Kearsipan',
    'kesiswaan' => 'Kesiswaan',
    'layanan' => 'Layanan Khusus',
    'tik' => 'TIK & Pengaturan'
];
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/tik/index.php" class="hover:text-blue-600">TIK & Pengaturan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Manajemen User</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-users-cog text-white text-xl"></i>
                    </div>
                    Manajemen User
                </h2>
                <p class="text-gray-600 mt-2">Kelola akun pengguna dan hak akses sistem</p>
            </div>
            <button onclick="openModal('modalAdd')"
                class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold shadow-lg hover:from-blue-700 hover:to-blue-800">
                <i class="fas fa-user-plus mr-2"></i>Tambah User
            </button>
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total User</p>
                    <p class="text-3xl font-bold text-blue-600"><?= $stats['total'] ?></p>
                </div>
                <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Administrator</p>
                    <p class="text-3xl font-bold text-purple-600"><?= $stats['admin'] ?></p>
                </div>
                <div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="mb-4 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-lg font-bold text-gray-800">Daftar Pengguna</h3>
            <input type="text" id="searchInput" placeholder="Cari user..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 w-64">
        </div>

        <div class="overflow-x-auto">
            <table class="data-table w-full" id="dataTable">
                <thead>
                    <tr>
                        <th class="text-left">User Info</th>
                        <th class="text-left">Role</th>
                        <th class="text-left">Hak Akses</th>
                        <th class="text-left">Terdaftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">
                                        <?= strtoupper(substr($u['full_name'], 0, 2)) ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($u['full_name']) ?>
                                        </div>
                                        <div class="text-xs text-gray-500">@<?= htmlspecialchars($u['username']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-bold <?= $u['role'] === 'Administrator' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' ?>">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['permissions'] === 'all'): ?>
                                    <span class="text-xs font-semibold text-green-600">Full Access</span>
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php
                                        $perms = explode(',', $u['permissions']);
                                        foreach ($perms as $p):
                                            if (isset($availablePermissions[$p])):
                                                ?>
                                                <span
                                                    class="px-1.5 py-0.5 bg-gray-100 text-gray-600 rounded text-[10px] border border-gray-200">
                                                    <?= $availablePermissions[$p] ?>
                                                </span>
                                            <?php
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-sm text-gray-500">
                                <?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '-' ?>
                            </td>
                            <td class="text-center">
                                <button onclick='editUser(<?= json_encode($u) ?>)'
                                    class="px-2 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($u['id'] != Session::get('user_id')): ?>
                                    <button onclick="deleteUser(<?= $u['id'] ?>)"
                                        class="px-2 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Modal Add -->
<div id="modalAdd" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Tambah User Baru</h3>
            <button onclick="closeModal('modalAdd')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="full_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                    <select name="role" id="role_add" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg" onchange="togglePermissions('add')">
                        <option value="Staf TU">Staf TU</option>
                        <option value="Administrator">Administrator</option>
                    </select>
                </div>

                <div class="md:col-span-2" id="permissions_container_add">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Hak Akses Modul</label>
                    <div class="grid grid-cols-2 gap-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <?php foreach ($availablePermissions as $key => $label): ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="<?= $key ?>"
                                    class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700"><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalAdd')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-content max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-gray-800">Edit User</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                    <input type="text" name="username" id="edit_username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password (Kosongkan jika tidak
                        ubah)</label>
                    <input type="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg"
                        placeholder="***">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap *</label>
                    <input type="text" name="full_name" id="edit_full_name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                    <select name="role" id="edit_role" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg" onchange="togglePermissions('edit')">
                        <option value="Staf TU">Staf TU</option>
                        <option value="Administrator">Administrator</option>
                    </select>
                </div>

                <div class="md:col-span-2" id="permissions_container_edit">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Hak Akses Modul</label>
                    <div class="grid grid-cols-2 gap-2 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <?php foreach ($availablePermissions as $key => $label): ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="permissions[]" value="<?= $key ?>" id="perm_<?= $key ?>"
                                    class="rounded text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700"><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-3 justify-end">
                <button type="button" onclick="closeModal('modalEdit')"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-semibold">
                    Batal
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete -->
<form method="POST" id="formDelete">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script src="/e-TU/assets/js/app.js"></script>
<script>
    document.getElementById('searchInput')?.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function togglePermissions(mode) {
        const role = document.getElementById(mode === 'add' ? 'role_add' : 'edit_role').value;
        const container = document.getElementById(mode === 'add' ? 'permissions_container_add' : 'permissions_container_edit');

        if (role === 'Administrator') {
            container.style.display = 'none';
        } else {
            container.style.display = 'block';
        }
    }

    function editUser(u) {
        document.getElementById('edit_id').value = u.id;
        document.getElementById('edit_username').value = u.username;
        document.getElementById('edit_full_name').value = u.full_name;
        document.getElementById('edit_role').value = u.role;

        // Reset checkboxes
        document.querySelectorAll('#modalEdit input[type="checkbox"]').forEach(cb => cb.checked = false);

        // Set checkboxes
        if (u.permissions && u.permissions !== 'all') {
            const perms = u.permissions.split(',');
            perms.forEach(p => {
                const cb = document.getElementById('perm_' + p);
                if (cb) cb.checked = true;
            });
        }

        togglePermissions('edit');
        openModal('modalEdit');
    }

    function deleteUser(id) {
        if (confirm('Yakin ingin menghapus user ini?')) {
            document.getElementById('delete_id').value = id;
            document.getElementById('formDelete').submit();
        }
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
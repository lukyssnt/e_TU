<?php
$pageTitle = 'Edit Profil';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
require_once __DIR__ . '/classes/User.php';

// Ensure user is logged in
if (!Session::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userObj = new User();
$user = $userObj->getById(Session::get('user_id'));

if (!$user) {
    Session::setFlash('error', 'Data user tidak ditemukan.');
    header('Location: dashboard.php');
    exit;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = clean($_POST['full_name']);
    $username = clean($_POST['username']);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (empty($fullName))
        $errors[] = 'Nama Lengkap harus diisi.';
    if (empty($username))
        $errors[] = 'Username harus diisi.';

    // Password validation
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }
    }

    if (empty($errors)) {
        $data = [
            'full_name' => $fullName,
            'username' => $username,
            'password' => $password // User class handles hashing
        ];

        $result = $userObj->updateProfile($user['id'], $data);

        if ($result === true) {
            // Update Session Data
            $_SESSION['full_name'] = $fullName;
            $_SESSION['username'] = $username;

            Session::setFlash('success', 'Profil berhasil diperbarui!');
            header('Location: profile.php');
            exit;
        } else {
            // Error from User class (e.g., username taken)
            Session::setFlash('error', is_string($result) ? $result : 'Gagal memperbarui profil.');
        }
    } else {
        Session::setFlash('error', implode('<br>', $errors));
    }
}
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Edit Profil</span>
        </nav>
        <div class="flex items-center gap-4">
            <div
                class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg text-white">
                <i class="fas fa-user-edit text-xl"></i>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Edit Profil</h2>
                <p class="text-gray-600">Perbarui informasi akun Anda</p>
            </div>
        </div>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <form method="POST" action="" class="space-y-6">

                <div class="flex items-center gap-6 mb-6 pb-6 border-b border-gray-100">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-indigo-100 to-white rounded-full flex items-center justify-center border-4 border-indigo-50 shadow-inner">
                        <span
                            class="text-2xl font-bold text-indigo-600"><?= strtoupper(substr($user['full_name'], 0, 2)) ?></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($user['full_name']) ?></h3>
                        <span
                            class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                            <?= htmlspecialchars($user['role']) ?>
                        </span>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>"
                                required
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                        </div>
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-400"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>"
                                required
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 mt-4">
                    <h4 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-lock text-indigo-500"></i> Ganti Password
                    </h4>
                    <p class="text-xs text-gray-500 mb-4 ml-6">Kosongkan jika tidak ingin mengubah password.</p>

                    <div class="grid md:grid-cols-2 gap-6 ml-6">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Password Baru</label>
                            <input type="password" name="password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Konfirmasi Password</label>
                            <input type="password" name="confirm_password"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 mt-6">
                    <a href="dashboard.php"
                        class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-8 py-2.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
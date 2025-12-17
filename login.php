<?php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (Session::isLoggedIn()) {
    header('Location: /e-TU/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        // Demo login - works without database
        if ($username === 'admin' && $password === 'admin123') {
            Session::setUser(1, 'admin', 'Administrator', 'Super Admin', ['all']);
            Session::setFlash('success', 'Selamat datang, Administrator!');
            logActivity('LOGIN', 'Auth', 'User logged in successfully (Demo)');
            header('Location: /e-TU/dashboard.php');
            exit;
        }

        // Try database authentication
        try {
            $db = Database::getInstance()->getConnection();

            if ($db !== null) {
                $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Fetch permissions from roles table
                    $permissions = [];
                    $roleStmt = $db->prepare("SELECT permissions FROM roles WHERE role_name = ?");
                    $roleStmt->execute([$user['role']]);
                    $roleData = $roleStmt->fetch();

                    if ($roleData) {
                        $permissions = json_decode($roleData['permissions'], true) ?? [];
                    } else {
                        // Fallback for legacy users or if role not found
                        $permissions = explode(',', $user['permissions'] ?? '');
                    }

                    Session::setUser(
                        $user['id'],
                        $user['username'],
                        $user['full_name'],
                        $user['role'],
                        $permissions
                    );

                    Session::setFlash('success', 'Selamat datang, ' . $user['full_name'] . '!');

                    // Log Login Activity
                    logActivity('LOGIN', 'Auth', 'User logged in successfully');

                    header('Location: /e-TU/dashboard.php');
                    exit;
                } else {
                    $error = 'Username atau password salah!';
                }
            } else {
                $error = 'Gagal terhubung ke database. Gunakan akun demo.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Gunakan akun demo.';
        }
    } else {
        $error = 'Username dan password harus diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-ADMIN TU MA AL IHSAN</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #023812ff 0%, #96e0acff 100%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4">

    <!-- Background Decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl float"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl float"
            style="animation-delay: 2s;"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md">

        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                <i class="fas fa-school text-4xl text-indigo-600"></i>
            </div>
            <h1 class="text-4xl font-bold text-white mb-2">E-ADMIN TU MA AL IHSAN</h1>
            <p class="text-indigo-100 text-lg">Sistem Informasi Intranet Tata Usaha</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-2xl shadow-2xl p-8">

            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Masuk ke Akun Anda</h2>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-red-700 text-sm"><?= $error ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-clock text-amber-500 mr-3"></i>
                        <p class="text-amber-700 text-sm">Sesi Anda telah berakhir. Silakan login kembali.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-indigo-600"></i>Username
                    </label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                        placeholder="Masukkan username Anda" value="admin">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-indigo-600"></i>Password
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200 pr-12"
                            placeholder="Masukkan password Anda" value="admin123">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-indigo-600">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold">Lupa password?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:from-indigo-700 hover:to-purple-700 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                </button>

            </form>

        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-white text-sm">
            <p>&copy; 2025 E-ADMIN TU MA AL IHSAN. All rights reserved.</p>
            <p class="text-indigo-100 mt-1">Sistem Informasi Tata Usaha v1.0</p>
        </div>

    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>
<?php
// Define navigation menus based on modules
$navigationModules = [
    [
        'module' => 'dashboard',
        'title' => 'Dashboard',
        'icon' => 'fa-home',
        'url' => BASE_URL . '/dashboard.php',
        'permission' => 'all'
    ],
    [
        'module' => 'kepegawaian',
        'title' => 'Kepegawaian',
        'icon' => 'fa-users',
        'url' => BASE_URL . '/modules/kepegawaian/index.php',
        'permission' => 'kepegawaian',
        'submenu' => [
            ['title' => 'Data Pegawai', 'url' => BASE_URL . '/modules/kepegawaian/pegawai.php'],
            ['title' => 'Manajemen Cuti', 'url' => BASE_URL . '/modules/kepegawaian/cuti.php'],
        ]
    ],
    [
        'module' => 'keuangan',
        'title' => 'Keuangan',
        'icon' => 'fa-money-bill-wave',
        'url' => BASE_URL . '/modules/keuangan/index.php',
        'permission' => 'keuangan',
        'submenu' => [
            ['title' => 'Kas Masuk/Keluar', 'url' => BASE_URL . '/modules/keuangan/kas.php'],
            ['title' => 'Buku Kas', 'url' => BASE_URL . '/modules/keuangan/buku-kas.php'],
            ['title' => 'Pembayaran Siswa', 'url' => BASE_URL . '/modules/keuangan/pembayaran.php'],
            ['title' => 'RAB', 'url' => BASE_URL . '/modules/keuangan/rab.php'],
            ['title' => 'Laporan Keuangan', 'url' => BASE_URL . '/modules/keuangan/laporan.php'],
        ]
    ],
    [
        'module' => 'sarpras',
        'title' => 'Sarana Prasarana',
        'icon' => 'fa-warehouse',
        'url' => BASE_URL . '/modules/sarpras/index.php',
        'permission' => 'sarpras',
        'submenu' => [
            ['title' => 'Inventaris Aset', 'url' => BASE_URL . '/modules/sarpras/aset.php'],
            ['title' => 'Peminjaman Aset', 'url' => BASE_URL . '/modules/sarpras/peminjaman.php'],
            ['title' => 'Maintenance', 'url' => BASE_URL . '/modules/sarpras/maintenance.php'],
        ]
    ],
    [
        'module' => 'kehumasan',
        'title' => 'Kehumasan',
        'icon' => 'fa-bullhorn',
        'url' => BASE_URL . '/modules/kehumasan/index.php',
        'permission' => 'kehumasan',
        'submenu' => [
            ['title' => 'Agenda Kegiatan', 'url' => BASE_URL . '/modules/kehumasan/agenda.php'],
            ['title' => 'Dokumentasi', 'url' => BASE_URL . '/modules/kehumasan/dokumentasi.php'],
            ['title' => 'Press Release', 'url' => BASE_URL . '/modules/kehumasan/press-release.php'],
        ]
    ],
    [
        'module' => 'persuratan',
        'title' => 'Persuratan & Kearsipan',
        'icon' => 'fa-envelope',
        'url' => BASE_URL . '/modules/persuratan/index.php',
        'permission' => 'persuratan',
        'submenu' => [
            ['title' => 'Surat Generator', 'url' => BASE_URL . '/modules/persuratan/generator.php', 'badge' => 'Popular'],
            ['title' => 'Surat Masuk', 'url' => BASE_URL . '/modules/persuratan/surat-masuk.php'],
            ['title' => 'Surat Keluar', 'url' => BASE_URL . '/modules/persuratan/surat-keluar.php'],
            ['title' => 'Disposisi Digital', 'url' => BASE_URL . '/modules/persuratan/disposisi.php'],
            ['title' => 'Arsip Digital', 'url' => BASE_URL . '/modules/persuratan/arsip.php'],
            ['title' => 'Template Surat', 'url' => BASE_URL . '/modules/persuratan/template.php'],
        ]
    ],
    [
        'module' => 'kesiswaan',
        'title' => 'Kesiswaan',
        'icon' => 'fa-user-graduate',
        'url' => BASE_URL . '/modules/kesiswaan/index.php',
        'permission' => 'kesiswaan',
        'submenu' => [
            ['title' => 'Data Siswa', 'url' => BASE_URL . '/modules/kesiswaan/siswa.php'],
            ['title' => 'Data Alumni', 'url' => BASE_URL . '/modules/kesiswaan/alumni.php'],
            ['title' => 'Manajemen Kelas', 'url' => BASE_URL . '/modules/kesiswaan/kelas.php'],
            ['title' => 'Mutasi Siswa', 'url' => BASE_URL . '/modules/kesiswaan/mutasi.php'],
        ]
    ],
    [
        'module' => 'layanan',
        'title' => 'Layanan Khusus',
        'icon' => 'fa-hands-helping',
        'url' => BASE_URL . '/modules/layanan/index.php',
        'permission' => 'layanan',
        'submenu' => [
            ['title' => 'Perpustakaan', 'url' => BASE_URL . '/modules/layanan/perpustakaan.php'],
            ['title' => 'UKS', 'url' => BASE_URL . '/modules/layanan/uks.php'],
        ]
    ],
    [
        'module' => 'portal',
        'title' => 'Portal Informasi',
        'icon' => 'fa-globe',
        'url' => BASE_URL . '/modules/portal/index.php',
        'permission' => 'admin',
        'submenu' => [
            ['title' => 'Konten Website', 'url' => BASE_URL . '/modules/portal/index.php'],
            ['title' => 'Buku Tamu', 'url' => BASE_URL . '/modules/portal/buku-tamu.php'],
            ['title' => 'Layanan Alumni', 'url' => BASE_URL . '/modules/portal/alumni.php'],
        ]
    ],
    [
        'module' => 'tik',
        'title' => 'TIK & Pengaturan',
        'icon' => 'fa-cog',
        'url' => BASE_URL . '/modules/tik/index.php',
        'permission' => 'tik',
        'submenu' => [
            ['title' => 'Pengaturan Akademik', 'url' => BASE_URL . '/modules/tik/academic-settings.php', 'badge' => 'New'],
            ['title' => 'Manajemen User', 'url' => BASE_URL . '/modules/tik/users.php'],
            ['title' => 'Role & Permission', 'url' => BASE_URL . '/modules/tik/roles.php'],
            ['title' => 'Log Aktivitas', 'url' => BASE_URL . '/modules/tik/logs.php'],
            ['title' => 'Pengaturan Sistem', 'url' => BASE_URL . '/modules/tik/settings.php'],
        ]
    ],
];

$currentPage = $_SERVER['REQUEST_URI'];
?>

<style>
    .sidebar-modern {
        background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
        border-right: 1px solid #e5e7eb;
    }

    .menu-item {
        transition: all 0.2s ease;
    }

    .menu-item:hover {
        background: #f0fdf4;
        transform: translateX(4px);
    }

    .dark .menu-item:hover {
        background: #374151 !important;
        /* bg-gray-700 */
        color: #6ee7b7 !important;
        /* text-emerald-300 */
    }

    .menu-item.active {
        background: linear-gradient(90deg, #059669 0%, #047857 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }

    .menu-item.active i {
        color: white !important;
    }

    .submenu-item {
        transition: all 0.2s ease;
    }

    .submenu-item:hover {
        background: #f0fdf4;
        color: #059669;
        transform: translateX(4px);
    }

    .dark .submenu-item:hover {
        background: #374151 !important;
        color: #6ee7b7 !important;
    }

    .user-badge {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }
</style>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed left-0 top-0 h-screen w-72 sidebar-modern text-gray-800 shadow-xl z-40 smooth-transition overflow-y-auto">

    <!-- Logo Header -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
        <div class="flex items-center gap-3">
            <?php
            // Fetch Logo if not already available
            $sidebarLogo = null;
            $sidebarSchoolName = 'E-Admin TU';

            if (!class_exists('Setting')) {
                require_once __DIR__ . '/../classes/Setting.php';
            }
            if (class_exists('Setting')) {
                $settingObjSidebar = new Setting();
                $sidebarSettings = $settingObjSidebar->getAll();
                foreach ($sidebarSettings as $s) {
                    if ($s['setting_key'] === 'app_logo' && !empty($s['setting_value'])) {
                        $sidebarLogo = BASE_URL . '/' . $s['setting_value'];
                    }
                    if ($s['setting_key'] === 'app_name') {
                        $sidebarSchoolName = $s['setting_value'];
                    }
                }
            }
            ?>

            <?php if ($sidebarLogo): ?>
                <img src="<?= htmlspecialchars($sidebarLogo) ?>" alt="Logo"
                    class="w-10 h-10 object-contain bg-white rounded-xl shadow-md p-1">
            <?php else: ?>
                <div
                    class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-school text-white text-xl"></i>
                </div>
            <?php endif; ?>

            <div>
                <h1 class="font-bold text-base text-gray-800"><?= htmlspecialchars($sidebarSchoolName) ?></h1>
                <p class="text-xs text-gray-500">MA Al Ihsan</p>
            </div>
        </div>
        <button onclick="toggleSidebarMobile()" class="lg:hidden text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-xl"></i>
        </button>
        <button onclick="toggleSidebarDesktop()"
            class="hidden lg:block text-gray-400 hover:text-emerald-600 transition-colors">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- User Info with Logout -->
    <div class="px-4 py-4">
        <div
            class="p-3 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-gray-800 dark:to-gray-900 rounded-xl border border-emerald-100 dark:border-gray-700">
            <div class="flex items-center gap-3 mb-3">
                <div
                    class="w-10 h-10 user-badge rounded-full flex items-center justify-center font-bold text-sm shadow-md text-white">
                    <?= strtoupper(substr(Session::get('full_name'), 0, 2)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm truncate text-gray-800">
                        <?= htmlspecialchars(Session::get('full_name')) ?>
                    </p>
                    <p class="text-xs text-emerald-600 font-medium"><?= htmlspecialchars(Session::get('role')) ?></p>
                </div>
                <!-- Dark Mode Toggle -->
                <button id="theme-toggle" type="button"
                    class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <i id="theme-toggle-dark-icon" class="fas fa-moon hidden text-sm"></i>
                    <i id="theme-toggle-light-icon" class="fas fa-sun hidden text-sm"></i>
                </button>
            </div>
            <!-- Edit Profile & Logout -->
            <div class="flex gap-2 mt-2">
                <a href="<?= BASE_URL ?>/profile.php"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg smooth-transition shadow-sm text-xs font-semibold"
                    title="Edit Profil">
                    <i class="fas fa-user-edit"></i>
                    <span>Edit</span>
                </a>
                <a href="<?= BASE_URL ?>/logout.php"
                    class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg smooth-transition shadow-md text-xs font-semibold"
                    title="Keluar">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Menu Label -->
    <div class="px-6 py-2">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Menu</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="px-3 pb-6 space-y-1">
        <?php foreach ($navigationModules as $menu): ?>
            <?php if (Session::hasPermission($menu['permission']) || $menu['permission'] === 'all'): ?>

                <?php if (isset($menu['submenu'])): ?>
                    <!-- Menu with Submenu -->
                    <div class="menu-group">
                        <button onclick="toggleSubmenu(this)"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-xl menu-item group">
                            <div class="flex items-center gap-3">
                                <i class="fas <?= $menu['icon'] ?> w-5 text-gray-400 group-hover:text-emerald-600 transition"></i>
                                <span class="font-medium text-sm text-gray-700"><?= $menu['title'] ?></span>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-400 smooth-transition submenu-icon"></i>
                        </button>
                        <div class="submenu hidden ml-8 mt-1 space-y-1">
                            <?php foreach ($menu['submenu'] as $sub): ?>
                                <a href="<?= $sub['url'] ?>"
                                    class="submenu-item flex items-center justify-between px-4 py-2 rounded-lg text-gray-600 hover:text-emerald-600 smooth-transition text-sm group">
                                    <span><?= $sub['title'] ?></span>
                                    <?php if (isset($sub['badge'])): ?>
                                        <span
                                            class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded"><?= $sub['badge'] ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Simple Menu -->
                    <a href="<?= $menu['url'] ?>"
                        class="menu-item flex items-center gap-3 px-4 py-3 rounded-xl <?= strpos($currentPage, $menu['url']) !== false ? 'active' : '' ?> group">
                        <i
                            class="fas <?= $menu['icon'] ?> w-5 <?= strpos($currentPage, $menu['url']) !== false ? 'text-white' : 'text-gray-400 group-hover:text-emerald-600' ?> transition"></i>
                        <span
                            class="font-medium text-sm <?= strpos($currentPage, $menu['url']) !== false ? 'text-white' : 'text-gray-700' ?>"><?= $menu['title'] ?></span>
                    </a>
                <?php endif; ?>

            <?php endif; ?>
        <?php endforeach; ?>

    </nav>

</aside>

<!-- Desktop Open Button (Floating) -->
<button id="desktopOpenBtn" onclick="toggleSidebarDesktop()"
    class="fixed top-4 left-4 z-50 bg-white p-3 rounded-xl shadow-lg text-gray-600 hover:text-emerald-600 transition-all duration-300 hidden hover:scale-105 active:scale-95 group">
    <i class="fas fa-bars text-xl group-hover:rotate-180 transition-transform duration-300"></i>
</button>

<!-- Mobile Toggle Button (Bottom Right) -->
<button onclick="toggleSidebarMobile()"
    class="lg:hidden fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 rounded-full shadow-2xl z-50 flex items-center justify-center smooth-transition hover:scale-110 active:scale-95">
    <i class="fas fa-bars text-white text-xl"></i>
</button>

<script>
    function toggleSidebarMobile() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
    }

    function toggleSidebarDesktop() {
        const sidebar = document.getElementById('sidebar');
        const main = document.querySelector('main');
        const openBtn = document.getElementById('desktopOpenBtn');

        // Toggle Sidebar position
        sidebar.classList.toggle('-translate-x-full');

        // Check if sidebar is now hidden (has the class)
        const isHidden = sidebar.classList.contains('-translate-x-full');

        if (isHidden) {
            // Sidebar is Hidden -> Show Open Button, Remove Main Margin
            main?.classList.remove('lg:ml-72');
            main?.classList.add('lg:ml-0');
            // Show button on desktop
            openBtn.classList.add('lg:block');
        } else {
            // Sidebar is Visible -> Hide Open Button, Restore Main Margin
            main?.classList.add('lg:ml-72');
            main?.classList.remove('lg:ml-0');
            // Hide button on desktop
            openBtn.classList.remove('lg:block');
        }
    }

    function toggleSubmenu(button) {
        const submenu = button.nextElementSibling;
        const icon = button.querySelector('.submenu-icon');

        submenu.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function (event) {
        const sidebar = document.getElementById('sidebar');
        const mobileBtn = event.target.closest('button[onclick="toggleSidebarMobile()"]');
        const desktopBtn = event.target.closest('button[onclick="toggleSidebarDesktop()"]'); // Don't close if clicking open button

        if (window.innerWidth < 1024 && !sidebar.contains(event.target) && !mobileBtn && !desktopBtn) {
            sidebar.classList.add('-translate-x-full');
        }
    });

    // Responsive sidebar
    window.addEventListener('resize', function () {
        const sidebar = document.getElementById('sidebar');
        const main = document.querySelector('main');
        const openBtn = document.getElementById('desktopOpenBtn');

        if (window.innerWidth >= 1024) {
            // Reset to default desktop state (Visible) on resize to large
            sidebar.classList.remove('-translate-x-full');
            main?.classList.add('lg:ml-72');
            main?.classList.remove('lg:ml-0');
            openBtn.classList.remove('lg:block'); // Hide button
        } else {
            // Default mobile state (Hidden)
            sidebar.classList.add('-translate-x-full');
            main?.classList.remove('lg:ml-0'); // Cleanup
        }
    });

    // Initialize state
    document.addEventListener('DOMContentLoaded', () => {
        if (window.innerWidth < 1024) {
            document.getElementById('sidebar').classList.add('-translate-x-full');
        }

        // Dark Mode Logic
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        const themeToggleBtn = document.getElementById('theme-toggle');

        // Change the icons inside the button based on previous settings
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        themeToggleBtn.addEventListener('click', function () {
            // toggle icons inside button
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            // if set via local storage previously
            if (localStorage.getItem('color-theme')) {
                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }

                // if NOT set via local storage previously
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                }
            }
        });
    });
</script>
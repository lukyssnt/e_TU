<?php
$pageTitle = 'Kehumasan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../classes/Agenda.php';
require_once __DIR__ . '/../../classes/PressRelease.php';
require_once __DIR__ . '/../../classes/Dokumentasi.php';

checkPermission('kehumasan');

$agenda = new Agenda();
$pr = new PressRelease();
$dok = new Dokumentasi();

$agendaStats = $agenda->getStatusCounts();
$totalAgenda = array_sum($agendaStats);
$agendaAkanDatang = $agendaStats['Akan Datang'] ?? 0;

$prStats = $pr->getStatusCounts();
$totalPR = array_sum($prStats);
$prPublis = $prStats['Dipublikasi'] ?? 0;

$totalDokumentasi = $dok->getTotalCount();
?>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Kehumasan</span>
        </nav>
        <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
            <div
                class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-bullhorn text-white text-xl"></i>
            </div>
            Kehumasan
        </h2>
        <p class="text-gray-600 mt-2">Kelola hubungan masyarakat, agenda, dan dokumentasi</p>
    </div>

    <!-- Quick Menu Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <a href="/e-TU/modules/kehumasan/agenda.php" class="block group">
            <div
                class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-calendar-alt text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Agenda Kegiatan</h3>
                <p class="text-blue-100 text-sm">Jadwal kegiatan sekolah</p>
            </div>
        </a>

        <a href="/e-TU/modules/kehumasan/dokumentasi.php" class="block group">
            <div
                class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-camera text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Dokumentasi</h3>
                <p class="text-emerald-100 text-sm">Galeri foto kegiatan</p>
            </div>
        </a>

        <a href="/e-TU/modules/kehumasan/press-release.php" class="block group">
            <div
                class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-6 text-white hover:shadow-2xl smooth-transition transform hover:-translate-y-2">
                <div class="w-14 h-14 bg-white/20 rounded-lg flex items-center justify-center mb-4">
                    <i class="fas fa-newspaper text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold mb-2">Press Release</h3>
                <p class="text-amber-100 text-sm">Berita & pengumuman publik</p>
            </div>
        </a>

    </div>

    <!-- Stats Overview -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Agenda Kegiatan</p>
                    <p class="text-3xl font-bold text-pink-600"><?= $totalAgenda ?></p>
                    <p class="text-xs text-green-600 mt-1"><?= $agendaAkanDatang ?> Akan Datang</p>
                </div>
                <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-pink-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Press Release</p>
                    <p class="text-3xl font-bold text-cyan-600"><?= $totalPR ?></p>
                    <p class="text-xs text-green-600 mt-1"><?= $prPublis ?> Dipublikasi</p>
                </div>
                <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-newspaper text-cyan-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Galeri Foto</p>
                    <p class="text-3xl font-bold text-violet-600"><?= $totalDokumentasi ?></p>
                </div>
                <div class="w-12 h-12 bg-violet-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-images text-violet-600 text-xl"></i>
                </div>
            </div>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
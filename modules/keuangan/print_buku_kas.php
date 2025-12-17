<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/TransaksiKas.php';
require_once __DIR__ . '/../../classes/Setting.php';
require_once __DIR__ . '/../../classes/Session.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /e-TU/login.php');
    exit;
}

$kas = new TransaksiKas();
$setting = new Setting();
$schoolName = $setting->get('school_name', 'MA AL IHSAN');
$schoolAddress = $setting->get('school_address', 'Jl. Contoh No. 123, Kota Contoh');

// Handle filters
$filterTanggal = $_GET['tanggal'] ?? date('Y-m-d');
$filterBulan = $_GET['bulan'] ?? date('Y-m');
$filterTahun = $_GET['tahun'] ?? date('Y');
$filterJenis = $_GET['jenis'] ?? 'semua';
$filterPeriode = $_GET['periode'] ?? 'hari';

// Get data based on filter
if ($filterPeriode === 'hari') {
    $transaksi = $kas->getByPeriod($filterTanggal, $filterTanggal);
    $periodLabel = formatTanggal($filterTanggal, 'long');
} elseif ($filterPeriode === 'bulan') {
    $startDate = $filterBulan . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    $transaksi = $kas->getByPeriod($startDate, $endDate);
    $periodLabel = date('F Y', strtotime($startDate));
} else { // tahun
    $startDate = $filterTahun . '-01-01';
    $endDate = $filterTahun . '-12-31';
    $transaksi = $kas->getByPeriod($startDate, $endDate);
    $periodLabel = 'Tahun ' . $filterTahun;
}

// Filter by jenis
if ($filterJenis !== 'semua') {
    $jenis = $filterJenis === 'masuk' ? 'Masuk' : 'Keluar';
    $transaksi = array_filter($transaksi, fn($t) => $t['jenis_transaksi'] === $jenis);
}

// Calculate totals
$totalMasuk = 0;
$totalKeluar = 0;
foreach ($transaksi as $t) {
    if ($t['jenis_transaksi'] === 'Masuk') {
        $totalMasuk += $t['nominal'];
    } else {
        $totalKeluar += $t['nominal'];
    }
}
$selisih = $totalMasuk - $totalKeluar;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Buku Kas - <?= $periodLabel ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            @page {
                size: 210mm 330mm; /* F4 Size */
                margin: 2cm;
            }

            body {
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Times New Roman', serif;
        }

        .table-bordered {
            border-collapse: collapse;
            width: 100%;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid black;
            padding: 8px;
        }

        .header-line {
            border-bottom: 2px solid black;
            margin-bottom: 2px;
        }

        .header-line-2 {
            border-bottom: 1px solid black;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="bg-white p-8" onload="window.print()">

    <!-- Kop Surat -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold uppercase"><?= $schoolName ?></h1>
        <p class="text-sm"><?= $schoolAddress ?></p>
        <div class="mt-4 header-line"></div>
        <div class="header-line-2"></div>
    </div>

    <div class="text-center mb-6">
        <h2 class="text-xl font-bold underline">LAPORAN BUKU KAS UMUM</h2>
        <p class="mt-2">Periode: <?= $periodLabel ?></p>
    </div>

    <table class="table-bordered mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="w-12 text-center">No</th>
                <th class="w-32 text-center">Tanggal</th>
                <th class="text-center">Uraian / Keterangan</th>
                <th class="w-32 text-center">Pemasukan</th>
                <th class="w-32 text-center">Pengeluaran</th>
                <th class="w-32 text-center">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($transaksi) > 0): ?>
                <?php foreach ($transaksi as $index => $t): ?>
                    <tr>
                        <td class="text-center"><?= $index + 1 ?></td>
                        <td class="text-center"><?= formatTanggal($t['tanggal'], 'short') ?></td>
                        <td>
                            <div class="font-bold"><?= htmlspecialchars($t['kategori']) ?></div>
                            <div class="text-sm"><?= htmlspecialchars($t['keterangan']) ?></div>
                        </td>
                        <td class="text-right">
                            <?= $t['jenis_transaksi'] === 'Masuk' ? formatRupiah($t['nominal']) : '-' ?>
                        </td>
                        <td class="text-right">
                            <?= $t['jenis_transaksi'] === 'Keluar' ? formatRupiah($t['nominal']) : '-' ?>
                        </td>
                        <td class="text-right"><?= formatRupiah($t['saldo']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center py-8">Tidak ada data transaksi</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="font-bold bg-gray-50">
                <td colspan="3" class="text-right">TOTAL</td>
                <td class="text-right"><?= formatRupiah($totalMasuk) ?></td>
                <td class="text-right"><?= formatRupiah($totalKeluar) ?></td>
                <td class="text-right"><?= formatRupiah($selisih) ?></td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <div class="flex justify-end mt-12">
        <div class="text-center w-64">
            <p><?= date('d F Y') ?></p>
            <p class="mb-20">Kepala Tata Usaha</p>
            <p class="font-bold underline"><?= Session::get('full_name') ?? '.........................' ?></p>
            <p>NIP. .........................</p>
        </div>
    </div>

</body>

</html>
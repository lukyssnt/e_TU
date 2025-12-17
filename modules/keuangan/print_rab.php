<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Setting.php';

// Check permission (optional, but good practice)
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak");
}

$db = Database::getInstance()->getConnection();
$settingObj = new Setting();
$settings = $settingObj->getAll();
$settingsMap = [];
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s['setting_value'];
}

// Filter Logic
$tahunFilter = isset($_GET['tahun']) ? (int) $_GET['tahun'] : date('Y');
$kategoriFilter = isset($_GET['kategori']) ? clean($_GET['kategori']) : '';
$statusFilter = isset($_GET['status']) ? clean($_GET['status']) : '';

// Build Query
$query = "SELECT * FROM rab WHERE tahun = ?";
$params = [$tahunFilter];

if (!empty($kategoriFilter)) {
    $query .= " AND kategori = ?";
    $params[] = $kategoriFilter;
}

if (!empty($statusFilter)) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$rabItems = $stmt->fetchAll();

// Calculate Summaries
$totalAnggaran = 0;
foreach ($rabItems as $item) {
    $totalAnggaran += $item['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan RAB Tahun <?= $tahunFilter ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double black;
            padding-bottom: 10px;
        }

        .header img {
            width: 80px;
            height: auto;
            float: left;
        }

        .header h1,
        .header h2,
        .header p {
            margin: 0;
        }

        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: bold;
        }

        .header p {
            font-size: 11pt;
        }

        .title {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            text-decoration: underline;
            font-size: 14pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .footer-sign {
            margin-top: 50px;
            width: 100%;
        }

        .sign-box {
            width: 30%;
            float: right;
            text-align: center;
        }

        .sign-box-left {
            width: 30%;
            float: left;
            text-align: center;
        }

        @media print {
            @page {
                size: 210mm 330mm;
                /* F4 Size */
                margin: 2cm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <!-- Logo placeholder if exists -->
        <?php if (!empty($settingsMap['app_logo'])): ?>
            <img src="/e-TU/<?= $settingsMap['app_logo'] ?>" alt="Logo">
        <?php endif; ?>

        <h1><?= $settingsMap['school_name'] ?? 'MA AL IHSAN' ?></h1>
        <p><?= $settingsMap['school_address'] ?? 'Alamat Sekolah' ?></p>
        <p>Telp: <?= $settingsMap['school_phone'] ?? '-' ?> | Email: <?= $settingsMap['school_email'] ?? '-' ?></p>
    </div>

    <div class="title">
        RENCANA ANGGARAN BIAYA (RAB)<br>
        TAHUN ANGGARAN <?= $tahunFilter ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Kode</th>
                <th width="30%">Uraian Kegiatan</th>
                <th width="10%">Vol</th>
                <th width="10%">Satuan</th>
                <th width="15%">Harga Satuan</th>
                <th width="15%">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rabItems) > 0): ?>
                <?php $no = 1;
                foreach ($rabItems as $item): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= $item['kode'] ?></td>
                        <td>
                            <?= $item['uraian'] ?>
                            <?php if (!empty($item['keterangan'])): ?>
                                <br><small><i>Ket: <?= $item['keterangan'] ?></i></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $item['volume'] ?></td>
                        <td class="text-center"><?= $item['satuan'] ?></td>
                        <td class="text-right"><?= formatRupiah($item['harga_satuan']) ?></td>
                        <td class="text-right"><?= formatRupiah($item['jumlah']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">Belum ada data RAB</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>TOTAL ANGGARAN</strong></td>
                <td class="text-right"><strong><?= formatRupiah($totalAnggaran) ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer-sign">
        <div class="sign-box-left">
            <br>
            Mengetahui,<br>
            Kepala Sekolah<br>
            <br><br><br>
            <strong>_______________________</strong><br>
            NIP. ...........................
        </div>
        <div class="sign-box">
            Bandung, <?= formatTanggal(date('Y-m-d')) ?><br>
            Bendahara,<br>
            <br><br><br>
            <strong>_______________________</strong><br>
            NIP. ...........................
        </div>
        <div style="clear: both;"></div>
    </div>

</body>

</html>
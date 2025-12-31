<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is logged in
if (!Session::isLoggedIn()) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/Pembayaran.php';

// Check ID
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID Pembayaran tidak valid.");
}

$pembayaran = new Pembayaran();
$data = $pembayaran->getPembayaranById($id);

if (!$data) {
    die("Data pembayaran tidak ditemukan.");
}

$title = "Kuitansi Pembayaran #" . str_pad($data['id'], 6, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            /* Smaller font */
            color: #000;
            background: #fff;
            padding: 10px;
        }

        .container {
            width: 100%;
            max-width: 215mm;
            /* Fit F4/A4 width */
            margin: 0 auto;
            border: 2px solid #000;
            padding: 15px;
            position: relative;
            background-color: #fff;
            /* Height approx 1/3 of 330mm is 110mm */
            min-height: 100mm;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #000;
            /* Double line looks more formal */
            padding-bottom: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: auto;
        }

        .school-info h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
            font-weight: 900;
        }

        .school-info p {
            margin: 0;
            font-size: 11px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
        }

        .content-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .content-table td {
            padding: 4px;
            vertical-align: top;
        }

        .label {
            width: 130px;
            font-weight: bold;
        }

        .separator {
            width: 10px;
            text-align: center;
        }

        .amount-box {
            background-color: #eee;
            padding: 8px 15px;
            font-weight: bold;
            font-size: 16px;
            border: 2px solid #000;
            display: inline-block;
            margin-top: 5px;
            border-radius: 4px;
        }

        .footer {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .signature-box {
            text-align: center;
            width: 200px;
        }

        .signature-space {
            height: 60px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .stamp-box {
            border: 2px dashed #999;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #aaa;
            transform: rotate(-5deg);
        }

        @media print {
            @page {
                size: auto;
                /* Let printer decide, prevents forcing huge scaling */
                margin: 5mm;
            }

            body {
                padding: 0;
                margin: 0;
                -webkit-print-color-adjust: exact;
            }

            .container {
                border: 2px solid #000;
                /* Ensure border prints */
                max-width: 100%;
                width: 100%;
                min-height: auto;
                /* Allow flexible height */
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 5px;">Cetak
            Kuitansi</button>
    </div>

    <div class="container">
        <!-- Optional: Add Logo if available -->
        <!-- <img src="/path/to/logo.png" alt="Logo" class="logo" style="position: absolute; left: 20px; top: 20px;"> -->

        <div class="header">
            <div class="school-info">
                <h1>MA AL IHSAN</h1>
                <p>Jl. Contoh No. 123, Kota Contoh, Jawa Barat</p>
                <p>Telp: (021) 1234567 | Email: info@maalihsan.sch.id</p>
            </div>
        </div>

        <div class="title">KUITANSI PEMBAYARAN</div>

        <table class="content-table">
            <tr>
                <td class="label">No. Kuitansi</td>
                <td class="separator">:</td>
                <td><?= str_pad($data['id'], 6, '0', STR_PAD_LEFT) ?> / KEU /
                    <?= date('m', strtotime($data['tanggal_bayar'])) ?> /
                    <?= date('Y', strtotime($data['tanggal_bayar'])) ?>
                </td>
            </tr>
            <tr>
                <td class="label">Telah Terima Dari</td>
                <td class="separator">:</td>
                <td><strong><?= htmlspecialchars($data['nama_lengkap']) ?></strong>
                    (<?= htmlspecialchars($data['nisn']) ?>) - Kelas <?= htmlspecialchars($data['nama_kelas']) ?></td>
            </tr>
            <tr>
                <td class="label">Uang Sejumlah</td>
                <td class="separator">:</td>
                <td style="font-style: italic; background: #eee; padding: 5px;"><?= terbilang($data['jumlah_bayar']) ?>
                    Rupiah</td>
            </tr>
            <tr>
                <td class="label">Untuk Pembayaran</td>
                <td class="separator">:</td>
                <td>
                    <?= htmlspecialchars($data['judul_tagihan']) ?>
                    <?php if (!empty($data['keterangan'])): ?>
                        <br><span style="font-size: 0.9em; color: #555;">(Catatan:
                            <?= htmlspecialchars($data['keterangan']) ?>)</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <div class="amount-box">
            <?= formatRupiah($data['jumlah_bayar']) ?>
        </div>

        <div class="footer">
            <div style="position: relative;">
                <div class="stamp-box">
                    STEMPEL
                </div>
            </div>

            <div class="signature-box">
                <p><?= formatTanggal($data['tanggal_bayar']) ?></p>
                <p>Bendahara,</p>
                <div class="signature-space">
                    <!-- Space for signature -->
                </div>
                <p><strong>_______________________</strong></p>
            </div>
        </div>
    </div>

</body>

</html>
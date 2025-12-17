<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/Siswa.php';

checkPermission('kesiswaan');

$siswa = new Siswa();
$filterTahun = $_GET['tahun'] ?? null;
$alumni = $siswa->getAllAlumni($filterTahun);

$filename = 'Data_Alumni_' . ($filterTahun ? $filterTahun . '_' : '') . date('Y-m-d') . '.xls';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <h3 style="text-align: center;">DATA ALUMNI MA AL IHSAN</h3>
    <?php if ($filterTahun): ?>
        <p style="text-align: center;">Tahun Lulus: <?= htmlspecialchars($filterTahun) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NISN</th>
                <th>Nama Lengkap</th>
                <th>Jenis Kelamin</th>
                <th>Tahun Lulus</th>
                <th>Kelas Terakhir</th>
                <th>Tempat, Tanggal Lahir</th>
                <th>Alamat</th>
                <th>Nama Ortu</th>
                <th>No HP Ortu</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alumni as $i => $a): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td class="text-center" style="mso-number-format:'\@'"><?= htmlspecialchars($a['nisn']) ?></td>
                    <td><?= htmlspecialchars($a['nama_lengkap']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($a['jenis_kelamin']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($a['tahun_lulus']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($a['kelas_terakhir'] ?? '-') ?></td>
                    <td>
                        <?= htmlspecialchars($a['tempat_lahir'] ?? '-') ?>,
                        <?= $a['tanggal_lahir'] ? date('d/m/Y', strtotime($a['tanggal_lahir'])) : '-' ?>
                    </td>
                    <td><?= htmlspecialchars($a['alamat'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($a['nama_ortu'] ?? '-') ?></td>
                    <td class="text-center" style="mso-number-format:'\@'"><?= htmlspecialchars($a['no_hp_ortu'] ?? '-') ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
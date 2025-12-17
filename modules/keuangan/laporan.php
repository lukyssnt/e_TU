<?php
$pageTitle = 'Laporan Keuangan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../classes/TransaksiKas.php';

checkPermission('keuangan');

$kas = new TransaksiKas();

// Handle filters
$filterTahun = $_GET['tahun'] ?? date('Y');
$filterBulan = $_GET['bulan'] ?? '';
$filterJenis = $_GET['jenis'] ?? 'rekap'; // rekap, detail

// Determine date range
if ($filterBulan) {
    $startDate = $filterTahun . '-' . $filterBulan . '-01';
    $endDate = date('Y-m-t', strtotime($startDate));
    $periodLabel = date('F Y', strtotime($startDate));
} else {
    $startDate = $filterTahun . '-01-01';
    $endDate = $filterTahun . '-12-31';
    $periodLabel = 'Tahun ' . $filterTahun;
}

// Get data
$transaksi = $kas->getByPeriod($startDate, $endDate);
$totalMasuk = $kas->getTotalPemasukan($startDate, $endDate);
$totalKeluar = $kas->getTotalPengeluaran($startDate, $endDate);
$saldo = $totalMasuk - $totalKeluar;

// Group by kategori
$byKategori = [];
foreach ($transaksi as $t) {
    $kat = $t['kategori'];
    if (!isset($byKategori[$kat])) {
        $byKategori[$kat] = ['masuk' => 0, 'keluar' => 0];
    }
    if ($t['jenis_transaksi'] === 'Masuk') {
        $byKategori[$kat]['masuk'] += $t['nominal'];
    } else {
        $byKategori[$kat]['keluar'] += $t['nominal'];
    }
}
?>

<link rel="stylesheet" href="/e-TU/assets/css/custom.css">

<style>
    @media print {

        /* F4 Paper: 210mm x 330mm */
        @page {
            size: 210mm 330mm;
            margin: 15mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: white !important;
            font-size: 10pt;
        }

        /* Hide non-printable elements */
        .no-print {
            display: none !important;
        }

        main {
            margin-left: 0 !important;
            padding: 10mm !important;
        }

        /* Header styling */
        h2 {
            text-align: center;
            font-size: 16pt;
            margin-bottom: 5mm;
            color: #000 !important;
        }

        h3 {
            font-size: 12pt;
            margin-bottom: 3mm;
            color: #000 !important;
        }

        /* Summary cards - convert to simple table */
        #summaryCards {
            display: table;
            width: 100%;
            margin-bottom: 5mm;
            border: 1px solid #000;
            border-collapse: collapse;
        }

        #summaryCards>div {
            display: table-cell !important;
            background: white !important;
            color: #000 !important;
            border: 1px solid #000;
            padding: 3mm;
            text-align: center;
            width: 33.33%;
        }

        #summaryCards p {
            color: #000 !important;
            margin: 0;
        }

        #summaryCards .text-3xl {
            font-size: 14pt !important;
            font-weight: bold;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5mm;
            font-size: 9pt;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 2mm;
            color: #000 !important;
        }

        table th {
            background-color: #f0f0f0 !important;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Remove background colors from table rows */
        table tbody tr {
            background: white !important;
        }

        /* Keep text colors for amounts */
        .text-green-600 {
            color: #059669 !important;
        }

        .text-red-600 {
            color: #dc2626 !important;
        }

        .text-blue-600 {
            color: #2563eb !important;
        }

        /* Badge styling for print */
        .badge {
            border: 1px solid #000;
            padding: 1mm 2mm;
            border-radius: 2mm;
            font-size: 8pt;
        }

        /* Prevent page breaks inside table rows */
        tr {
            page-break-inside: avoid;
        }

        /* Report content */
        #reportContent {
            background: white !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
        }
    }
</style>

<main class="lg:ml-72 min-h-screen p-6">

    <div class="mb-8">
        <nav class="text-sm text-gray-600 mb-4">
            <a href="/e-TU/dashboard.php" class="hover:text-blue-600">Dashboard</a>
            <span class="mx-2">/</span>
            <a href="/e-TU/modules/keuangan/index.php" class="hover:text-blue-600">Keuangan</a>
            <span class="mx-2">/</span>
            <span class="text-gray-800 font-semibold">Laporan Keuangan</span>
        </nav>
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-chart-pie text-white text-xl"></i>
                    </div>
                    Laporan Keuangan
                </h2>
                <p class="text-gray-600 mt-2">Analisis dan laporan keuangan sekolah</p>
            </div>
            <div class="flex gap-2 no-print">
                <button onclick="window.print()"
                    class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-print mr-2"></i>Cetak
                </button>
                <button onclick="downloadPDF()"
                    class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </button>
                <button onclick="exportToExcel()"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold shadow-lg">
                    <i class="fas fa-file-excel mr-2"></i>Export Excel
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-6 no-print">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                <select name="tahun"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $filterTahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan (Opsional)</label>
                <select name="bulan"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    <option value="">Semua Bulan</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $filterBulan == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Laporan</label>
                <select name="jenis"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500">
                    <option value="rekap" <?= $filterJenis === 'rekap' ? 'selected' : '' ?>>Rekap</option>
                    <option value="detail" <?= $filterJenis === 'detail' ? 'selected' : '' ?>>Detail</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-semibold">
                    <i class="fas fa-search mr-2"></i>Tampilkan
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6" id="summaryCards">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-6 text-white shadow-xl">
            <p class="text-green-100 text-sm mb-2">Total Pemasukan</p>
            <p class="text-3xl font-bold"><?= formatRupiah($totalMasuk) ?></p>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-xl p-6 text-white shadow-xl">
            <p class="text-red-100 text-sm mb-2">Total Pengeluaran</p>
            <p class="text-3xl font-bold"><?= formatRupiah($totalKeluar) ?></p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-6 text-white shadow-xl">
            <p class="text-blue-100 text-sm mb-2">Saldo</p>
            <p class="text-3xl font-bold"><?= formatRupiah($saldo) ?></p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-6 text-white shadow-xl">
            <p class="text-purple-100 text-sm mb-2">Transaksi</p>
            <p class="text-3xl font-bold"><?= count($transaksi) ?></p>
        </div>
    </div>

    <!-- Rekap Laporan -->
    <?php if ($filterJenis === 'rekap'): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6" id="reportContent">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Rekap Per Kategori - <?= $periodLabel ?></h3>
            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">#</th>
                            <th class="text-left">Kategori</th>
                            <th class="text-right">Pemasukan</th>
                            <th class="text-right">Pengeluaran</th>
                            <th class="text-right">Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($byKategori as $kategori => $data): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="font-semibold"><?= htmlspecialchars($kategori) ?></td>
                                <td class="text-right text-green-600 font-semibold"><?= formatRupiah($data['masuk']) ?></td>
                                <td class="text-right text-red-600 font-semibold"><?= formatRupiah($data['keluar']) ?></td>
                                <td
                                    class="text-right font-bold <?= ($data['masuk'] - $data['keluar']) >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= formatRupiah($data['masuk'] - $data['keluar']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-amber-50 font-bold">
                            <td colspan="2" class="text-right">TOTAL</td>
                            <td class="text-right text-green-600"><?= formatRupiah($totalMasuk) ?></td>
                            <td class="text-right text-red-600"><?= formatRupiah($totalKeluar) ?></td>
                            <td class="text-right text-blue-600"><?= formatRupiah($saldo) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <!-- Detail Laporan -->
        <div class="bg-white rounded-xl shadow-lg p-6" id="reportContent">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Detail Transaksi - <?= $periodLabel ?></h3>
            <div class="overflow-x-auto">
                <table class="data-table w-full">
                    <thead>
                        <tr>
                            <th class="text-left">#</th>
                            <th class="text-left">Tanggal</th>
                            <th class="text-left">Keterangan</th>
                            <th class="text-left">Kategori</th>
                            <th class="text-left">Jenis</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transaksi) > 0): ?>
                            <?php foreach ($transaksi as $index => $t): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= formatTanggal($t['tanggal'], 'short') ?></td>
                                    <td><?= htmlspecialchars($t['keterangan']) ?></td>
                                    <td><span
                                            class="text-xs px-2 py-1 bg-gray-100 rounded"><?= htmlspecialchars($t['kategori']) ?></span>
                                    </td>
                                    <td><span
                                            class="badge badge-<?= $t['jenis_transaksi'] === 'Masuk' ? 'success' : 'danger' ?>"><?= $t['jenis_transaksi'] ?></span>
                                    </td>
                                    <td
                                        class="text-right font-semibold <?= $t['jenis_transaksi'] === 'Masuk' ? 'text-green-600' : 'text-red-600' ?>">
                                        <?= formatRupiah($t['nominal']) ?>
                                    </td>
                                    <td class="text-right font-bold text-blue-600"><?= formatRupiah($t['saldo']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-8">Tidak ada transaksi pada periode ini</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="/e-TU/assets/js/app.js"></script>
<script>
    function downloadPDF() {
        const table = document.querySelector('.data-table');
        if (!table) {
            alert('Tabel tidak ditemukan!');
            return;
        }

        // Get summary values
        const summaryCards = document.querySelectorAll('#summaryCards .text-3xl');
        const totalMasuk = summaryCards[0]?.textContent.trim() || 'Rp 0';
        const totalKeluar = summaryCards[1]?.textContent.trim() || 'Rp 0';
        const saldo = summaryCards[2]?.textContent.trim() || 'Rp 0';

        // Get period
        const reportTitle = document.querySelector('#reportContent h3')?.textContent || '';
        const periodMatch = reportTitle.match(/- (.+)$/);
        const period = periodMatch ? periodMatch[1] : 'Laporan';

        // Create PDF container
        const pdfContainer = document.createElement('div');
        pdfContainer.style.fontFamily = 'Arial, sans-serif';
        pdfContainer.style.padding = '20px';
        pdfContainer.style.fontSize = '10pt';

        // Add title
        const title = document.createElement('h1');
        title.textContent = 'LAPORAN KEUANGAN';
        title.style.textAlign = 'center';
        title.style.fontSize = '18pt';
        title.style.marginBottom = '5px';
        pdfContainer.appendChild(title);

        // Add period
        const periodEl = document.createElement('h2');
        periodEl.textContent = period;
        periodEl.style.textAlign = 'center';
        periodEl.style.fontSize = '14pt';
        periodEl.style.color = '#666';
        periodEl.style.margin = '0 0 20px 0';
        pdfContainer.appendChild(periodEl);

        // Add summary table
        const summaryTable = document.createElement('table');
        summaryTable.style.width = '100%';
        summaryTable.style.marginBottom = '20px';
        summaryTable.style.border = '1px solid #ddd';
        summaryTable.style.borderCollapse = 'collapse';

        const summaryRow = document.createElement('tr');

        const cells = [
            { label: 'Total Pemasukan', value: totalMasuk, color: '#059669' },
            { label: 'Total Pengeluaran', value: totalKeluar, color: '#dc2626' },
            { label: 'Saldo', value: saldo, color: '#2563eb' }
        ];

        cells.forEach((cell, index) => {
            const td = document.createElement('td');
            td.style.padding = '15px';
            td.style.textAlign = 'center';
            if (index < 2) td.style.borderRight = '1px solid #ddd';

            const label = document.createElement('div');
            label.textContent = cell.label;
            label.style.color = '#666';
            label.style.fontSize = '10pt';
            label.style.marginBottom = '5px';

            const value = document.createElement('div');
            value.textContent = cell.value;
            value.style.fontSize = '16pt';
            value.style.fontWeight = 'bold';
            value.style.color = cell.color;

            td.appendChild(label);
            td.appendChild(value);
            summaryRow.appendChild(td);
        });

        summaryTable.appendChild(summaryRow);
        pdfContainer.appendChild(summaryTable);

        // Clone and style data table
        const tableClone = table.cloneNode(true);
        tableClone.style.width = '100%';
        tableClone.style.borderCollapse = 'collapse';
        tableClone.style.fontSize = '10pt';

        const allCells = tableClone.querySelectorAll('th, td');
        allCells.forEach(cell => {
            cell.style.border = '1px solid #ddd';
            cell.style.padding = '8px';
        });

        const headers = tableClone.querySelectorAll('th');
        headers.forEach(th => {
            th.style.backgroundColor = '#f5f5f5';
            th.style.fontWeight = 'bold';
        });

        pdfContainer.appendChild(tableClone);

        // F4 paper: 210mm x 330mm
        const opt = {
            margin: [15, 15, 15, 15],
            filename: `Laporan_Keuangan_${period.replace(/ /g, '_')}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false
            },
            jsPDF: {
                unit: 'mm',
                format: [210, 330],
                orientation: 'portrait'
            }
        };

        html2pdf().set(opt).from(pdfContainer).save();
    }


    function exportToExcel() {
        const table = document.querySelector('.data-table');
        const template = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <!--[if gte mso 9]>
            <xml>
                <x:ExcelWorkbook>
                    <x:ExcelWorksheets>
                        <x:ExcelWorksheet>
                            <x:Name>Laporan</x:Name>
                            <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
                        </x:ExcelWorksheet>
                    </x:ExcelWorksheets>
                </x:ExcelWorkbook>
            </xml>
            <![endif]-->
            <style>
                table { border-collapse: collapse; width: 100%; font-family: Arial; }
                th, td { border: 1px solid #000; padding: 8px; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .text-right { text-align: right; }
                .text-green-600 { color: #059669; }
                .text-red-600 { color: #dc2626; }
                .text-blue-600 { color: #2563eb; }
                .font-bold { font-weight: bold; }
            </style>
        </head>
        <body>
            <h2 style="text-align: center;">Laporan Keuangan - <?= $periodLabel ?></h2>
            ${table.outerHTML}
        </body>
        </html>`;

        const blob = new Blob([template], { type: 'application/vnd.ms-excel' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'Laporan_Keuangan_<?= $periodLabel ?>.xls';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
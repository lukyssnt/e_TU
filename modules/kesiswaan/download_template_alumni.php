<?php
// Set headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="template_import_alumni.csv"');

// Clean output buffer
if (ob_get_level())
    ob_end_clean();

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add separator hint for Excel
fwrite($output, "sep=;\n");

// Output BOM for Excel UTF-8 support
echo "\xEF\xBB\xBF";

// Output the column headings
fputcsv($output, ['NISN', 'Nama Lengkap', 'Jenis Kelamin (L/P)', 'Tahun Masuk', 'Tahun Lulus', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 'Alamat', 'Nama Ortu', 'No HP Ortu'], ';', '"', '\\');

// Output sample data
fputcsv($output, ['1234567890', 'Ahmad Contoh', 'L', '2020', '2023', 'Jakarta', '2005-01-01', 'Jl. Contoh No. 1', 'Budi Santoso', '081234567890'], ';', '"', '\\');
fputcsv($output, ['0987654321', 'Siti Sample', 'P', '2020', '2023', 'Bandung', '2005-05-15', 'Jl. Sample No. 2', 'Agus Salim', '081987654321'], ';', '"', '\\');

fclose($output);
exit;

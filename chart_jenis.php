<?php
include 'db.php';

// Cukup ambil kolom jenis untuk efisiensi
$raw_data = getSupabaseData("trash_data?select=jenis");

$total_logam = 0;
$total_non_logam = 0;

if (is_array($raw_data)) {
    foreach ($raw_data as $row) {
        // Amankan string: huruf kecil semua dan hilangkan spasi
        $jenis = strtolower(trim($row['jenis'] ?? ''));

        if ($jenis === 'logam') {
            $total_logam++;
        } elseif ($jenis === 'non-logam' || $jenis === 'nonlogam') {
            $total_non_logam++;
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'total_logam' => $total_logam,
    'total_non_logam' => $total_non_logam
]);
?>
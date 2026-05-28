<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta'); // Sesuaikan zona waktu WIB

// Ambil semua data created_at dari Supabase
$raw_data = getSupabaseData("trash_data?select=created_at");

$grouped = [];

if (is_array($raw_data)) {
    foreach ($raw_data as $row) {
        if (!empty($row['created_at'])) {
            // Ambil tanggalnya saja (Format: YYYY-MM-DD)
            $date = date('Y-m-d', strtotime($row['created_at']));
            
            if (!isset($grouped[$date])) {
                $grouped[$date] = 0;
            }
            $grouped[$date]++; // Tambah 1 setiap ada data di tanggal tersebut
        }
    }
}

// Urutkan berdasarkan tanggal (dari terlama ke terbaru)
ksort($grouped);

$data = [];
foreach ($grouped as $tgl => $total) {
    $data[] = [
        'tanggal' => $tgl,
        'total' => $total
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>
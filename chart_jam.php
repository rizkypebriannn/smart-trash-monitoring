<?php
include 'db.php';
date_default_timezone_set('Asia/Jakarta'); // WIB

$today = date('Y-m-d'); // Tanggal hari ini
$raw_data = getSupabaseData("trash_data?select=created_at");

$grouped = [];

if (is_array($raw_data)) {
    foreach ($raw_data as $row) {
        if (!empty($row['created_at'])) {
            $timestamp = strtotime($row['created_at']);
            $date = date('Y-m-d', $timestamp);

            // Cek apakah data ini terjadi hari ini
            if ($date === $today) {
                $hour = date('H', $timestamp); // Ambil jamnya saja (00-23)
                
                if (!isset($grouped[$hour])) {
                    $grouped[$hour] = 0;
                }
                $grouped[$hour]++;
            }
        }
    }
}

// Urutkan berdasarkan jam dari pagi ke malam
ksort($grouped);

$data = [];
foreach ($grouped as $jam => $total) {
    $data[] = [
        'jam' => $jam . ":00", // Tambahkan :00 agar tampilannya bagus (ex: 14:00)
        'total' => $total
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
?>
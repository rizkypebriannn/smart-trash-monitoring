<?php
require_once 'db.php';

$data = [
    'logam' => ['kapasitas' => '0', 'last_update' => ''],
    'nonlogam' => ['kapasitas' => '0', 'last_update' => '']
];

$query = mysqli_query($conn, "SELECT jenis, kapasitas, last_update FROM sampah_status");
while ($row = mysqli_fetch_assoc($query)) {
    $jenis = $row['jenis'];
    $kapasitas = $row['kapasitas'];

    $data[$jenis] = [
        'kapasitas' => $kapasitas,
        'status_text' => ($kapasitas === 'full') ? 'Penuh' : 'Kosong',
        'last_update' => $row['last_update']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);

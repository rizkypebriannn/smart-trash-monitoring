<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

date_default_timezone_set('Asia/Jakarta');

// ================= RESPONSE DEFAULT =================
$response = [
    "hari" => [
        "tgl" => date('Y-m-d'),
        "total" => 0
    ],
    "jam" => [
        "jam" => "-",
        "total" => 0
    ],
    "jenis" => [
        "jenis_sampah" => "-"
    ]
];

// ================= AMBIL SEMUA DATA DARI SUPABASE =================
// Diambil semua dulu, lalu difilter di PHP agar aman dari perbedaan timezone Supabase.
$data = getSupabaseData("trash_data?select=*");

if (!empty($data) && is_array($data)) {

    $tanggalHariIni = date('Y-m-d');

    $dataHariIni = [];

    // ================= FILTER DATA HARI INI =================
    foreach ($data as $row) {

        if (empty($row['created_at'])) {
            continue;
        }

        $tanggalData = date('Y-m-d', strtotime($row['created_at']));

        if ($tanggalData === $tanggalHariIni) {
            $dataHariIni[] = $row;
        }
    }

    // ================= JIKA ADA DATA HARI INI =================
    if (!empty($dataHariIni)) {

        // Total aktivitas hari ini
        $response['hari']['tgl'] = $tanggalHariIni;
        $response['hari']['total'] = count($dataHariIni);

        // ================= JAM TERSIBUK HARI INI =================
        $jamCounter = [];

        foreach ($dataHariIni as $row) {

            $jam = date('H:00', strtotime($row['created_at']));

            if (!isset($jamCounter[$jam])) {
                $jamCounter[$jam] = 0;
            }

            $jamCounter[$jam]++;
        }

        if (!empty($jamCounter)) {
            arsort($jamCounter);

            $jamTersibuk = array_key_first($jamCounter);

            $response['jam']['jam'] = $jamTersibuk;
            $response['jam']['total'] = $jamCounter[$jamTersibuk];
        }

        // ================= JENIS DOMINAN HARI INI =================
        $jenisCounter = [];

        foreach ($dataHariIni as $row) {

            if (empty($row['jenis'])) {
                continue;
            }

            $jenis = strtoupper(trim($row['jenis']));

            if (!isset($jenisCounter[$jenis])) {
                $jenisCounter[$jenis] = 0;
            }

            $jenisCounter[$jenis]++;
        }

        if (!empty($jenisCounter)) {
            arsort($jenisCounter);

            $jenisDominan = array_key_first($jenisCounter);

            $response['jenis']['jenis_sampah'] = $jenisDominan;
        }
    }
}

header('Content-Type: application/json');

echo json_encode($response);

?>
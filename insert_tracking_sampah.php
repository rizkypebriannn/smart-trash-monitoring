<?php
header('Content-Type: application/json');
require_once 'db.php'; // pastikan $conn aktif dan pakai MySQLi

// Ambil input mentah dari ESP32
$rawInput = file_get_contents('php://input');
file_put_contents(__DIR__ . '/debug_input.txt', $rawInput . PHP_EOL, FILE_APPEND);

// Decode JSON ke array
$input = json_decode($rawInput, true);

// Fungsi log ke file log_sampah.txt
function writeToLogFile($data) {
    $logFile = __DIR__ . '/log_sampah.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] " . json_encode($data) . PHP_EOL;
    @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['jenis'])) {
        $jenis = strtolower(trim($input['jenis']));
        $jenis = mysqli_real_escape_string($conn, $jenis);

        // Validasi isi 'jenis' yang diizinkan
        if ($jenis !== 'logam' && $jenis !== 'non-logam') {
            $error = ['status' => 'error', 'message' => 'Jenis tidak valid (hanya logam / non-logam)'];
            writeToLogFile($error);
            echo json_encode($error);
            exit;
        }

        // Simpan ke tabel riwayat_sampah
        $query = "INSERT INTO riwayat_sampah (jenis_sampah) VALUES ('$jenis')";
        $result = mysqli_query($conn, $query);

        $response = [
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Data berhasil disimpan ke riwayat_sampah' : mysqli_error($conn)
        ];
        writeToLogFile($response);
        echo json_encode($response);
    } else {
        $error = ['status' => 'error', 'message' => 'Parameter jenis tidak ditemukan'];
        writeToLogFile($error);
        echo json_encode($error);
    }
} else {
    $error = ['status' => 'error', 'message' => 'Method tidak diizinkan'];
    writeToLogFile($error);
    echo json_encode($error);
}

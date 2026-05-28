<?php
header('Content-Type: application/json');
require_once 'db.php'; // pastikan ini versi mysqli

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($input['jenis'])) {
        $jenis = mysqli_real_escape_string($conn, $input['jenis']);

        // Jika nilai kapasitas dikirimkan secara eksplisit
        if (isset($input['kapasitas'])) {
            $kapasitas = mysqli_real_escape_string($conn, $input['kapasitas']); // bisa angka atau 'full'

            $query = "UPDATE sampah_status SET kapasitas = '$kapasitas', last_update = NOW() WHERE jenis = '$jenis'";
        } else {
            // Default: tambahkan 1
            $query = "UPDATE sampah_status SET kapasitas = kapasitas + 1, last_update = NOW() WHERE jenis = '$jenis'";
        }

        $result = mysqli_query($conn, $query);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Query error: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Parameter tidak lengkap']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan123']);
}

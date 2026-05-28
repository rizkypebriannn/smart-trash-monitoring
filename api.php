<?php
require_once 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if ($data && isset($data['type_id'])) {
    $type_id = (int)$data['type_id'];

    // Mapping type_id ke jenis
    $jenis = $type_id === 1 ? 'logam' : ($type_id === 2 ? 'nonlogam' : null);

    if ($jenis) {
        try {
            // Tambah kapasitas 1 setiap kali ada sampah masuk
            $stmt = $pdo->prepare("UPDATE sampah_status 
                                   SET kapasitas = kapasitas + 1, last_update = NOW() 
                                   WHERE jenis = '$jenis'");
            $stmt->execute([$jenis]);

            echo json_encode(['status' => 'ok']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Jenis tidak valid']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
}
?>

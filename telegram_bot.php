<?php
require_once 'db.php';

// Konfigurasi bot Telegram
$botToken = '7476583187:AAFU6IthbfhhFgiKV_s0SKfZCw0GO3BPNCg';
// $apiUrl = "http://192.168.110.176/api.php/bot$botToken";
$apiUrl = "https://api.telegram.org/bot$botToken/";


// Fungsi untuk mengirim request ke API Telegram
function sendTelegramRequest($method, $params = []) {
    global $apiUrl;
    $url = $apiUrl . $method . '?' . http_build_query($params);
    return file_get_contents($url);
}

// Proses update dari Telegram
$update = json_decode(file_get_contents('php://input'), true);

if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = trim($message['text']);
    
    // Proses perintah /update
    if (strpos($text, '/update') === 0) {
        $parts = explode(' ', $text);
        if (count($parts) === 3) {
            $jenis = strtolower($parts[1]);
            $kapasitas = (int)$parts[2];
            
            if (in_array($jenis, ['logam', 'nonlogam']) && $kapasitas >= 0 && $kapasitas <= 100) {
                try {
                    // Update database
                    $stmt = $pdo->prepare("UPDATE sampah_status SET kapasitas = ?, last_update = NOW() WHERE jenis = ?");
                    $stmt->execute([$kapasitas, $jenis]);
                    
                    // Kirim respon ke Telegram
                    sendTelegramRequest('sendMessage', [
                        'chat_id' => $chatId,
                        'text' => "Data $jenis berhasil diupdate menjadi $kapasitas%"
                    ]);
                } catch (PDOException $e) {
                    sendTelegramRequest('sendMessage', [
                        'chat_id' => $chatId,
                        'text' => "Gagal update database: " . $e->getMessage()
                    ]);
                }
            } else {
                sendTelegramRequest('sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "Format salah. Gunakan: /update [nonlogam|logam] [0-100]"
                ]);
            }
        } else {
            sendTelegramRequest('sendMessage', [
                'chat_id' => $chatId,
                'text' => "Format salah. Gunakan: /update [nonlogam|logam] [0-100]"
            ]);
        }
    } else {
        sendTelegramRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => "Perintah tidak dikenali. Gunakan /update [nonlogam|logam] [0-100]"
        ]);
    }
}

echo "OK";
?>
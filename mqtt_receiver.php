
<?php
// mqtt_receiver.php
date_default_timezone_set('Asia/Jakarta');
require('vendor/autoload.php'); 

use \PhpMqtt\Client\MqttClient;
use \PhpMqtt\Client\ConnectionSettings;

// 1. DATA CLUSTER 
$server   = '558c0856a924493eb20c95313d4134b1.s1.eu.hivemq.cloud';
$port     = 8883; 
$clientId = 'php_bridge_' . uniqid();

$mqtt = new MqttClient($server, $port, $clientId);

// 2. KREDENSIAL 
$settings = (new ConnectionSettings())
    ->setUsername('rizkypebrian')
    ->setPassword('Password123')
    ->setUseTls(true) 
    ->setTlsSelfSignedAllowed(true); 

try {
    // Menghubungkan ke broker
    $mqtt->connect($settings, true);
    echo "=================================================\n";
    echo "✅ KONEKSI BERHASIL: Terhubung ke HiveMQ Cloud!\n";
    echo "=================================================\n";
} catch (Exception $e) {
    // Jika masih Unauthorized, error [6] akan muncul di sini
    die("❌ GAGAL KONEKSI: " . $e->getMessage() . "\n");
}

// 3. LOGIKA PENERIMAAN DATA
$mqtt->subscribe('trashbin/data', function ($topic, $message) {
    echo "\n[" . date('H:i:s') . "] Data Masuk: " . $message . "\n";
    
    $data = json_decode($message, true);
    $jenis = isset($data['jenis']) ? $data['jenis'] : $message;
    $kapasitas = isset($data['kapasitas']) ? (int)$data['kapasitas'] : 0;

    // DATA SUPABASE 
    $supabaseUrl = "https://aqptyhoaibyalurtyxbr.supabase.co"; 
    $supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImFxcHR5aG9haWJ5YWx1cnR5eGJyIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NzY3NTE2MjYsImV4cCI6MjA5MjMyNzYyNn0.b9M2E8FaA_6rgJamIj0ygt3ax5OKId17swdq6Q0ZdA8"; // Pastikan key ini utuh (copy dari tab browser)
    
    $url = $supabaseUrl . "/rest/v1/trash_data";

    $payload = json_encode([
        'jenis'     => $jenis,
        'kapasitas' => $kapasitas,
        'timestamp' => time() 
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 201 || $httpCode == 200) {
        echo "   -> 🟢 Tersimpan ke Supabase!\n";
    } else {
        echo "   -> 🔴 Supabase Error ($httpCode)\n";
    }
}, 0);

$mqtt->loop(true);
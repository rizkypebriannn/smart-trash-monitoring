<?php
// db.example.php
// Salin file ini menjadi db.php, lalu isi dengan Supabase URL dan Supabase Key asli.

$supabaseUrl = "ISI_SUPABASE_URL";
$supabaseKey = "ISI_SUPABASE_ANON_KEY";

function getSupabaseData($query) {
    global $supabaseUrl, $supabaseKey;

    $ch = curl_init($supabaseUrl . "/rest/v1/" . $query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $supabaseKey,
        'Authorization: Bearer ' . $supabaseKey
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
?>
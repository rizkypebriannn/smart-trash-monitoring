<?php

include 'db.php';

// Ambil semua data
$data = getSupabaseData("trash_data?select=id");

if (!empty($data)) {

    foreach ($data as $row) {

        $id = $row['id'];

        $ch = curl_init($supabaseUrl . "/rest/v1/trash_data?id=eq." . $id);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey
        ]);

        curl_exec($ch);

        curl_close($ch);
    }
}

header("Location: riwayat_sampah.php");

exit;
?>
<?php

// ================= STATUS TEXT =================
// Menentukan status berdasarkan nilai kapasitas angka
function getStatusText($kapasitas) {

    $kapasitas = (int) $kapasitas;

    if ($kapasitas >= 100) {
        return "PENUH";
    }

    if ($kapasitas <= 10) {
        return "KOSONG";
    }

    return "TERSEDIA";
}


// ================= STATUS COLOR =================
// Menentukan warna status berdasarkan kapasitas
function getStatusColor($kapasitas) {

    $kapasitas = (int) $kapasitas;

    if ($kapasitas >= 100) {
        return "#ef4444"; // merah
    }

    if ($kapasitas <= 10) {
        return "#10b981"; // hijau
    }

    return "#f59e0b"; // kuning
}


// ================= NOTIFICATION TEXT =================
// Menentukan teks notifikasi utama dashboard
function getNotificationText($kapasitas_nonlogam, $kapasitas_logam) {

    $kapasitas_nonlogam = (int) $kapasitas_nonlogam;
    $kapasitas_logam = (int) $kapasitas_logam;

    if ($kapasitas_logam >= 100 && $kapasitas_nonlogam >= 100) {
        return "Kapasitas logam dan non-logam penuh, segera lakukan pengosongan tempat sampah.";
    }

    if ($kapasitas_logam >= 100) {
        return "Kapasitas logam penuh, segera lakukan pengosongan.";
    }

    if ($kapasitas_nonlogam >= 100) {
        return "Kapasitas non-logam penuh, segera kosongkan tempat sampah.";
    }

    return "Tempat sampah masih tersedia.";
}


// ================= NOTIFICATION STYLE =================
// Optional: jika nanti dipakai untuk memberi warna notifikasi
function getNotificationStyle($kapasitas_nonlogam, $kapasitas_logam) {

    $kapasitas_nonlogam = (int) $kapasitas_nonlogam;
    $kapasitas_logam = (int) $kapasitas_logam;

    if ($kapasitas_logam >= 100 && $kapasitas_nonlogam >= 100) {
        return "background-color: #fee2e2; color: #dc2626;";
    }

    if ($kapasitas_logam >= 100 || $kapasitas_nonlogam >= 100) {
        return "background-color: #fff7ed; color: #ea580c;";
    }

    return "background-color: #ecfdf5; color: #059669;";
}


// ================= FORMAT WAKTU =================
// Format waktu dari Supabase ke WIB/local display
function formatWaktu($timestamp) {

    if (empty($timestamp) || $timestamp === 'Belum ada data') {
        return "-";
    }

    return date('d/m/Y H:i:s', strtotime($timestamp));
}

?>
<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

date_default_timezone_set('Asia/Jakarta');

// ================= AMBIL DATA SUPABASE =================
$data_sampah = getSupabaseData(
    "trash_data?select=*&order=created_at.desc"
);

// validasi data
if (!is_array($data_sampah)) {
    $data_sampah = [];
}

// ================= TOTAL DATA =================
$total_data = count($data_sampah);

// ================= TOTAL LOGAM =================
$total_logam = 0;
$total_nonlogam = 0;

foreach ($data_sampah as $row) {

    $jenis = strtolower(trim($row['jenis'] ?? ''));

    if ($jenis === 'logam') {
        $total_logam++;
    }

    if ($jenis === 'non-logam' || $jenis === 'nonlogam') {
        $total_nonlogam++;
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Riwayat Sampah - IoT Monitoring</title>

    <meta http-equiv="refresh" content="15">

    <!-- FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- ICON -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- TAILWIND -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: '#3b82f6',
                        success: '#10b981',
                        danger: '#ef4444',
                        lightbg: '#f8fafc'
                    }
                }
            }
        }
    </script>

</head>

<body class="bg-lightbg text-slate-700 font-sans antialiased flex h-screen overflow-hidden">

<!-- OVERLAY -->
<div id="sidebarOverlay"
     onclick="toggleSidebar()"
     class="fixed inset-0 bg-slate-900/50 z-40 hidden">
</div>

<!-- SIDEBAR -->
<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform -translate-x-full transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:flex flex-col">

    <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200">

        <h2 class="text-xl font-bold text-slate-800">
            <i class="fa-solid fa-trash-can text-primary mr-2"></i>
            IoT Panel
        </h2>

        <button onclick="toggleSidebar()" class="md:hidden text-slate-400">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

    </div>

    <div class="p-6 text-center border-b border-slate-200">

        <div class="w-16 h-16 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-2xl text-slate-400 mb-3 shadow-inner">
            <i class="fa-solid fa-user-tie"></i>
        </div>

        <h3 class="font-semibold text-slate-800">
            Admin Panel
        </h3>

        <p class="text-xs text-slate-500">
            Sistem Monitoring
        </p>

    </div>

    <nav class="flex-1 p-4 space-y-2">

        <a href="index.php"
           class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-slate-50 rounded-lg font-medium">

            <i class="fa-solid fa-gauge w-6"></i>
            Dashboard

        </a>

        <a href="#"
           class="flex items-center px-4 py-2.5 bg-blue-50 text-primary rounded-lg font-medium">

            <i class="fa-solid fa-history w-6"></i>
            Riwayat Data

        </a>

    </nav>

</aside>

<!-- MAIN -->
<main class="flex-1 flex flex-col overflow-hidden">

    <!-- HEADER -->
    <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">

        <div class="flex items-center">

            <button onclick="toggleSidebar()"
                    class="mr-4 text-slate-600 md:hidden">

                <i class="fa-solid fa-bars text-xl"></i>

            </button>

            <h1 class="text-lg font-semibold text-slate-800">
                Riwayat Aktivitas Sampah
            </h1>

        </div>

        <div class="text-sm text-slate-400">

            <i class="fa-solid fa-rotate-right mr-1"></i>
            Auto Refresh 15s

        </div>

    </header>

    <!-- CONTENT -->
    <div class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">

        <!-- ACTION -->
        <div class="flex flex-wrap gap-3 justify-between items-center mb-6">

            <a href="index.php"
               class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg shadow-sm hover:bg-slate-50 text-sm font-medium">

                <i class="fa-solid fa-arrow-left mr-2"></i>
                Dashboard

            </a>

            <div class="flex gap-2">


                <button onclick="hapusRiwayat()"
                        class="inline-flex items-center px-4 py-2 bg-red-500 text-white rounded-lg shadow-sm hover:bg-red-600 text-sm font-medium">

                    <i class="fa-solid fa-trash mr-2"></i>
                    Hapus Riwayat

                </button>

            </div>

        </div>

        <!-- STATISTIK -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">

                <p class="text-xs text-slate-400 uppercase font-semibold mb-1">
                    Total Aktivitas
                </p>

                <h3 class="text-3xl font-bold text-slate-800">
                    <?= $total_data ?>
                </h3>

            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">

                <p class="text-xs text-blue-400 uppercase font-semibold mb-1">
                    Total Logam
                </p>

                <h3 class="text-3xl font-bold text-blue-500">
                    <?= $total_logam ?>
                </h3>

            </div>

            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">

                <p class="text-xs text-emerald-400 uppercase font-semibold mb-1">
                    Total Non-Logam
                </p>

                <h3 class="text-3xl font-bold text-emerald-500">
                    <?= $total_nonlogam ?>
                </h3>

            </div>

        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="p-5 border-b border-slate-100">

                <h3 class="font-bold text-slate-800">
                    Log Aktivitas Sensor
                </h3>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-left border-collapse">

                    <thead>

                    <tr class="bg-slate-50 text-slate-500 uppercase text-[11px] font-bold tracking-wider">

                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Jenis Sampah</th>

                    </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                    <?php if (!empty($data_sampah)): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_sampah as $row): ?>

                            <?php

                            $jenis = strtoupper($row['jenis'] ?? '-');

                            $waktu = $row['created_at'] ?? '';

                            $isLogam = strtolower($row['jenis']) == 'logam';

                            $badge = $isLogam
                                ? 'bg-blue-50 text-blue-600 border-blue-100'
                                : 'bg-emerald-50 text-emerald-600 border-emerald-100';

                            ?>

                            <tr class="hover:bg-slate-50 transition-colors">

                                <td class="px-6 py-4 text-sm font-medium text-slate-500">
                                    <?= $no++ ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-700">
                                    <?= date('d M Y', strtotime($waktu)) ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-slate-500">
                                    <?= date('H:i:s', strtotime($waktu)) ?>
                                </td>

                                <td class="px-6 py-4">

                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-bold border <?= $badge ?>">

                                        <?= $jenis ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="4"
                                class="px-6 py-10 text-center text-slate-400 text-sm">

                                <i class="fa-solid fa-database text-3xl mb-3 block"></i>

                                Belum ada data aktivitas sensor.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</main>

<script>

function toggleSidebar() {

    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

// ================= HAPUS RIWAYAT =================
function hapusRiwayat() {

    const konfirmasi = confirm(
        "Apakah yakin ingin menghapus seluruh riwayat data?"
    );

    if (konfirmasi) {

        window.location.href = "hapus_riwayat.php";
    }
}

</script>

</body>
</html>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
require_once 'helpers.php';

// Ambil data terbaru dari Supabase
$dataLogam = getSupabaseData("trash_data?jenis=eq.logam&order=created_at.desc&limit=1");
$dataNonLogam = getSupabaseData("trash_data?or=(jenis.eq.non-logam,jenis.eq.nonlogam)&order=created_at.desc&limit=1");

$data = [];
if (!empty($dataLogam)) {
    $row = $dataLogam[0];
    $row['last_update'] = formatWaktu($row['created_at']);
    $data[] = $row;
}
if (!empty($dataNonLogam)) {
    $row = $dataNonLogam[0];
    $row['last_update'] = formatWaktu($row['created_at']);
    $data[] = $row;
}

$kapasitas_logam = (!empty($dataLogam)) ? $dataLogam[0]['kapasitas'] : '0';
$kapasitas_nonlogam = (!empty($dataNonLogam)) ? $dataNonLogam[0]['kapasitas'] : '0';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoring Sampah IoT</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#3b82f6', success: '#10b981', dark: '#1e293b', lightbg: '#f8fafc' }
                }
            }
        }
    </script>
    <style> canvas { width: 100% !important; max-height: 280px !important; } </style>
</head>

<body class="bg-lightbg text-slate-700 font-sans antialiased flex h-screen overflow-hidden">
    <div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/50 z-40 hidden transition-opacity"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 transform -translate-x-full transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:flex flex-col">
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-800"><i class="fa-solid fa-trash-can text-primary mr-2"></i> IoT Panel</h2>
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
        </div>
        <div class="p-6 text-center border-b border-slate-200">
            <div class="w-16 h-16 rounded-full bg-slate-100 mx-auto flex items-center justify-center text-2xl text-slate-400 mb-3 shadow-inner"><i class="fa-solid fa-user-tie"></i></div>
            <h3 class="font-semibold text-slate-800">Admin Panel</h3>
            <p class="text-xs text-slate-500">Sistem Monitoring</p>
        </div>
        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="flex items-center px-4 py-2.5 bg-blue-50 text-primary rounded-lg font-medium"><i class="fa-solid fa-gauge w-6"></i> Dashboard</a>
            <a href="riwayat_sampah.php" class="flex items-center px-4 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-primary transition-colors rounded-lg font-medium"><i class="fa-solid fa-history w-6"></i> Riwayat Data</a>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="mr-4 text-slate-600 md:hidden flex items-center"><i class="fa-solid fa-bars text-xl"></i></button>
                <h1 class="text-lg font-semibold text-slate-800">Dashboard Real-Time</h1>
            </div>
            <div class="flex items-center text-slate-500">
                <span class="hidden sm:inline text-sm font-medium mr-4">Status: <span class="text-success"><i class="fa-solid fa-circle text-[10px]"></i> Online</span></span>
                <i class="fa-regular fa-bell text-lg hover:text-primary cursor-pointer"></i>
            </div>
        </header>

        <div class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
            
            <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div class="text-sm font-medium text-slate-600">
                    <i class="fa-solid fa-info-circle text-primary mr-2"></i> <?= getNotificationText($kapasitas_nonlogam, $kapasitas_logam) ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <?php foreach($data as $row): 
                    $isFull = (int)$row['kapasitas'] >= 100;
                    $jenisID = strtolower(str_replace('-', '', $row['jenis']));
                    $iconClass = ($jenisID == 'logam') ? 'fa-magnet text-blue-500' : 'fa-leaf text-emerald-500';
                    $bgIcon = ($jenisID == 'logam') ? 'bg-blue-50' : 'bg-emerald-50';
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 flex flex-col relative overflow-hidden">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Kapasitas <?= ucfirst($row['jenis']) ?></p>
                            <h3 id="kapasitas-<?= $jenisID ?>" class="text-2xl font-bold <?= $isFull ? 'text-red-500' : 'text-slate-800' ?>">
                                <?= $row['kapasitas'] ?>%
                            </h3>
                        </div>
                        <div class="w-12 h-12 rounded-full <?= $bgIcon ?> flex items-center justify-center text-xl">
                            <i class="fa-solid <?= $iconClass ?>"></i>
                        </div>
                    </div>
                    <div class="mt-auto">
                        <span id="status-<?= $jenisID ?>" class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium <?= $isFull ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' ?>">
                            Status: <?= $isFull ? 'Penuh' : 'Tersedia' ?>
                        </span>
                        <p class="text-[11px] text-slate-400 mt-2 text-right">Update: <?= $row['last_update'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 col-span-1 md:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-bold text-slate-700">📡 Real-Time Sensor (MQTT)</h4>
                        <span id="mqttStatus" class="text-xs font-medium text-amber-500 bg-amber-50 px-2 py-1 rounded-md">Menunggu data...</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Deteksi Terakhir</p>
                            <p id="mqttJenis" class="text-lg font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Latency / Ping</p>
                            <p id="mqttLatency" class="text-lg font-semibold text-slate-800">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 mb-6 flex items-start gap-4">
                <div class="mt-1 text-primary"><i class="fa-solid fa-lightbulb text-xl"></i></div>
                <div>
                    <h5 class="text-sm font-bold text-slate-700 mb-1">Analisis Sampah Harian</h5>
                    <p id="insightText" class="text-sm text-slate-500 leading-relaxed">Memuat analisis data harian...</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Komparasi Logam vs Non-Logam</h4>
                    <div class="relative w-full h-64 flex justify-center">
                        <canvas id="chartJenis"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 lg:col-span-2">
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Tren Sampah per Jam</h4>
                    <div class="relative w-full h-64">
                        <canvas id="chartJam"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Volume Sampah Harian</h4>
                    <div class="relative w-full h-64">
                        <canvas id="chartSampah"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                    <h4 class="text-sm font-bold text-slate-700 mb-4">Grafik Real-Time</h4>
                    <div class="relative w-full h-64">
                        <canvas id="chartRealtime"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
    <script src="script.js"></script>
    
    <script>
        // Fungsi Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Global Chart Defaults
        Chart.defaults.color = '#64748b';
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.scale.grid.color = '#f1f5f9';

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        };

        // Fetch Data untuk Grafik (History)
        fetch('chart_data.php').then(r=>r.json()).then(d=>{
            if(!d.length) return;
            new Chart(document.getElementById('chartSampah'),{
                type:'line',
                data:{
                    labels:d.map(x=>x.tanggal),
                    datasets:[{
                        label: 'Total Sampah',
                        data:d.map(x=>x.total),
                        borderColor:'#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: chartOptions
            });
        });

        fetch('chart_jenis.php').then(r=>r.json()).then(d=>{
            new Chart(document.getElementById('chartJenis'),{
                type:'doughnut',
                data:{
                    labels:['Logam','Non-Logam'],
                    datasets:[{
                        data:[d.total_logam || 0, d.total_non_logam || 0],
                        backgroundColor:['#3b82f6', '#10b981'],
                        borderWidth: 0
                    }]
                },
                options: { ...chartOptions, cutout: '75%' }
            });
        });

        fetch('chart_jam.php').then(r=>r.json()).then(d=>{
            if(!d.length) return;
            new Chart(document.getElementById('chartJam'),{
                type:'bar',
                data:{
                    labels:d.map(x=>x.jam),
                    datasets:[{
                        label: 'Intensitas',
                        data:d.map(x=>x.total),
                        backgroundColor:'#10b981',
                        borderRadius: 4
                    }]
                },
                options: chartOptions
            });
        });

        fetch('insight.php')
.then(res => res.json())
.then(data => {

    const insightEl = document.getElementById("insightText");

    if (!insightEl) return;

    if (!data.hari || data.hari.total == 0) {
        insightEl.innerHTML = "Belum ada aktivitas pembuangan sampah hari ini.";
        return;
    }

    insightEl.innerHTML =
        `Hari ini terdeteksi <b>${data.hari.total}</b> aktivitas pembuangan sampah. Jam tersibuk: <b>${data.jam.jam}</b>. Dominasi: <b>${data.jenis.jenis_sampah}</b>.`;

})
.catch(() => {
    document.getElementById("insightText").innerHTML =
        "Gagal memuat data analisis.";
});
    </script>
</body>
</html>
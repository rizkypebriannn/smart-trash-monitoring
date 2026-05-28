// ================= GLOBAL VARIABLES =================
let chartRealtime;
let totalLogam = 0;
let totalNonLogam = 0;

function safeSetText(id, value) {
    const el = document.getElementById(id);
    if (el && value !== undefined) {
        el.textContent = value;
    }
}

// ================= INITIALIZE CHART =================
window.addEventListener("load", () => {
    const ctx = document.getElementById('chartRealtime');
    if (!ctx) return;

    chartRealtime = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Logam', 'Non-Logam'],
            datasets: [{
                label: 'Jumlah Deteksi',
                data: [0, 0],
                backgroundColor: ['#3b82f6', '#10b981'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});

// ================= MQTT CONFIGURATION =================
const brokerUrl = 'wss://558c0856a924493eb20c95313d4134b1.s1.eu.hivemq.cloud:8884/mqtt';

const client = mqtt.connect(brokerUrl, {
    username: 'rizkypebrian',
    password: 'Password123',
    clientId: 'web_client_' + Math.random().toString(16).substr(2, 8),
    path: '/mqtt',
    clean: true,
    connectTimeout: 5000,
    reconnectPeriod: 2000,
});

// ================= MQTT EVENTS =================
client.on('connect', () => {
    console.log("✅ Dashboard Connected to HiveMQ");
    safeSetText("mqttStatus", "🟢 Online");
    client.subscribe('trashbin/data');
});

client.on('message', (topic, message) => {
    const receiveTime = Date.now();
    const rawMessage = message.toString();
    let data = {};

    try {
        data = JSON.parse(rawMessage);
        console.log("📩 Data Masuk:", data);
    } catch (e) {
        console.log("⚠️ Bukan JSON:", rawMessage);
        return; 
    }

    // 1. HITUNG LATENCY
    let latencyDisplay = "-";
    if (data.timestamp) {
        const sendTimeMS = data.timestamp * 1000;
        const diff = Math.abs(receiveTime - sendTimeMS);
        latencyDisplay = diff + " ms";
    }

    // 2. UPDATE UI MQTT (Sesuaikan dengan ID di index.php kamu)
    const jenisBersih = (data.jenis || "").toLowerCase().trim();
    safeSetText("mqttJenis", jenisBersih.toUpperCase());
    safeSetText("mqttLatency", latencyDisplay);
    
    const statusBadge = document.getElementById("mqttStatus");
    if (statusBadge) {
        statusBadge.textContent = "📥 Data Baru!";
        statusBadge.className = "text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-md";
    }

    // 3. UPDATE KARTU KAPASITAS (Atas)
    if (data.kapasitas !== undefined) {
        const capId = "kapasitas-" + (jenisBersih === "logam" ? "logam" : "non-logam");
        const statusId = "status-" + (jenisBersih === "logam" ? "logam" : "non-logam");
        
        const capEl = document.getElementById(capId);
        const statusEl = document.getElementById(statusId);

        if (capEl) {
            capEl.textContent = data.kapasitas + "%";
            
            // Logika Warna Berdasarkan Kapasitas
            if (data.kapasitas >= 100) {
                capEl.className = "text-2xl font-bold text-red-500";
                if (statusEl) {
                    statusEl.textContent = "Status: Penuh";
                    statusEl.className = "inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-red-50 text-red-600";
                }
            } else {
                capEl.className = "text-2xl font-bold text-slate-800";
                if (statusEl) {
                    statusEl.textContent = "Status: Tersedia";
                    statusEl.className = "inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-600";
                }
            }
        }
    }

    // 4. UPDATE CHART
    if (chartRealtime) {
        if (jenisBersih === "logam") totalLogam++;
        else if (jenisBersih === "non-logam") totalNonLogam++;
        
        chartRealtime.data.datasets[0].data = [totalLogam, totalNonLogam];
        chartRealtime.update();
    }

    // Reset status badge setelah 2 detik
    setTimeout(() => {
        if (client.connected && statusBadge) {
            statusBadge.textContent = "🟢 Online";
            statusBadge.className = "text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md";
        }
    }, 2000);
});
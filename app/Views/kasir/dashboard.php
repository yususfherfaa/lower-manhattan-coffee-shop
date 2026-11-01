<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Kasir — <?= esc($nama_warkop) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .dashboard-header {
        margin-bottom: var(--sp-6);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: var(--sp-4);
    }
    .dashboard-header h1 {
        font-size: var(--fs-h1);
        margin: 0;
    }
    .dashboard-header p {
        color: var(--taupe);
        margin: 4px 0 0 0;
        font-size: var(--fs-sm);
    }
    .stats-grid-kasir {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: var(--sp-5);
        margin-bottom: var(--sp-8);
    }
    .stat-card-uniform {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-lg);
        padding: var(--sp-6);
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: var(--sp-5);
        min-height: 120px;
        transition: all var(--dur) var(--ease);
    }
    .stat-card-uniform:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--beige);
    }
    .stat-icon-wrap {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--sand);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
        border: 2px solid var(--beige);
    }
    .stat-meta {
        flex: 1;
        min-width: 0;
    }
    .stat-meta h3 {
        color: var(--taupe);
        font-size: var(--fs-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin: 0 0 6px 0;
        font-family: var(--font-body);
        font-weight: 600;
    }
    .stat-meta .stat-num {
        color: var(--espresso);
        font-family: var(--font-heading);
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }
    .action-grid-kasir {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: var(--sp-4);
        margin-top: var(--sp-4);
    }
    .action-btn-kasir {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-md);
        padding: var(--sp-5);
        text-decoration: none;
        color: var(--espresso);
        display: flex;
        align-items: center;
        gap: var(--sp-4);
        transition: all var(--dur-fast) var(--ease);
        box-shadow: var(--shadow-sm);
    }
    .action-btn-kasir:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        border-color: var(--terracotta);
        background: #fff;
    }
    .action-btn-kasir .action-icon {
        width: 44px;
        height: 44px;
        border-radius: var(--r-sm);
        background: var(--grad-btn);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .action-btn-kasir .action-text {
        font-weight: 600;
        font-size: var(--fs-sm);
    }
    .action-btn-kasir .action-arrow {
        margin-left: auto;
        color: var(--taupe);
        font-size: 14px;
        transition: transform var(--dur-fast) var(--ease);
    }
    .action-btn-kasir:hover .action-arrow {
        transform: translateX(4px);
        color: var(--terracotta);
    }
    /* Notification Toast Slide-In */
    .notif-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--espresso);
        color: var(--cream);
        border: 1px solid var(--terracotta);
        border-radius: var(--r-md);
        padding: var(--sp-4) var(--sp-5);
        box-shadow: var(--shadow-xl);
        z-index: 2000;
        display: flex;
        align-items: center;
        gap: var(--sp-3);
        transform: translateY(120%);
        transition: transform var(--dur) var(--ease);
    }
    .notif-toast.show {
        transform: translateY(0);
    }
    .row-highlight-pulse {
        animation: rowFlash 2s ease-out;
    }
    @keyframes rowFlash {
        0%, 30% { background-color: rgba(217, 164, 65, 0.35) !important; }
        100% { background-color: transparent; }
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_kasir') ?>

<!-- NOTIFICATION PANEL -->
<div class="notification-panel" id="notification-panel">
    <div class="notification-header">
        <span>🔔 Pesanan Masuk</span>
        <button onclick="closeNotificationPanel()" style="background: none; border: none; color: var(--cream); font-size: 18px; cursor: pointer;">✕</button>
    </div>
    <ul class="notification-list" id="notification-list">
        <li class="notification-empty">Tidak ada pesanan baru</li>
    </ul>
</div>

<!-- FLOATING TOAST UNTUK PESANAN BARU -->
<div class="notif-toast" id="notifToast">
    <span style="font-size:24px;">🔔</span>
    <div>
        <div style="font-weight:700; font-size:var(--fs-sm); color:#fff;" id="notifToastTitle">Pesanan Baru Masuk!</div>
        <div style="font-size:var(--fs-xs); color:var(--beige);" id="notifToastDesc">Meja ...</div>
    </div>
</div>

<div class="container reveal">

    <div class="dashboard-header">
        <div>
            <h1>Dashboard Kasir</h1>
            <p>Selamat bertugas, <b><?= esc($nama_kasir) ?></b>. Kelola pesanan dan transaksi harian dengan cepat.</p>
        </div>
        <div class="text-sm text-muted">
            📅 <?= date('d M Y') ?>
        </div>
    </div>

    <!-- 3 KARTU STATISTIK DENGAN TINGGI SERAGAM -->
    <div class="stats-grid-kasir">

        <div class="stat-card-uniform">
            <div class="stat-icon-wrap">📋</div>
            <div class="stat-meta">
                <h3>Total Pesanan Hari Ini</h3>
                <p class="stat-num count-up"><?= (int)$total_pesanan ?></p>
            </div>
        </div>

        <div class="stat-card-uniform">
            <div class="stat-icon-wrap">💰</div>
            <div class="stat-meta">
                <h3>Total Pendapatan Hari Ini</h3>
                <p class="stat-num count-up">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
            </div>
        </div>

        <div class="stat-card-uniform">
            <div class="stat-icon-wrap">☕</div>
            <div class="stat-meta">
                <h3>Total Menu</h3>
                <p class="stat-num count-up"><?= (int)$jumlah_produk ?></p>
            </div>
        </div>

    </div>

    <div style="background:var(--sand); border:1px solid var(--beige); border-radius:var(--r-lg); padding:12px 18px; margin-bottom:var(--sp-6); display:flex; align-items:center; gap:12px; font-size:var(--fs-sm); color:var(--espresso);">
        <span style="font-size:22px; flex-shrink:0;">⚡</span>
        <div>
            <strong>Status Kasir Aktif:</strong> Sistem secara otomatis memantau pesanan baru dari meja pelanggan. Anda akan menerima notifikasi suara dan tanda lonceng jika ada pesanan masuk yang siap diproses.
        </div>
    </div>

    <!-- MENU AKSI KASIR -->
    <h2 style="text-align:left; font-size:var(--fs-h3); margin-bottom:var(--sp-4);">Menu Operasional</h2>

    <div class="action-grid-kasir">
        <a href="<?= base_url('/kasir/pesanan') ?>" class="action-btn-kasir">
            <div class="action-icon">📋</div>
            <div>
                <div class="action-text">Kelola Pesanan</div>
                <small class="text-muted" style="font-size:11px;">Konfirmasi pembayaran & status saji</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/laporan') ?>" class="action-btn-kasir">
            <div class="action-icon">📈</div>
            <div>
                <div class="action-text">Laporan Penjualan</div>
                <small class="text-muted" style="font-size:11px;">Rekap shift kasir & cetak PDF</small>
            </div>
            <span class="action-arrow">→</span>
        </a>

        <a href="<?= base_url('/kasir/menu') ?>" class="action-btn-kasir">
            <div class="action-icon">☕</div>
            <div>
                <div class="action-text">Manajemen Menu</div>
                <small class="text-muted" style="font-size:11px;">Update stok, harga & varian menu</small>
            </div>
            <span class="action-arrow">→</span>
        </a>
    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc($nama_warkop) ?> — Dashboard Kasir
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
// Notification System (Preserved Logic & IDs)
let lastKnownNotifCount = 0;

function toggleNotificationPanel() {
    const panel = document.getElementById('notification-panel');
    panel.classList.toggle('show');
}

function closeNotificationPanel() {
    const panel = document.getElementById('notification-panel');
    panel.classList.remove('show');
}

// Close panel when clicking outside
document.addEventListener('click', function(event) {
    const panel = document.getElementById('notification-panel');
    const bell = document.querySelector('.notification-bell');
    
    if (panel && bell && !panel.contains(event.target) && !bell.contains(event.target)) {
        panel.classList.remove('show');
    }
});

// Format time
function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
    return Math.floor(diff / 86400) + ' hari lalu';
}

function showNewOrderToast(latest) {
    const toast = document.getElementById('notifToast');
    const title = document.getElementById('notifToastTitle');
    const desc = document.getElementById('notifToastDesc');
    if (!toast) return;

    title.textContent = 'Pesanan Baru di ' + (latest.id_meja || 'Meja');
    const notes = (latest.items || [])
        .filter(item => item.catatan)
        .map(item => item.nama + ': ' + item.catatan);
    const orderSummary = (latest.nama_pelanggan || 'Pelanggan') + ' · Rp ' + parseInt(latest.total || 0).toLocaleString('id-ID');
    desc.textContent = notes.length ? orderSummary + ' · ' + notes.join(' | ') : orderSummary;
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 4000);
}

// Fetch notifications
function fetchNotifications() {
    fetch('<?= base_url('/api/notifications') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.data.length > lastKnownNotifCount && data.data.length > 0) {
                    // Trigger sound & slide-in toast
                    playNotificationSound();
                    showNewOrderToast(data.data[0]);
                }
                lastKnownNotifCount = data.data.length;
                updateNotificationUI(data.data);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Update notification UI
function updateNotificationUI(notifications) {
    const list = document.getElementById('notification-list');
    const count = document.getElementById('notif-count');
    if (!list || !count) return;
    
    if (notifications.length === 0) {
        list.innerHTML = '<li class="notification-empty">Tidak ada pesanan baru</li>';
        count.style.display = 'none';
    } else {
        count.textContent = notifications.length;
        count.style.display = 'flex';
        list.replaceChildren();
        notifications.forEach(notif => {
            const item = document.createElement('li');
            item.className = 'notification-item unread';
            item.addEventListener('click', () => handleNotificationClick(notif.id_notif));

            const details = [
                ['notification-item-meja', '📍 ' + (notif.id_meja || '')],
                ['notification-item-nama', '👤 ' + (notif.nama_pelanggan || '')],
                ['notification-item-total', '💰 Rp ' + parseInt(notif.total || 0).toLocaleString('id-ID')],
            ];
            details.forEach(([className, text]) => {
                const line = document.createElement('div');
                line.className = className;
                line.textContent = text;
                item.appendChild(line);
            });

            (notif.items || []).forEach(orderItem => {
                const line = document.createElement('div');
                line.style.cssText = 'color:var(--espresso);font-size:12px;margin-top:5px;';
                line.textContent = (orderItem.nama || 'Menu') + ' × ' + parseInt(orderItem.qty || 0);
                if (orderItem.opsi) line.textContent += ' · ' + orderItem.opsi;
                if (orderItem.suhu) line.textContent += ' · ' + (orderItem.suhu === 'panas' ? 'Panas' : 'Es');
                item.appendChild(line);

                if (orderItem.catatan) {
                    const note = document.createElement('div');
                    note.style.cssText = 'color:var(--terracotta);font-size:12px;margin:2px 0 0 8px;';
                    note.textContent = 'Catatan: ' + orderItem.catatan;
                    item.appendChild(note);
                }
            });

            const time = document.createElement('div');
            time.className = 'notification-item-time';
            time.textContent = '⏱️ ' + formatTime(notif.created_at);
            item.appendChild(time);
            list.appendChild(item);
        });
    }
}

// Handle notification click
function handleNotificationClick(id_notif) {
    fetch('<?= base_url('/api/notifications/mark-read') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id_notif=' + id_notif
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications();
            if (document.location.pathname.includes('/kasir/pesanan')) {
                location.reload();
            }
        }
    });
    
    window.location.href = '<?= base_url('/kasir/pesanan') ?>';
}

// Polling every 3 seconds
setInterval(fetchNotifications, 3000);

// Initial fetch
fetchNotifications();

function playNotificationSound() {
    try {
        const audio = new Audio('data:audio/wav;base64,UklGRiYAAABXQVZFZm10IBAAAAABAAEAQB8AAAB9AAACABAAZGF0YQIAAAAAAA==');
        audio.play().catch(e => {});
    } catch(e) {}
}
</script>
</body>
</html>
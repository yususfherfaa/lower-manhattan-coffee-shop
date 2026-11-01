<?php
$current_uri = uri_string();
$namaWarkop = setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop';
$logoWarkop = setting('logo') ?? 'uploads/manhattan-coffee-shop-logo.png';
$namaKasir  = session()->get('nama_kasir') ?? 'Kasir';
?>
<link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
<style>
.kasir-navbar {
    background: var(--espresso);
    color: var(--cream);
    padding: 12px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 0;
    z-index: 1000;
}
.kasir-navbar .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--cream);
}
.kasir-navbar .brand img {
    width: 82px;
    height: 50px;
    border-radius: 7px;
    object-fit: contain;
    border: 2px solid var(--nude);
    background: #fff;
}
.kasir-navbar .brand-name {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}
.kasir-navbar .brand-badge {
    background: rgba(196, 127, 94, 0.25);
    color: var(--rose-nude);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: var(--r-pill);
    font-weight: 500;
    margin-left: 4px;
}
.kasir-navbar .nav-links {
    display: flex;
    gap: 6px;
    align-items: center;
}
.kasir-navbar .nav-link {
    color: var(--cream);
    text-decoration: none;
    font-size: var(--fs-sm);
    font-weight: 500;
    padding: 8px 14px;
    border-radius: var(--r-pill);
    transition: all var(--dur-fast) var(--ease);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.kasir-navbar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.kasir-navbar .nav-link.active {
    background: var(--terracotta);
    color: #fff;
    box-shadow: var(--shadow-sm);
}
.kasir-navbar .user-greeting {
    color: var(--nude);
    font-size: var(--fs-sm);
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.kasir-navbar .notification-bell {
    position: relative;
    cursor: pointer;
    font-size: 20px;
    padding: 6px 10px;
    border-radius: var(--r-pill);
    transition: all var(--dur-fast) var(--ease);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--cream);
}
.kasir-navbar .notification-bell:hover {
    background: rgba(255, 255, 255, 0.1);
}
.kasir-navbar .notification-count {
    position: absolute;
    top: 0;
    right: 2px;
    background: var(--brick);
    color: #fff;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    border: 2px solid var(--espresso);
    animation: notifPulse 2s infinite ease-in-out;
}
@keyframes notifPulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(184, 92, 79, 0.7); }
    50% { transform: scale(1.15); box-shadow: 0 0 0 6px rgba(184, 92, 79, 0); }
}
.kasir-navbar .btn-logout-pill {
    background: var(--brick);
    color: #fff;
    padding: 8px 18px;
    border-radius: var(--r-pill);
    text-decoration: none;
    font-size: var(--fs-sm);
    font-weight: 600;
    transition: all var(--dur-fast) var(--ease);
    box-shadow: 0 2px 8px rgba(184, 92, 79, 0.35);
    margin-left: 6px;
}
.kasir-navbar .btn-logout-pill:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(184, 92, 79, 0.5);
    color: #fff;
}
.nav-mobile-toggle-kasir {
    display: none;
    background: transparent;
    border: none;
    color: var(--cream);
    font-size: 24px;
    cursor: pointer;
    padding: 4px;
}
@media (max-width: 960px) {
    .nav-mobile-toggle-kasir { display: block; }
    .kasir-navbar { flex-wrap: wrap; }
    .kasir-navbar .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: stretch;
        padding-top: 14px;
        gap: 8px;
    }
    .kasir-navbar .nav-links.open { display: flex; }
    .kasir-navbar .nav-link { border-radius: var(--r-sm); padding: 10px 14px; }
    .kasir-navbar .btn-logout-pill { text-align: center; margin-left: 0; }
}
</style>

<nav class="kasir-navbar">
    <a href="<?= base_url('kasir/dashboard') ?>" class="brand">
        <img src="<?= base_url($logoWarkop) ?>" alt="Lower Manhattan Coffee Shop" onerror="this.style.display='none'">
        <span class="brand-name"><?= esc($namaWarkop) ?></span>
        <span class="brand-badge">Kasir</span>
    </a>

    <button class="nav-mobile-toggle-kasir" onclick="toggleKasirNav()" aria-label="Toggle Navigation">☰</button>

    <div class="nav-links" id="kasirNavLinks">
        <span class="user-greeting">
            👋 Halo, <?= esc($namaKasir) ?>
        </span>

        <a href="<?= base_url('kasir/dashboard') ?>"
           class="nav-link <?= strpos($current_uri, 'kasir/dashboard') !== false ? 'active' : '' ?>">
            📊 Dashboard
        </a>

        <a href="<?= base_url('kasir/pesanan') ?>"
           class="nav-link <?= strpos($current_uri, 'kasir/pesanan') !== false ? 'active' : '' ?>">
            📋 Kelola Pesanan
        </a>

        <a href="<?= base_url('laporan') ?>"
           class="nav-link <?= strpos($current_uri, 'laporan') !== false ? 'active' : '' ?>">
            📈 Laporan Penjualan
        </a>

        <a href="<?= base_url('kasir/menu') ?>"
           class="nav-link <?= strpos($current_uri, 'kasir/menu') !== false ? 'active' : '' ?>">
            ☕ Manajemen Menu
        </a>

        <div class="notification-bell" onclick="toggleNotificationPanel()" title="Notifikasi Pesanan">
            🔔
            <div class="notification-count" id="notif-count" style="display: none;">0</div>
        </div>

        <a href="<?= base_url('logout') ?>" class="btn-logout-pill">
            Logout
        </a>
    </div>
</nav>

<script>
function toggleKasirNav() {
    var el = document.getElementById('kasirNavLinks');
    el.classList.toggle('open');
}

let idleTimeKasir = 0;
function resetIdleKasir() { idleTimeKasir = 0; }
document.addEventListener('mousemove', resetIdleKasir);
document.addEventListener('keypress', resetIdleKasir);
document.addEventListener('click', resetIdleKasir);
document.addEventListener('scroll', resetIdleKasir);
document.addEventListener('touchstart', resetIdleKasir);

setInterval(function(){
    idleTimeKasir++;
    if(idleTimeKasir >= 10){
        window.location.href = "<?= base_url('kasir/dashboard') ?>";
    }
}, 60000);
</script>
<?php
$current_uri = uri_string();
$namaWarkop = setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop';
$logoWarkop = setting('logo') ?? 'uploads/manhattan-coffee-shop-logo.png';
?>
<link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
<style>
.admin-navbar {
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
.admin-navbar .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--cream);
}
.admin-navbar .brand img {
    width: 82px;
    height: 50px;
    border-radius: 7px;
    object-fit: contain;
    border: 2px solid var(--nude);
    background: #fff;
}
.admin-navbar .brand-name {
    font-family: var(--font-heading);
    font-size: 1.15rem;
    font-weight: 600;
    letter-spacing: -0.01em;
}
.admin-navbar .brand-badge {
    background: rgba(226, 205, 177, 0.2);
    color: var(--beige);
    font-size: 11px;
    padding: 2px 8px;
    border-radius: var(--r-pill);
    font-weight: 500;
    margin-left: 4px;
}
.admin-navbar .nav-links {
    display: flex;
    gap: 6px;
    align-items: center;
}
.admin-navbar .nav-link {
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
.admin-navbar .nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.admin-navbar .nav-link.active {
    background: var(--terracotta);
    color: #fff;
    box-shadow: var(--shadow-sm);
}
.admin-navbar .user-greeting {
    color: var(--nude);
    font-size: var(--fs-sm);
    padding: 0 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.admin-navbar .btn-logout-pill {
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
.admin-navbar .btn-logout-pill:hover {
    filter: brightness(1.1);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(184, 92, 79, 0.5);
    color: #fff;
}
.nav-mobile-toggle {
    display: none;
    background: transparent;
    border: none;
    color: var(--cream);
    font-size: 24px;
    cursor: pointer;
    padding: 4px;
}
@media (max-width: 900px) {
    .nav-mobile-toggle { display: block; }
    .admin-navbar { flex-wrap: wrap; }
    .admin-navbar .nav-links {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: stretch;
        padding-top: 14px;
        gap: 8px;
    }
    .admin-navbar .nav-links.open { display: flex; }
    .admin-navbar .nav-link { border-radius: var(--r-sm); padding: 10px 14px; }
    .admin-navbar .btn-logout-pill { text-align: center; margin-left: 0; }
}
</style>

<nav class="admin-navbar">
    <a href="<?= base_url('admin/dashboard') ?>" class="brand">
        <img src="<?= base_url($logoWarkop) ?>" alt="Lower Manhattan Coffee Shop" onerror="this.style.display='none'">
        <span class="brand-name"><?= esc($namaWarkop) ?></span>
        <span class="brand-badge">Admin</span>
    </a>

    <button class="nav-mobile-toggle" onclick="toggleAdminNav()" aria-label="Toggle Navigation">☰</button>

    <div class="nav-links" id="adminNavLinks">
        <span class="user-greeting">
            👑 <?= esc(session()->get('nama_kasir') ?? 'Administrator') ?>
        </span>

        <a href="<?= base_url('admin/dashboard') ?>"
           class="nav-link <?= strpos($current_uri, 'admin/dashboard') !== false ? 'active' : '' ?>">
            📊 Dashboard
        </a>

        <a href="<?= base_url('admin/kasir') ?>"
           class="nav-link <?= strpos($current_uri, 'admin/kasir') !== false ? 'active' : '' ?>">
            👥 Kelola Kasir
        </a>

        <a href="<?= base_url('laporan') ?>"
           class="nav-link <?= (strpos($current_uri, 'laporan') !== false && strpos($current_uri, 'laporan-stok') === false) ? 'active' : '' ?>">
            📈 Laporan Penjualan
        </a>

        <a href="<?= base_url('laporan-stok') ?>"
           class="nav-link <?= strpos($current_uri, 'laporan-stok') !== false ? 'active' : '' ?>">
            📦 Laporan Stok
        </a>

        <a href="<?= base_url('admin/recommendation-acceptance') ?>"
           class="nav-link <?= strpos($current_uri, 'recommendation-acceptance') !== false ? 'active' : '' ?>">
            Tingkat Penerimaan Rekomendasi
        </a>

        <a href="<?= base_url('admin/analitik-penjualan') ?>"
           class="nav-link <?= strpos($current_uri, 'analitik-penjualan') !== false ? 'active' : '' ?>">
            ☕ Analitik Penjualan
        </a>

        <a href="<?= base_url('admin/evaluasi-model') ?>"
           class="nav-link <?= strpos($current_uri, 'evaluasi-model') !== false ? 'active' : '' ?>">
            🤖 Evaluasi Model
        </a>

        <a href="<?= base_url('admin/settings') ?>"
           class="nav-link <?= strpos($current_uri, 'admin/settings') !== false ? 'active' : '' ?>">
            ⚙️ Pengaturan
        </a>

        <a href="<?= base_url('logout') ?>" class="btn-logout-pill">
            Logout
        </a>
    </div>
</nav>

<script>
function toggleAdminNav() {
    var el = document.getElementById('adminNavLinks');
    el.classList.toggle('open');
}

let idleTimeAdmin = 0;
function resetIdleAdmin() { idleTimeAdmin = 0; }
document.addEventListener('mousemove', resetIdleAdmin);
document.addEventListener('keypress', resetIdleAdmin);
document.addEventListener('click', resetIdleAdmin);
document.addEventListener('scroll', resetIdleAdmin);
document.addEventListener('touchstart', resetIdleAdmin);

// Idle reload prevention/safety: 5 menit idle
setInterval(function(){
    idleTimeAdmin++;
    if(idleTimeAdmin >= 5){
        window.location.href = "<?= base_url('admin/dashboard') ?>";
    }
}, 60000);
</script>

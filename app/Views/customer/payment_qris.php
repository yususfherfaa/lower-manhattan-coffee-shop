<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pembayaran QRIS — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
<link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
<style>
body {
    background: #e9e7e4;
    color: #1d1b1a;
    font-family: Arial, sans-serif;
}
.container-customer {
    max-width: 620px;
    margin: 0 auto;
    padding: 18px 18px 28px;
}
.qris-card {
    background: #f3f0ee;
    padding: 18px 18px 20px;
    border-radius: 28px;
    text-align: center;
    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
    border: 1px solid #d9d0ca;
    max-width: 520px;
    margin: 0 auto;
}
.qris-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 18px;
}
.qris-header .close-btn {
    font-size: 2rem;
    line-height: 1;
    color: #1c1a1a;
    opacity: 0.8;
}
.qris-header h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.qris-top-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.qris-top-actions .action-pill {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #1d1d1d;
    display: grid;
    place-items: center;
    color: #fff;
    font-weight: 700;
    font-size: 0.8rem;
}
.merchant-box {
    background: rgba(255,255,255,0.38);
    border: 1px solid #d8d1ce;
    border-radius: 18px;
    padding: 12px 16px;
    margin: 6px 0 22px;
}
.merchant-box .name {
    font-weight: 700;
    font-size: clamp(1.05rem, 2vw, 1.55rem);
    line-height: 1.25;
    letter-spacing: 0.01em;
    margin-bottom: 6px;
}
.merchant-box .address {
    font-size: 0.72rem;
    color: #3a3635;
    opacity: 0.95;
    line-height: 1.5;
}
.merchant-box .nmid {
    margin-top: 10px;
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.qris-frame {
    background: #fff;
    border: 2px solid #101010;
    border-radius: 18px;
    padding: 18px 18px 12px;
    width: min(76%, 400px);
    margin: 0 auto 18px;
    position: relative;
    box-shadow: inset 0 0 0 1px rgba(0,0,0,0.08);
}
.qris-frame::before,
.qris-frame::after {
    content: "";
    position: absolute;
    background: #f6474c;
    border-radius: 0 0 18px 0;
    width: 36px;
    height: 26px;
    bottom: -2px;
    right: -2px;
}
.qris-frame::before {
    left: -2px;
    border-radius: 0 0 0 18px;
    right: auto;
}
.qris-frame .brand {
    display: inline-block;
    font-size: 2.1rem;
    font-weight: 900;
    letter-spacing: -0.08em;
    margin-bottom: 12px;
    color: #0f0f0f;
}
.qris-frame .brand .q {
    color: #1e1e1e;
}
.qris-frame .brand .r {
    color: #f6474c;
}
.qris-frame .brand .i {
    color: #1e1e1e;
}
.qris-frame .brand .s {
    color: #1e1e1e;
}
.qris-frame img {
    width: 100%;
    display: block;
    border-radius: 6px;
    background: #fff;
}
.qris-cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: rgba(255,255,255,0.38);
    border: 1px solid #d8d1ce;
    border-radius: 999px;
    padding: 10px 18px;
    margin-top: 6px;
    font-weight: 600;
    color: #1f1b1a;
}
.qris-cta .refresh {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #1f1b1a;
    position: relative;
    display: inline-block;
}
.qris-cta .refresh::before {
    content: "";
    position: absolute;
    width: 7px;
    height: 7px;
    border-top: 2px solid #1f1b1a;
    border-right: 2px solid #1f1b1a;
    transform: rotate(45deg);
    top: 2px;
    right: 2px;
}
.bottom-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    max-width: 520px;
    margin: 24px auto 0;
}
.bottom-actions .btn {
    min-height: 54px;
    border-radius: 20px;
    border: 2px solid #3a8b52;
    background: rgba(255,255,255,0.18);
    color: #1a6f3a;
    font-size: 1.05rem;
    font-weight: 700;
    display: grid;
    place-items: center;
    text-decoration: none;
}
.bottom-actions .btn.primary {
    border-color: #3a8b52;
    color: #1a6f3a;
}
.qris-amount {
    font-size: 2.2rem;
    color: var(--terracotta);
    font-family: var(--font-heading);
    font-weight: 700;
    margin: var(--sp-2) 0 var(--sp-5) 0;
}
.qris-img-wrap {
    background: #fff;
    padding: 16px;
    border-radius: var(--r-lg);
    display: inline-block;
    border: 1px solid var(--sand);
    box-shadow: var(--shadow-sm);
    margin: var(--sp-3) 0 var(--sp-5) 0;
}
.qris-img-wrap img {
    width: 260px;
    max-width: 100%;
    display: block;
}
.qris-notice {
    background: var(--amber-light);
    color: var(--espresso);
    border: 1px solid var(--amber);
    padding: var(--sp-4);
    border-radius: var(--r-md);
    font-size: var(--fs-sm);
    line-height: 1.5;
    margin-top: var(--sp-5);
}
@media (max-width: 480px) {
    .container-customer {
        padding-left: 12px;
        padding-right: 12px;
    }
    .qris-card {
        padding: 14px 12px 16px;
    }
    .qris-frame {
        width: min(88%, 360px);
    }
    .bottom-actions {
        grid-template-columns: 1fr;
    }
}
.qris-card {
    max-width: 480px;
    padding: clamp(16px, 4vw, 26px);
    border-radius: 24px;
}
.qris-header {
    justify-content: center;
    position: relative;
    margin-bottom: 18px;
}
.qris-header .close-btn {
    position: absolute;
    left: 0;
    text-decoration: none;
}
.qris-header .close-btn a {
    color: inherit;
    text-decoration: none;
}
.merchant-box {
    padding: 16px;
    margin-bottom: 18px;
}
.merchant-box .name {
    overflow-wrap: anywhere;
}
.merchant-box .address {
    font-size: 0.82rem;
}
.qris-frame {
    width: min(100%, 360px);
    box-sizing: border-box;
    padding: 14px;
    border: 1px solid #e4dedb;
    border-radius: 20px;
}
.qris-frame::before,
.qris-frame::after {
    z-index: 0;
    pointer-events: none;
}
.qris-frame .brand,
.qris-frame img {
    position: relative;
    z-index: 1;
}
.qris-frame img {
    aspect-ratio: 1;
    object-fit: contain;
}
.qris-amount {
    margin: 0 0 18px;
    font-size: clamp(1.5rem, 6vw, 2rem);
    color: #1d1b1a;
    font-family: Arial, sans-serif;
}
.qris-amount small {
    display: block;
    margin-bottom: 5px;
    color: #716a66;
    font-size: 0.8rem;
    font-weight: 600;
}
.qris-instructions {
    margin: 16px 0 0;
    color: #514b48;
    font-size: 0.88rem;
    line-height: 1.55;
}
.qris-notice {
    margin-top: 16px;
    background: #fff8e9;
    border-color: #e6c980;
    text-align: left;
}
.qris-order-meta {
    margin-top: 14px;
    color: #716a66;
    font-size: 0.78rem;
}
</style>
</head>
<body>

<div class="container-customer reveal">

    <div class="qris-card">

        <div class="qris-header">
            <div class="close-btn"><a href="<?= base_url('cart/'.$order['id_meja']) ?>" aria-label="Kembali ke keranjang">×</a></div>
            <h2>QRIS</h2>
        </div>

        <div class="merchant-box">
            <div class="name"><?= esc($merchant_name) ?>, <?= esc(ucwords(strtolower($merchant_address))) ?></div>
            <div class="address">Jl. Kaliabang No.8, Perwira, Bekasi Utara, Kota Bekasi, Jawa Barat</div>
            <div class="nmid">NMID: ID102435780413</div>
        </div>

        <div class="qris-amount">
            <small>Total pembayaran</small>
            Rp <?= number_format($order['total'], 0, ',', '.') ?>
        </div>

        <div class="qris-frame">
            <div class="brand"><span class="q">Q</span><span class="r">R</span><span class="i">I</span><span class="s">S</span></div>
            <img src="<?= base_url($qris) ?>" alt="QRIS Barcode" onerror="this.src='<?= base_url('uploads/qris-placeholder.svg') ?>'">
        </div>

        <p class="qris-instructions">
            Scan kode QRIS di atas dan bayarkan sesuai total pesanan. Setelah membayar, silakan tunggu kasir memverifikasi pembayaran.
        </p>

        <div class="qris-notice">
            <b>Menunggu konfirmasi kasir</b><br>
            Halaman akan otomatis beralih setelah pembayaran Anda ditandai lunas.
        </div>

        <div class="qris-order-meta">
            Pesanan #<?= esc($order['id_order']) ?> · Meja <?= esc($order['id_meja']) ?>
        </div>
    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
setInterval(function(){
    fetch("<?= base_url('cek-status/'.$order['id_order']) ?>?token=<?= esc($order_token, 'url') ?>")
    .then(response => response.json())
    .then(data => {
        if(data.status == 'lunas'){
            window.location.href = "<?= base_url('order-success/'.$order['id_order'].'/'.$order['id_meja']) ?>?token=<?= esc($order_token, 'url') ?>";
        }
    })
    .catch(err => {});
}, 3000);
</script>

</body>
</html>

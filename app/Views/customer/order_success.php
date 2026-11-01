<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan Berhasil — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
        .success-page-wrap {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--sp-6) var(--sp-4);
        }
        .success-box-beige {
            background: var(--ivory);
            padding: var(--sp-8);
            border-radius: var(--r-xl);
            box-shadow: var(--shadow-xl);
            text-align: center;
            max-width: 460px;
            width: 100%;
            border: 1px solid var(--sand);
        }
        .success-icon-badge {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--sage-light);
            border: 2px solid var(--sage);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin: 0 auto var(--sp-4) auto;
            color: var(--sage);
            animation: bounce-in .5s cubic-bezier(.22,1,.36,1);
        }
        .order-info-pill {
            background: var(--cream);
            border: 1px solid var(--sand);
            border-radius: var(--r-md);
            padding: var(--sp-4);
            margin: var(--sp-5) 0;
            font-size: var(--fs-sm);
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="success-page-wrap reveal">

    <div class="success-box-beige">

        <div class="success-icon-badge">✓</div>

        <h1 style="font-size:var(--fs-h2); margin-bottom:var(--sp-2);">Pesanan Berhasil!</h1>

        <p style="color:var(--mocha); font-size:var(--fs-body); margin:0;">
            Pesanan untuk <strong>🍽️ <?= esc($order['id_meja']) ?></strong> berhasil dicatat.
        </p>

        <div class="order-info-pill">
            <div>Order ID: <strong>#<?= esc($order['id_order']) ?></strong></div>
            <div style="margin-top:4px;">
                Status: <span class="badge" style="background:var(--sage); color:#fff; padding:2px 8px; border-radius:var(--r-pill); font-size:11px;">
                    <?= esc(ucfirst(str_replace('_', ' ', $order['status']))) ?>
                </span>
            </div>
            <div style="margin-top:8px; color:var(--taupe); font-size:var(--fs-xs);">
                <?php if (($order['metode_pembayaran'] ?? 'cash') == 'cash'): ?>
                    Silakan lakukan pembayaran langsung di meja kasir.
                <?php else: ?>
                    Pembayaran QRIS Anda telah diverifikasi oleh kasir.
                <?php endif; ?>
            </div>
        </div>

        <div style="text-align:left; margin:var(--sp-4) 0; padding:var(--sp-4); border:1px solid var(--sand); border-radius:var(--r-md); background:#fff;">
            <strong>Rincian pesanan</strong>
            <?php foreach ($items as $item): ?>
            <div style="display:flex;justify-content:space-between;gap:12px;margin-top:10px;font-size:var(--fs-sm);">
                <span>
                    <?php
                        $ukuranLabel = catalog_product_size_label($item);
                        $namaMenu = catalog_product_display_name($item);
                    ?>
                    <?= esc($namaMenu) ?>
                    <?php if ($ukuranLabel !== null): ?>
                        <small style="display:block;color:var(--taupe);">Ukuran: <?= esc($ukuranLabel) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($item['opsi'])): ?>
                        <small style="display:block;color:var(--terracotta);">Varian: <?= esc($item['opsi']) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($item['catatan'])): ?>
                        <small style="display:block;color:var(--espresso);"><strong>Catatan:</strong> <?= esc($item['catatan']) ?></small>
                    <?php endif; ?>
                    <?php if (!empty($item['suhu'])): ?>
                        <small style="display:block;color:var(--taupe);"><?= $item['suhu'] === 'panas' ? 'Panas' : 'Es' ?></small>
                    <?php endif; ?>
                    <small style="display:block;color:var(--taupe);"><?= (int) $item['qty'] ?> × Rp <?= number_format($item['harga'], 0, ',', '.') ?></small>
                </span>
                <strong>Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></strong>
            </div>
            <?php endforeach; ?>
            <div style="display:flex;justify-content:space-between;border-top:1px solid var(--sand);margin-top:12px;padding-top:10px;">
                <strong>Total</strong>
                <strong>Rp <?= number_format($order['total'], 0, ',', '.') ?></strong>
            </div>
        </div>

        <p style="color:var(--taupe); font-size:var(--fs-xs);">
            Pesanan Anda segera diracik. Silakan bersantai menikmati suasana coffee shop! ☕
        </p>

        <a href="<?= base_url('/order/'.$id_meja) ?>" class="btn btn-neutral" style="width:100%; display:block; box-sizing:border-box; margin-top:var(--sp-4);">
            ← Kembali ke Menu Meja
        </a>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
// Confetti effect
if (window.CoffeeShopConfetti) {
    window.CoffeeShopConfetti();
}

// Polling status jika sedang menunggu pembayaran
setInterval(function(){
    fetch("<?= base_url('cek-status/'.$order['id_order']) ?>?token=<?= esc($order_token, 'url') ?>")
    .then(response => response.json())
    .then(data => {
        if(data.status == 'lunas'){
            window.location.href = "<?= base_url('order-success/'.$order['id_order'].'/'.$id_meja) ?>?token=<?= esc($order_token, 'url') ?>";
        }
    })
    .catch(err => {});
}, 3000);
</script>

</body>
</html>

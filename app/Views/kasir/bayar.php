<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Pesanan #<?= esc($order['id_order']) ?> — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .pay-card {
        max-width: 800px;
        margin: var(--sp-6) auto;
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-xl);
        padding: var(--sp-8);
        box-shadow: var(--shadow-lg);
    }
    .table-pay {
        width: 100%;
        border-collapse: collapse;
        margin-top: var(--sp-4);
        border-radius: var(--r-md);
        overflow: hidden;
    }
    .table-pay th {
        background: var(--espresso);
        color: var(--cream);
        padding: 12px 14px;
        font-size: var(--fs-sm);
        text-align: left;
    }
    .table-pay td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--sand);
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .total-box-pay {
        margin: var(--sp-5) 0;
        padding: var(--sp-5);
        background: var(--cream);
        border-radius: var(--r-md);
        border: 1px solid var(--sand);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .total-box-pay .amount {
        font-family: var(--font-heading);
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--terracotta);
    }
    .input-pay {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid var(--beige);
        background: #fff;
        border-radius: var(--r-input);
        font-size: 1.25rem;
        font-family: var(--font-body);
        font-weight: 700;
        color: var(--espresso);
        box-sizing: border-box;
        transition: all var(--dur-fast) var(--ease);
    }
    .input-pay:focus {
        border-color: var(--terracotta);
        outline: none;
        box-shadow: 0 0 0 4px rgba(196, 127, 94, 0.18);
    }
    .btn-pay-submit {
        margin-top: var(--sp-4);
        width: 100%;
        padding: 14px;
        background: var(--grad-btn);
        color: white;
        border: none;
        border-radius: var(--r-btn);
        cursor: pointer;
        font-size: var(--fs-body);
        font-weight: 700;
        font-family: var(--font-body);
        box-shadow: var(--shadow-btn);
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-pay-submit:hover {
        box-shadow: var(--shadow-btn-hover);
        transform: translateY(-2px);
    }
    .btn-verify-qris {
        margin-top: var(--sp-4);
        width: 100%;
        padding: 14px;
        background: var(--sage);
        color: white;
        border: none;
        border-radius: var(--r-btn);
        cursor: pointer;
        font-size: var(--fs-body);
        font-weight: 700;
        font-family: var(--font-body);
        box-shadow: 0 4px 14px rgba(143, 166, 138, 0.4);
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-verify-qris:hover {
        filter: brightness(1.1);
        transform: translateY(-2px);
    }
    .alert-error-beige {
        background: var(--brick-light);
        color: var(--brick);
        border: 1px solid var(--brick);
        padding: var(--sp-3) var(--sp-4);
        border-radius: var(--r-md);
        margin-bottom: var(--sp-4);
        font-weight: 600;
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_kasir') ?>

<div class="container reveal">

    <div class="pay-card">

        <div style="margin-bottom:var(--sp-4);">
            <a href="<?= base_url('/kasir/pesanan') ?>" style="font-size:var(--fs-xs); color:var(--taupe); text-decoration:none;">
                ← Kembali ke Daftar Pesanan
            </a>
            <h1 style="font-size:var(--fs-h2); margin:8px 0 4px 0;">Kasir Pembayaran</h1>
            <p class="text-muted" style="margin:0;">Proses penyelesaian transaksi pesanan pelanggan.</p>
        </div>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert-error-beige">
                ⚠ <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; background:var(--cream); padding:12px 16px; border-radius:var(--r-md); border:1px solid var(--sand); margin-bottom:var(--sp-4);">
            <div><strong>Order ID:</strong> #<?= esc($order['id_order']) ?></div>
            <div><strong>Meja:</strong> 🍽️ <?= esc($order['id_meja']) ?></div>
            <div><strong>Pelanggan:</strong> 👤 <?= esc($order['nama_pelanggan']) ?></div>
        </div>

        <table class="table-pay">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th style="text-align:right;">Harga</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $it): ?>
                <tr>
                    <td>
                        <?php
                            $ukuranLabel = catalog_product_size_label($it);
                            $namaMenu = catalog_product_display_name($it);
                        ?>
                        <strong><?= esc($namaMenu) ?></strong>
                        <?php if ($ukuranLabel !== null): ?>
                            <small style="display:block;color:var(--taupe);">Ukuran: <?= esc($ukuranLabel) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($it['opsi'])): ?>
                            <div style="margin-top:3px;color:var(--terracotta);font-size:var(--fs-xs);">Varian: <?= esc($it['opsi']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($it['catatan'])): ?>
                            <div style="margin-top:3px;color:var(--espresso);font-size:var(--fs-xs);"><strong>Catatan:</strong> <?= esc($it['catatan']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($it['suhu'])): ?>
                            <div style="margin-top:3px;color:var(--taupe);font-size:var(--fs-xs);"><?= $it['suhu'] === 'panas' ? 'Panas' : 'Es' ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">Rp <?= number_format($it['harga'], 0, ',', '.') ?></td>
                    <td style="text-align:center;"><?= esc($it['qty']) ?></td>
                    <td style="text-align:right; font-weight:600;">Rp <?= number_format($it['subtotal'], 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-box-pay">
            <div>
                <span class="text-muted text-xs" style="text-transform:uppercase; letter-spacing:0.05em; display:block;">Metode Dipilih</span>
                <strong>💳 <?= strtoupper(esc($order['metode_pembayaran'])) ?></strong>
            </div>
            <div style="text-align:right;">
                <span class="text-muted text-xs" style="text-transform:uppercase; letter-spacing:0.05em; display:block;">Total Tagihan</span>
                <span class="amount">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
        </div>

        <?php if($order['metode_pembayaran'] == 'cash'): ?>

            <form method="post" action="<?= base_url('/kasir/bayar/process/'.$order['id_order']) ?>">
                <div style="margin-bottom:var(--sp-3);">
                    <label style="display:block; margin-bottom:6px; font-weight:600; color:var(--espresso);">
                        Nominal Tunai Diterima (Rp)
                    </label>
                    <input type="number"
                           name="uang_dibayar"
                           id="uang_dibayar"
                           class="input-pay"
                           min="<?= (int) $order['total'] ?>"
                           placeholder="Contoh: <?= (int) $order['total'] ?>"
                           required
                           autofocus>
                </div>

                <div id="kembalianPreview" style="display:none; padding:12px 16px; background:var(--sage-light); color:var(--sage); border-radius:var(--r-md); font-weight:700; font-size:var(--fs-body); margin-bottom:var(--sp-3);">
                    Kembalian: <span id="kembalianVal">Rp 0</span>
                </div>

                <button type="submit" class="btn-pay-submit">
                    ✓ Selesaikan Pembayaran Tunai
                </button>
            </form>

            <script>
            document.getElementById('uang_dibayar').addEventListener('input', function() {
                var total = <?= (int)$order['total'] ?>;
                var val = parseInt(this.value) || 0;
                var preview = document.getElementById('kembalianPreview');
                var valSpan = document.getElementById('kembalianVal');
                if (val >= total) {
                    var change = val - total;
                    valSpan.textContent = 'Rp ' + change.toLocaleString('id-ID');
                    preview.style.display = 'block';
                } else {
                    preview.style.display = 'none';
                }
            });
            </script>

        <?php else: ?>

            <form method="post" action="<?= base_url('/kasir/bayar/process/'.$order['id_order']) ?>">
                <input type="hidden" name="uang_dibayar" value="<?= (int) $order['total'] ?>">

                <div style="padding:var(--sp-5); background:var(--sage-light); border-radius:var(--r-md); color:var(--espresso); border:1px solid var(--sage);">
                    <div style="font-weight:700; color:var(--sage); margin-bottom:4px; font-size:var(--fs-body);">
                        📲 Pembayaran QRIS Pelanggan
                    </div>
                    Pelanggan telah memilih pembayaran via QRIS. Mohon periksa mutasi saldo di aplikasi rekening/QRIS warkop sebelum menekan tombol verifikasi.
                </div>

                <button type="submit" class="btn-verify-qris">
                    ✓ Verifikasi & Lunaskan Pembayaran QRIS
                </button>
            </form>

        <?php endif; ?>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>

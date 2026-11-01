<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Keranjang Pesanan — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
<link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
<style>
.container-customer {
    max-width: 1100px;
}
.cart-card {
    background: var(--ivory);
    border: 1px solid var(--sand);
    border-radius: var(--r-xl);
    padding: var(--sp-6);
    box-shadow: var(--shadow-md);
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    margin: var(--sp-6) auto;
}
.table-cart {
    width: 100%;
    border-collapse: collapse;
    margin-top: var(--sp-4);
}
.table-cart-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table-cart {
    min-width: 620px;
}
.table-cart th {
    background: var(--espresso);
    color: var(--cream);
    padding: 12px 14px;
    font-size: var(--fs-sm);
    text-align: left;
}
.table-cart td {
    padding: 12px 14px;
    border-bottom: 1px solid var(--sand);
    color: var(--espresso);
    font-size: var(--fs-sm);
}
@media (max-width: 640px) {
    .table-cart-wrap {
        overflow: visible;
    }
    .table-cart,
    .table-cart tbody {
        display: block;
        width: 100%;
        min-width: 0;
    }
    .table-cart thead {
        display: none;
    }
    .table-cart tr {
        display: block;
        margin-bottom: var(--sp-3);
        padding: 8px 14px;
        background: #fff;
        border: 1px solid var(--sand);
        border-radius: var(--r-md);
    }
    .table-cart td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
        text-align: right !important;
    }
    .table-cart td::before {
        content: attr(data-label);
        color: var(--taupe);
        font-size: var(--fs-xs);
        font-weight: 600;
        text-align: left;
    }
    .table-cart td:first-child {
        display: block;
        text-align: left !important;
        padding-top: 2px;
    }
    .table-cart td:first-child::before {
        content: none;
    }
    .table-cart td:last-child {
        border-bottom: 0;
    }
}
.qty-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: var(--sand);
    color: var(--espresso);
    text-decoration: none;
    font-weight: 700;
    transition: all var(--dur-fast) var(--ease);
}
.qty-btn:hover {
    background: var(--beige);
    color: var(--espresso);
}
.btn-del-cart {
    background: var(--brick-light);
    color: var(--brick);
    border: 1px solid var(--brick);
    padding: 4px 10px;
    border-radius: var(--r-sm);
    text-decoration: none;
    font-size: var(--fs-xs);
    font-weight: 600;
}
.btn-del-cart:hover {
    background: var(--brick);
    color: #fff;
}
.cart-total-box {
    margin-top: var(--sp-5);
    padding: var(--sp-4) var(--sp-5);
    background: var(--cream);
    border-radius: var(--r-md);
    border: 1px solid var(--sand);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.3rem;
    font-family: var(--font-heading);
    font-weight: 700;
}
.checkout-input {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid var(--beige);
    border-radius: var(--r-input);
    font-family: var(--font-body);
    font-size: var(--fs-body);
    color: var(--espresso);
    background: var(--ivory);
    box-sizing: border-box;
    margin-bottom: var(--sp-4);
}
.checkout-input:focus {
    border-color: var(--terracotta);
    outline: none;
    box-shadow: 0 0 0 3px rgba(196, 127, 94, 0.18);
}
.item-note-form {
    margin-top: 10px;
}
.item-note-input {
    display: block;
    width: 100%;
    min-height: 56px;
    box-sizing: border-box;
    padding: 8px 10px;
    border: 1px solid var(--beige);
    border-radius: var(--r-input);
    font: inherit;
    resize: vertical;
}
.payment-method-box {
    margin-bottom: var(--sp-5);
    padding: var(--sp-4) var(--sp-5);
    background: var(--ivory);
    border: 1.5px solid var(--beige);
    border-radius: var(--r-md);
}
.payment-method-box label {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 8px 0;
    font-weight: 600;
    cursor: pointer;
}
.btn-checkout-beige {
    width: 100%;
    padding: 14px;
    background: var(--grad-btn);
    color: white;
    border: none;
    border-radius: var(--r-btn);
    font-size: var(--fs-body);
    font-weight: 700;
    cursor: pointer;
    font-family: var(--font-body);
    box-shadow: var(--shadow-btn);
    transition: all var(--dur-fast) var(--ease);
}
.btn-checkout-beige:hover {
    box-shadow: var(--shadow-btn-hover);
    transform: translateY(-2px);
}
.btn-add-more {
    display: block;
    text-align: center;
    padding: 12px;
    background: var(--sand);
    color: var(--espresso);
    border-radius: var(--r-btn);
    text-decoration: none;
    font-weight: 600;
    margin-top: var(--sp-3);
    border: 1px solid var(--beige);
    transition: all var(--dur-fast) var(--ease);
}
.btn-add-more:hover {
    background: var(--beige);
    color: var(--espresso);
}
</style>
</head>
<body>

<div class="container-customer reveal">

    <div class="cart-card">

        <h2 style="margin-top:0;">🛒 Keranjang Pesanan</h2>
        <?php if (session()->getFlashdata('error')): ?>
        <div role="alert" style="margin:12px 0;padding:12px;border-radius:8px;background:var(--brick-light);color:var(--brick);">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
        <div role="status" style="margin:12px 0;padding:12px;border-radius:8px;background:var(--sage-light);color:var(--espresso);">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
        <?php endif; ?>
        <div style="text-align:center; margin-bottom:var(--sp-4);">
            <span class="badge" style="background:var(--terracotta); color:#fff; border-radius:var(--r-pill); padding:4px 12px; font-size:var(--fs-xs);">
                🍽️ Meja: <?= esc($id_meja) ?>
            </span>
        </div>

        <?php if(count($items) > 0): ?>

        <form id="checkoutForm" method="post" action="<?= base_url('/checkout/'.$id_meja) ?>">
        <div class="table-cart-wrap">
        <table class="table-cart">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th style="text-align:right;">Harga</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Subtotal</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td data-label="Menu">
                        <strong><?= esc(catalog_product_display_name($item)) ?></strong>
                        <?php $ukuranLabel = catalog_product_size_label($item); ?>
                        <?php if ($ukuranLabel !== null): ?>
                            <small style="display:block;color:var(--taupe);">Ukuran: <?= esc($ukuranLabel) ?></small>
                        <?php endif; ?>
                        <?php if (!empty($item['suhu'])): ?>
                            <div style="font-size:var(--fs-xs); color:var(--taupe); margin-top:3px;">
                                <?= $item['suhu'] === 'panas' ? 'Panas' : 'Es' ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['opsi'])): ?>
                            <div style="font-size:var(--fs-xs); color:var(--terracotta); margin-top:3px;">
                                <?= in_array($item['kategori_dataset'] ?? $item['kategori'] ?? '', ['Bakery', 'Branded', 'Coffee beans', 'Flavours', 'Loose Tea', 'Packaged Chocolate'], true) ? 'Pilih varian' : esc($item['label_opsi'] ?? 'Varian') ?>: <?= esc($item['opsi']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="item-note-form">
                            <label for="note-<?= esc($item['key']) ?>" style="display:block;margin-bottom:4px;font-size:var(--fs-xs);font-weight:600;">Catatan menu</label>
                            <textarea class="item-note-input" id="note-<?= esc($item['key']) ?>" name="catatan[<?= esc($item['key']) ?>]" maxlength="500" placeholder="Contoh: pakai cabe ya"><?= esc($item['catatan'] ?? '') ?></textarea>
                            <small class="text-muted">Catatan ikut dikirim saat pesanan dikonfirmasi.</small>
                        </div>
                    </td>
                    <td data-label="Harga" style="text-align:right;">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                    <td data-label="Jumlah" style="text-align:center; white-space:nowrap;">
                        <a href="<?= base_url('/cart/qty/'.$id_meja.'/'.$item['key'].'/minus') ?>" class="qty-btn">-</a>
                        <span style="display:inline-block; width:28px; text-align:center; font-weight:700;"><?= (int) $item['qty'] ?></span>
                        <a href="<?= base_url('/cart/qty/'.$id_meja.'/'.$item['key'].'/plus') ?>" class="qty-btn">+</a>
                    </td>
                    <td data-label="Subtotal" style="text-align:right; font-weight:600;">
                        Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?>
                    </td>
                    <td data-label="Aksi" style="text-align:center;">
                        <a href="<?= base_url('cart/remove/'.$id_meja.'/'.$item['key']) ?>" class="btn-del-cart">
                            ✕ Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div class="cart-total-box">
            <span>Total Tagihan:</span>
            <span style="color:var(--terracotta);">Rp <?= number_format($total, 0, ',', '.') ?></span>
        </div>

        <div style="margin-top:var(--sp-6);">
                <label style="display:block; margin-bottom:6px; font-weight:600; font-size:var(--fs-sm); color:var(--espresso);">
                    Nama Pemesan
                </label>
                <input type="text"
                       name="nama_pelanggan"
                       class="checkout-input"
                       placeholder="Masukkan Nama Anda (contoh: Mas Dani)"
                       required>

                <div class="payment-method-box">
                    <span style="display:block; font-weight:700; font-size:var(--fs-sm); color:var(--espresso); margin-bottom:8px;">
                        Pilih Metode Pembayaran:
                    </span>

                    <label>
                        <input type="radio"
                               name="metode_pembayaran"
                               value="cash"
                               checked>
                        <span>💵 Tunai (Bayar langsung di kasir)</span>
                    </label>

                    <label>
                        <input type="radio"
                               name="metode_pembayaran"
                               value="qris">
                        <span>📲 QRIS (Scan barcode non-tunai)</span>
                    </label>
                </div>

                <button class="btn-checkout-beige" type="submit">
                    ✓ Kirim Pesanan Sekarang
                </button>

        </form>

            <a href="<?= base_url('/order/'.$id_meja) ?>" class="btn-add-more">
                + Tambah Menu Lainnya
            </a>
        </div>

        <?php else: ?>

        <div style="text-align:center; padding:var(--sp-10) var(--sp-4);">
            <div style="font-size:48px; margin-bottom:12px;">☕</div>
            <p style="color:var(--taupe); font-size:var(--fs-body);">Keranjang Anda masih kosong.</p>
            <a href="<?= base_url('/order/'.$id_meja) ?>" class="btn" style="margin-top:var(--sp-3);">
                Pilih Menu Sekarang
            </a>
        </div>

        <?php endif; ?>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>

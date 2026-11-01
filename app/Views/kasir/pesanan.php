<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pesanan — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .card-table-wrap {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-xl);
        padding: var(--sp-6);
        box-shadow: var(--shadow-md);
        margin-top: var(--sp-5);
    }
    .table-orders {
        width: 100%;
        border-collapse: collapse;
        border-radius: var(--r-md);
        overflow: hidden;
    }
    .table-orders th {
        background: var(--espresso);
        color: var(--cream);
        padding: 14px 16px;
        text-align: center;
        font-weight: 600;
        font-size: var(--fs-sm);
    }
    .table-orders td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--sand);
        text-align: center;
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .table-orders tr:nth-child(even) td {
        background: rgba(250, 245, 238, 0.5);
    }
    .table-orders tr:hover td {
        background: rgba(226, 205, 177, 0.25);
    }
    .status-badge {
        font-weight: 600;
        padding: 4px 12px;
        border-radius: var(--r-pill);
        display: inline-block;
        font-size: var(--fs-xs);
    }
    .status-badge.lunas {
        background: var(--sage-light);
        color: var(--sage);
        border: 1px solid var(--sage);
    }
    .status-badge.menunggu {
        background: var(--amber-light);
        color: var(--amber);
        border: 1px solid var(--amber);
    }
    .status-badge.dibatalkan {
        background: var(--brick-light);
        color: var(--brick);
        border: 1px solid var(--brick);
    }
    .btn-action-detail {
        background: var(--sand);
        color: var(--espresso);
        border: 1px solid var(--beige);
        padding: 6px 12px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        margin: 2px;
        display: inline-block;
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-action-detail:hover {
        background: var(--beige);
        color: var(--espresso);
    }
    .btn-action-bayar {
        background: var(--grad-btn);
        color: #fff;
        padding: 6px 14px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        margin: 2px;
        display: inline-block;
        box-shadow: var(--shadow-btn);
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-action-bayar:hover {
        filter: brightness(1.08);
        box-shadow: var(--shadow-btn-hover);
        color: #fff;
    }
    .btn-action-struk {
        background: var(--sage);
        color: #fff;
        padding: 6px 12px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        margin: 2px;
        display: inline-block;
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-action-struk:hover {
        filter: brightness(1.1);
        color: #fff;
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_kasir') ?>

<div class="container reveal">

    <?php if (session()->getFlashdata('success')): ?>
        <div role="status" class="alert-success-beige"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div role="alert" class="alert-error-beige"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="margin:0;">Kelola Pesanan Masuk</h1>
            <p class="text-muted" style="margin:4px 0 0 0;">Monitoring daftar pesanan dari seluruh meja secara real-time.</p>
        </div>
        <div>
            <button onclick="location.reload()" class="btn btn-neutral btn-sm" style="display:inline-flex; align-items:center; gap:6px;">
                🔄 Muat Ulang Pesanan
            </button>
        </div>
    </div>

    <div style="background:var(--sand); border:1px solid var(--beige); border-radius:var(--r-lg); padding:12px 18px; margin-top:var(--sp-4); display:flex; align-items:center; gap:12px; font-size:var(--fs-sm); color:var(--espresso);">
        <span style="font-size:22px; flex-shrink:0;">💡</span>
        <div>
            <strong>Alur Status:</strong> Pesanan <em>Menunggu Pembayaran</em> (kuning) diproses pembayarannya via tombol <strong>💳 Bayar</strong>. Setelah lunas, dapur menyiapkan sajian dan kasir dapat mencetak struk.
        </div>
    </div>

    <div class="card-table-wrap">

        <table class="table-orders">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Meja</th>
                    <th>Pelanggan</th>
                    <th>Status Pembayaran</th>
                    <th>Total Belanja</th>
                    <th>Waktu Pesan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr>
                    <td><code>#<?= esc($o['id_order']) ?></code></td>
                    <td><strong><?= esc($o['id_meja']) ?></strong></td>
                    <td><?= esc($o['nama_pelanggan']) ?></td>
                    <td>
                        <span class="status-badge <?= $o['status'] === 'lunas' ? 'lunas' : ($o['status'] === 'dibatalkan' ? 'dibatalkan' : 'menunggu') ?>">
                            <?= $o['status'] === 'lunas' ? '✓ Lunas' : ($o['status'] === 'dibatalkan' ? 'Dibatalkan' : '⏳ Menunggu Pembayaran') ?>
                        </span>
                    </td>
                    <td style="font-weight:700;">
                        Rp <?= number_format($o['total'], 0, ',', '.') ?>
                    </td>
                    <td style="color:var(--taupe); font-size:var(--fs-xs);">
                        <?= esc($o['created_at']) ?>
                    </td>
                    <td>
                        <a class="btn-action-detail" href="<?= base_url('/kasir/detail-pesanan/'.$o['id_order']) ?>">
                            👁️ Detail
                        </a>

                        <?php if($o['status'] === 'menunggu_pembayaran'): ?>
                            <a class="btn-action-bayar" href="<?= base_url('/kasir/bayar/'.$o['id_order']) ?>">
                                💳 Bayar
                            </a>
                            <form method="post" action="<?= base_url('/kasir/pesanan/batalkan/'.$o['id_order']) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-action-delete" onclick="return confirm('Batalkan pesanan ini dan kembalikan stok?')">
                                    Batalkan
                                </button>
                            </form>
                        <?php elseif($o['status'] === 'lunas'): ?>
                            <a class="btn-action-struk" href="<?= base_url('/kasir/struk/'.$o['id_order']) ?>">
                                🧾 Struk
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?> — Kelola Pesanan
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
        .filter-card {
            background: var(--ivory);
            padding: var(--sp-5);
            border-radius: var(--r-lg);
            border: 1px solid var(--sand);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--sp-5);
        }
        .filter-card form {
            display: flex;
            gap: var(--sp-3);
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-card input,
        .filter-card select {
            padding: 10px 14px;
            border: 1.5px solid var(--beige);
            background: var(--ivory);
            border-radius: var(--r-input);
            font-family: var(--font-body);
            font-size: var(--fs-sm);
            color: var(--espresso);
        }
        .filter-card input:focus,
        .filter-card select:focus {
            border-color: var(--terracotta);
            outline: none;
            box-shadow: 0 0 0 3px rgba(196, 127, 94, 0.18);
        }
        .summary-banner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: var(--sp-4);
            margin-top: var(--sp-6);
        }
        .summary-box {
            background: var(--ivory);
            border: 1px solid var(--sand);
            border-radius: var(--r-lg);
            padding: var(--sp-5);
            box-shadow: var(--shadow-sm);
        }
        .summary-box small {
            display: block;
            color: var(--taupe);
            font-size: var(--fs-xs);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .summary-box .val {
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--espresso);
            margin: 0;
        }
        /* DataTables Beige */
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 1.5px solid var(--beige) !important;
            border-radius: var(--r-input) !important;
            padding: 6px 12px !important;
            background: var(--ivory) !important;
            color: var(--espresso) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--button-success) !important;
            color: #fff !important;
            border: none !important;
            border-radius: var(--r-sm) !important;
        }
        table.dataTable thead th {
            background: var(--espresso) !important;
            color: var(--cream) !important;
            font-family: var(--font-body);
            font-weight: 600;
            border-bottom: none !important;
            padding: 12px 14px;
        }
        table.dataTable tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--sand);
            background: var(--ivory);
            color: var(--espresso);
        }
        table.dataTable tbody tr:hover td {
            background: rgba(226, 205, 177, 0.25) !important;
        }
    </style>
</head>
<body>

<?php if(session()->get('role') == 'admin'): ?>
    <?= $this->include('layout/navbar_admin') ?>
<?php else: ?>
    <?= $this->include('layout/navbar_kasir') ?>
<?php endif; ?>

<div class="container reveal">

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:var(--sp-4);">
        <div>
            <h1 style="margin:0;">Laporan Penjualan</h1>
            <p class="text-muted" style="margin:4px 0 0 0;">Rekapitulasi transaksi pemesanan dan omzet <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></p>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-card">
        <form method="get">
            <div>
                <label style="display:block; font-size:11px; color:var(--taupe); margin-bottom:2px;">DARI TANGGAL</label>
                <input type="date" name="from" value="<?= esc($from, 'attr') ?>">
            </div>

            <div>
                <label style="display:block; font-size:11px; color:var(--taupe); margin-bottom:2px;">SAMPAI TANGGAL</label>
                <input type="date" name="to" value="<?= esc($to, 'attr') ?>">
            </div>

            <?php if(session()->get('role') == 'admin'): ?>
            <div>
                <label style="display:block; font-size:11px; color:var(--taupe); margin-bottom:2px;">KASIR</label>
                <select name="id_user">
                    <option value="all">Semua Kasir</option>
                    <?php foreach($kasirList as $k): ?>
                    <option value="<?= esc($k['id_user'], 'attr') ?>" <?= ($id_user == $k['id_user']) ? 'selected' : '' ?>>
                        <?= esc($k['nama_kasir']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div style="align-self:flex-end;">
                <button type="submit" class="btn btn-filter btn-sm" style="padding:10px 18px;">
                    🔍 Terapkan Filter
                </button>
            </div>

            <div style="align-self:flex-end;">
                <a href="<?= esc(base_url('laporan/pdf?' . http_build_query([
                    'from' => $from,
                    'to' => $to,
                    'id_user' => $id_user,
                ])), 'attr') ?>"
                   class="btn btn-neutral btn-sm"
                   style="background:var(--sand); color:var(--espresso); border:1px solid var(--beige); padding:10px 18px;">
                    📄 Unduh Rekap PDF
                </a>
            </div>
        </form>
    </div>

    <!-- TABEL LAPORAN -->
    <div style="background:var(--ivory); border:1px solid var(--sand); border-radius:var(--r-xl); padding:var(--sp-6); box-shadow:var(--shadow-md);">
        <table class="display" id="tabelLaporan" style="width:100%;">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Meja</th>
                    <th>Pelanggan</th>
                    <?php if(session()->get('role') == 'admin'): ?>
                        <th>Kasir</th>
                    <?php endif; ?>
                    <th>Total Transaksi</th>
                    <th>Waktu & Tanggal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr>
                    <td><code>#<?= esc($o['id_order']) ?></code></td>
                    <td><strong><?= esc($o['id_meja']) ?></strong></td>
                    <td><?= esc($o['nama_pelanggan']) ?></td>
                    <?php if(session()->get('role') == 'admin'): ?>
                        <td><?= esc($o['nama_kasir'] ?? '-') ?></td>
                    <?php endif; ?>
                    <td style="font-weight:700;">
                        Rp <?= number_format($o['total'], 0, ',', '.') ?>
                    </td>
                    <td style="color:var(--taupe); font-size:var(--fs-xs);">
                        <?= esc($o['created_at']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- RINGKASAN OMZET -->
    <div class="summary-banner">
        <div class="summary-box">
            <small>Total Transaksi</small>
            <p class="val count-up"><?= (int)$total_pesanan ?> Pesanan</p>
        </div>
        <div class="summary-box">
            <small>Total Omzet / Pendapatan</small>
            <p class="val count-up" style="color:var(--terracotta);">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
        </div>
    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
$(document).ready(function(){
    $('#tabelLaporan').DataTable({
        pageLength: 10,
        lengthMenu: [
            [5, 10, 25, 50, 100, -1],
            [5, 10, 25, 50, 100, 'Semua']
        ],
        language: {
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi',
            paginate: { previous: 'Sebelumnya', next: 'Berikutnya' }
        }
    });
});
</script>
</body>
</html>

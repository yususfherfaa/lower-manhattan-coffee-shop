<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Kasir — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
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
    .table-beige {
        width: 100%;
        border-collapse: collapse;
        border-radius: var(--r-md);
        overflow: hidden;
    }
    .table-beige th {
        background: var(--espresso);
        color: var(--cream);
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: var(--fs-sm);
    }
    .table-beige td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--sand);
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .table-beige tr:nth-child(even) td {
        background: rgba(250, 245, 238, 0.5);
    }
    .table-beige tr:hover td {
        background: rgba(226, 205, 177, 0.25);
    }
    .btn-action-edit {
        background: var(--sage);
        color: #fff;
        padding: 6px 12px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        margin-right: 6px;
        transition: all var(--dur-fast) var(--ease);
        display: inline-block;
    }
    .btn-action-edit:hover {
        filter: brightness(1.1);
        color: #fff;
    }
    .btn-action-delete {
        background: var(--brick);
        color: #fff;
        padding: 6px 12px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        transition: all var(--dur-fast) var(--ease);
        display: inline-block;
    }
    .btn-action-delete:hover {
        filter: brightness(1.1);
        color: #fff;
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_admin') ?>

<div class="container reveal">

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div>
            <h1 style="margin:0;">Kelola Akun Kasir</h1>
            <p class="text-muted" style="margin:4px 0 0 0;">Daftar staf kasir yang memiliki akses transaksi sistem.</p>
        </div>
        <div>
            <a href="<?= base_url('admin/kasir/add') ?>" class="btn">
                ➕ Tambah Kasir Baru
            </a>
        </div>
    </div>

    <div class="card-table-wrap">

        <table class="table-beige">
            <thead>
                <tr>
                    <th style="width:120px;">ID User</th>
                    <th>Nama Kasir</th>
                    <th>Username</th>
                    <th style="width:180px; text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kasir as $k): ?>
                <tr>
                    <td><code><?= esc($k['id_user']) ?></code></td>
                    <td><strong><?= esc($k['nama_kasir']) ?></strong></td>
                    <td><?= esc($k['username']) ?></td>
                    <td style="text-align:center;">
                        <a href="<?= base_url('admin/kasir/edit/'.$k['id_user']) ?>" class="btn-action-edit">
                            ✏️ Edit
                        </a>
                        <a href="<?= base_url('admin/kasir/delete/'.$k['id_user']) ?>"
                           class="btn-action-delete"
                           onclick="return confirm('Hapus kasir <?= esc($k['nama_kasir']) ?>?')">
                            🗑️ Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>
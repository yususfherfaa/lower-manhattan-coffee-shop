<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kasir — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .form-card {
        max-width: 520px;
        margin: var(--sp-6) auto;
        background: var(--ivory);
        padding: var(--sp-8);
        border-radius: var(--r-xl);
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--sand);
    }
    .form-group {
        margin-bottom: var(--sp-5);
    }
    .form-group label {
        display: block;
        margin-bottom: var(--sp-2);
        font-weight: 600;
        color: var(--espresso);
        font-size: var(--fs-sm);
    }
    .form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid var(--beige);
        background: var(--ivory);
        border-radius: var(--r-input);
        font-family: var(--font-body);
        font-size: var(--fs-body);
        color: var(--espresso);
        box-sizing: border-box;
        transition: all var(--dur-fast) var(--ease);
    }
    .form-group input:focus {
        border-color: var(--terracotta);
        outline: none;
        box-shadow: 0 0 0 3px rgba(196, 127, 94, 0.18);
    }
    .btn-submit-beige {
        width: 100%;
        padding: 12px;
        border: none;
        background: var(--grad-btn);
        color: white;
        border-radius: var(--r-btn);
        cursor: pointer;
        font-size: var(--fs-body);
        font-weight: 600;
        font-family: var(--font-body);
        box-shadow: var(--shadow-btn);
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-submit-beige:hover {
        box-shadow: var(--shadow-btn-hover);
        transform: translateY(-2px);
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_admin') ?>

<div class="container reveal">

    <div class="form-card">

        <div style="margin-bottom:var(--sp-6);">
            <a href="<?= base_url('admin/kasir') ?>" style="font-size:var(--fs-xs); color:var(--taupe); text-decoration:none;">
                ← Kembali ke Daftar Kasir
            </a>
            <h2 style="text-align:left; margin:8px 0 4px 0;">Tambah Kasir Baru</h2>
            <p class="text-muted" style="font-size:var(--fs-sm); margin:0;">Buat akun login untuk staf kasir coffee shop.</p>
        </div>
        <?php if (session()->getFlashdata('error')): ?>
            <div role="alert" style="padding:10px;margin-bottom:16px;border-radius:8px;background:var(--brick-light);color:var(--brick);">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('admin/kasir/save') ?>">

            <div class="form-group">
                <label>Nama Lengkap Staf</label>
                <input type="text"
                       name="nama"
                       placeholder="Contoh: Budi Santoso"
                       required>
            </div>

            <div class="form-group">
                <label>Username Login</label>
                <input type="text"
                       name="username"
                       placeholder="Contoh: budi_kasir"
                       required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password"
                       name="password"
                       placeholder="Masukkan password"
                       minlength="8"
                       required>
            </div>

            <button type="submit" class="btn-submit-beige">
                💾 Simpan Kasir
            </button>

        </form>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
</body>
</html>
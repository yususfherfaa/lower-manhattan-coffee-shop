<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaturan Sistem — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
        .setting-box {
            max-width: 720px;
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
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--beige);
            background: var(--ivory);
            border-radius: var(--r-input);
            box-sizing: border-box;
            font-family: var(--font-body);
            font-size: var(--fs-body);
            color: var(--espresso);
            transition: all var(--dur-fast) var(--ease);
        }
        .form-control:focus {
            border-color: var(--terracotta);
            outline: none;
            box-shadow: 0 0 0 3px rgba(196, 127, 94, 0.18);
        }
        .logo-preview-wrap {
            display: flex;
            align-items: center;
            gap: var(--sp-4);
            margin-bottom: var(--sp-3);
        }
        .logo-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: var(--r-md);
            border: 2px solid var(--nude);
            background: var(--cream);
            box-shadow: var(--shadow-sm);
        }
        .alert-success-beige {
            background: var(--sage-light);
            color: var(--sage);
            border: 1px solid var(--sage);
            padding: var(--sp-3) var(--sp-4);
            border-radius: var(--r-md);
            margin-bottom: var(--sp-5);
            font-weight: 600;
        }
        .btn-setting-submit {
            background: var(--grad-btn);
            color: #fff;
            padding: 12px 28px;
            border: none;
            border-radius: var(--r-btn);
            cursor: pointer;
            font-weight: 600;
            font-family: var(--font-body);
            box-shadow: var(--shadow-btn);
            transition: all var(--dur-fast) var(--ease);
            display: inline-block;
        }
        .btn-setting-submit:hover {
            box-shadow: var(--shadow-btn-hover);
            transform: translateY(-2px);
        }
        .btn-qr-link {
            background: var(--sand);
            color: var(--espresso);
            padding: 12px 24px;
            border-radius: var(--r-btn);
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-left: var(--sp-3);
            border: 1px solid var(--beige);
            transition: all var(--dur-fast) var(--ease);
        }
        .btn-qr-link:hover {
            background: var(--beige);
            color: var(--espresso);
        }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_admin') ?>

<div class="container reveal">

    <div class="setting-box">

        <h2 style="text-align:left; margin-bottom:var(--sp-2);">Pengaturan Sistem</h2>
        <p class="text-muted" style="margin-bottom:var(--sp-6);">Sesuaikan identitas coffee shop, alamat, kontak, dan gambar QRIS pembayaran.</p>

        <?php if(session()->getFlashdata('success')): ?>
            <div class="alert-success-beige">
                ✓ <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div role="alert" style="padding:10px;margin-bottom:16px;border-radius:8px;background:var(--brick-light);color:var(--brick);">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form method="post"
              action="<?= base_url('admin/settings/update') ?>"
              enctype="multipart/form-data">

            <div class="form-group">
                <label>Nama Coffee Shop</label>
                <input
                    type="text"
                    name="nama_warkop"
                    class="form-control"
                    value="<?= esc($settings['nama_warkop'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label>Alamat Lengkap</label>
                <textarea
                    name="alamat"
                    class="form-control"
                    rows="3"><?= esc($settings['alamat'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Kontak / No. WhatsApp</label>
                <input
                    type="text"
                    name="kontak"
                    class="form-control"
                    value="<?= esc($settings['kontak'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>IP / URL Server (Host Pengunjung Meja)</label>
                <input
                    type="text"
                    name="server_url"
                    class="form-control"
                    value="<?= esc($settings['server_url'] ?? '') ?>"
                    placeholder="192.168.1.10:8080">
            </div>

            <div class="form-group">
                <label for="batas_pembayaran_menit">Batas pembayaran pesanan (menit)</label>
                <input
                    type="number"
                    id="batas_pembayaran_menit"
                    name="batas_pembayaran_menit"
                    class="form-control"
                    min="1"
                    max="1440"
                    value="<?= esc($settings['batas_pembayaran_menit'] ?? '15', 'attr') ?>"
                    required>
                <small class="text-muted">Pesanan yang belum dibayar melewati batas ini akan dibatalkan otomatis.</small>
            </div>

            <div class="form-group">
                <label>Logo Coffee Shop</label>
                <div class="logo-preview-wrap">
                    <img
                        src="<?= base_url($settings['logo'] ?? 'uploads/manhattan-coffee-shop-logo.png') ?>"
                        class="logo-preview"
                        id="logoPreview"
                        style="width:200px;height:122px;object-fit:contain;background:#fff;"
                        alt="Logo">
                    <div>
                        <input
                            type="file"
                            name="logo"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                            onchange="previewImg(this, 'logoPreview')">
                        <small class="text-muted">JPG, PNG, atau WebP; maksimal 2 MB. Logo saat ini menggunakan rasio lanskap.</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>QRIS Coffee Shop (Untuk Pembayaran Non-Tunai)</label>
                <div class="logo-preview-wrap">
                    <img
                        src="<?= base_url($settings['qris'] ?? 'uploads/qris-placeholder.svg') ?>"
                        class="logo-preview"
                        id="qrisPreview"
                        alt="QRIS">
                    <div>
                        <input
                            type="file"
                            name="qris"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp"
                            onchange="previewImg(this, 'qrisPreview')">
                        <small class="text-muted">JPG, PNG, atau WebP; maksimal 2 MB.</small>
                    </div>
                </div>
            </div>

            <div style="margin-top:var(--sp-6);">
                <button type="submit" class="btn-setting-submit">
                    💾 Simpan Setting
                </button>

                <a href="<?= base_url('admin/qr-meja') ?>" class="btn-qr-link">
                    📲 Generate QR Meja
                </a>
            </div>

        </form>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc($settings['nama_warkop'] ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
function previewImg(input, targetId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(targetId).src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>

<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Menu — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
        .form-edit-card {
            background: var(--ivory);
            padding: var(--sp-8);
            border-radius: var(--r-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--sand);
            max-width: 560px;
            margin: var(--sp-6) auto;
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
        .form-group input,
        .form-group select,
        .form-group textarea {
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
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--terracotta);
            outline: none;
            box-shadow: 0 0 0 3px rgba(196, 127, 94, 0.18);
        }
        .img-preview-box {
            display: flex;
            align-items: center;
            gap: var(--sp-4);
            margin-bottom: var(--sp-3);
        }
        .img-preview-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--r-md);
            border: 1.5px solid var(--beige);
            background: var(--cream);
        }
        .btn-update-menu {
            width: 100%;
            padding: 12px;
            background: var(--grad-btn);
            color: white;
            border: none;
            border-radius: var(--r-btn);
            cursor: pointer;
            font-weight: 700;
            font-family: var(--font-body);
            box-shadow: var(--shadow-btn);
            transition: all var(--dur-fast) var(--ease);
        }
        .btn-update-menu:hover {
            box-shadow: var(--shadow-btn-hover);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_kasir') ?>

<div class="container reveal">

    <div class="form-edit-card">

        <div style="margin-bottom:var(--sp-6);">
            <a href="<?= base_url('/kasir/menu') ?>" style="font-size:var(--fs-xs); color:var(--taupe); text-decoration:none;">
                ← Kembali ke Manajemen Menu
            </a>
            <h2 style="text-align:left; margin:8px 0 4px 0;">Edit Data Menu</h2>
            <p class="text-muted" style="font-size:var(--fs-sm); margin:0;">Perbarui detail harga, stok, atau unggah foto khusus untuk produk ini. Foto kustom tampil di katalog, rekomendasi, dan halaman admin. Untuk menu berukuran, foto dari salah satu ukuran akan dipakai pada kartu menu.</p>
        </div>
        <?php if (session()->getFlashdata('error')): ?>
            <div role="alert" style="padding:10px;margin-bottom:16px;border-radius:8px;background:var(--brick-light);color:var(--brick);"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div role="status" style="padding:10px;margin-bottom:16px;border-radius:8px;background:var(--sage-light);color:var(--sage);"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <form method="post"
              action="<?= base_url('/kasir/menu/update/'.$product['id_produk']) ?>"
              enctype="multipart/form-data">

            <div class="form-group">
                <label>Nama Menu</label>
                <input type="text" name="nama"
                       value="<?= esc($product['nama']) ?>"
                       placeholder="Nama Produk"
                       required>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi singkat (wajib)</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="255" required
                          placeholder="Jelaskan rasa atau sajian menu secara singkat"><?= esc(old('deskripsi', $product['deskripsi'] ?? '')) ?></textarea>
                <small class="text-muted">Maksimal 255 karakter. Tampil pada menu pelanggan dan rekomendasi.</small>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="kategori" id="editMenuCategory" required>
                    <?php foreach (['Bakery', 'Branded', 'Coffee', 'Coffee beans', 'Drinking Chocolate', 'Flavours', 'Loose Tea', 'Packaged Chocolate', 'Tea'] as $kat): ?>
                        <option value="<?= esc($kat) ?>" <?= $product['kategori'] === $kat ? 'selected' : '' ?>><?= esc($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="harga"
                       value="<?= esc($product['harga']) ?>"
                       min="0"
                       step="any"
                       placeholder="Harga"
                       required>
            </div>

            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stok"
                       value="<?= esc($product['stok']) ?>"
                       min="0"
                       step="1"
                       placeholder="Stok"
                       required>
            </div>

            <div class="form-group drink-only-field">
                <label style="display:flex; gap:8px; align-items:center;">
                    <input type="checkbox" name="varian_suhu" value="1" style="width:auto;" <?= !empty($product['varian_suhu']) ? 'checked' : '' ?>>
                    Menu punya pilihan Panas/Es
                </label>
            </div>

            <div class="form-group drink-only-field">
                <label>Suhu tetap (opsional)</label>
                <select name="suhu_tetap">
                    <option value="" <?= empty($product['suhu_tetap']) ? 'selected' : '' ?>>Pilih sendiri / tidak ditetapkan</option>
                    <option value="panas" <?= ($product['suhu_tetap'] ?? '') === 'panas' ? 'selected' : '' ?>>Panas saja</option>
                    <option value="dingin" <?= ($product['suhu_tetap'] ?? '') === 'dingin' ? 'selected' : '' ?>>Dingin saja</option>
                </select>
            </div>

            <div class="form-group drink-only-field">
                <label>Tambahan harga Es (Rp)</label>
                <input type="number" name="tambahan_harga_es"
                       value="<?= esc($product['tambahan_harga_es'] ?? 0) ?>"
                       min="0">
            </div>

            <div class="form-group">
                <label>Foto Produk Saat Ini</label>
                <div class="img-preview-box">
                    <img src="<?= product_image_url($product['gambar'] ?? null, $product['kategori_dataset'] ?? $product['kategori'] ?? null) ?>"
                         id="menuImgPreview"
                         alt="<?= esc($product['nama']) ?>"
                         onerror="this.src='<?= base_url('assets/no-image.svg') ?>'">
                    <div>
                        <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp" onchange="previewMenuImg(this)">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah foto. JPG, PNG, atau WebP; maksimal 2 MB.</small>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-update-menu">
                💾 Perbarui Menu
            </button>

        </form>

        <section style="margin-top:var(--sp-8);padding-top:var(--sp-5);border-top:1px solid var(--sand);">
            <h3 style="margin:0 0 6px;">Pilihan varian menu</h3>
            <p class="text-muted" style="font-size:var(--fs-sm);margin-top:0;">Varian aktif dapat dipilih pelanggan. Varian nonaktif tidak muncul pada menu pelanggan dan nama varian tetap tersimpan di riwayat pesanan.</p>
            <form method="post" action="<?= base_url('/kasir/menu/options/add/'.$product['id_produk']) ?>" style="display:grid;grid-template-columns:1fr 140px auto;gap:8px;align-items:end;margin-bottom:16px;">
                <?= csrf_field() ?>
                <div class="form-group" style="margin:0;">
                    <label>Nama varian</label>
                    <input type="text" name="nama_opsi" maxlength="100" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Tambahan harga</label>
                    <input type="number" name="tambahan_harga" min="0" value="0" required>
                </div>
                <button type="submit" class="btn-update-menu">Tambah varian</button>
            </form>
            <?php if (empty($options)): ?>
                <p class="text-muted" style="font-size:var(--fs-sm);">Belum ada varian.</p>
            <?php else: ?>
                <?php foreach ($options as $option): ?>
                <div style="padding:12px 0;border-top:1px solid var(--sand);">
                    <form method="post" action="<?= base_url('/kasir/menu/options/update/'.$option['id_opsi']) ?>" style="display:grid;grid-template-columns:1fr 130px auto;gap:8px;align-items:end;">
                        <?= csrf_field() ?>
                        <div class="form-group" style="margin:0;">
                            <label>Nama varian</label>
                            <input type="text" name="nama_opsi" value="<?= esc($option['nama_opsi']) ?>" maxlength="100" required>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>Tambah harga</label>
                            <input type="number" name="tambahan_harga" value="<?= esc($option['tambahan_harga']) ?>" min="0" required>
                        </div>
                        <button type="submit" class="btn-update-menu">Simpan</button>
                    </form>
                    <form method="post" action="<?= base_url('/kasir/menu/options/' . ((int) $option['aktif'] === 1 ? 'delete' : 'activate') . '/' . $option['id_opsi']) ?>" onsubmit="return confirm('<?= (int) $option['aktif'] === 1 ? 'Nonaktifkan' : 'Aktifkan kembali' ?> varian ini?')" style="text-align:right;margin-top:6px;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-del-cart"><?= (int) $option['aktif'] === 1 ? 'Nonaktifkan' : 'Aktifkan kembali' ?></button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
function previewMenuImg(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('menuImgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<script>
const drinkCategories = ['Coffee', 'Drinking Chocolate', 'Tea'];
const editMenuCategory = document.getElementById('editMenuCategory');
function updateDrinkFields(categorySelect) {
    const visible = drinkCategories.includes(categorySelect.value);
    categorySelect.form.querySelectorAll('.drink-only-field').forEach((field) => {
        field.hidden = !visible;
    });
}
editMenuCategory.addEventListener('change', () => updateDrinkFields(editMenuCategory));
updateDrinkFields(editMenuCategory);
</script>
</body>
</html>

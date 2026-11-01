<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Menu — <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
    <style>
    .form-menu-card {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-xl);
        padding: var(--sp-6);
        box-shadow: var(--shadow-md);
        margin-bottom: var(--sp-6);
    }
    .form-inline-menu {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: var(--sp-3);
        align-items: flex-end;
    }
    .form-inline-menu .form-col label {
        display: block;
        font-size: 11px;
        color: var(--taupe);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    .form-inline-menu input,
    .form-inline-menu select,
    .form-inline-menu textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--beige);
        background: var(--ivory);
        border-radius: var(--r-input);
        font-family: var(--font-body);
        font-size: var(--fs-sm);
        color: var(--espresso);
        box-sizing: border-box;
    }
    .form-inline-menu input:focus,
    .form-inline-menu select:focus,
    .form-inline-menu textarea:focus {
        border-color: var(--terracotta);
        outline: none;
        box-shadow: 0 0 0 3px rgba(196, 127, 94, 0.18);
    }
    .btn-add-menu {
        padding: 11px 22px;
        background: var(--grad-btn);
        color: white;
        border: none;
        border-radius: var(--r-btn);
        cursor: pointer;
        font-weight: 700;
        font-family: var(--font-body);
        box-shadow: var(--shadow-btn);
        transition: all var(--dur-fast) var(--ease);
        width: 100%;
    }
    .btn-add-menu:hover {
        box-shadow: var(--shadow-btn-hover);
        transform: translateY(-2px);
    }
    .menu-grid-admin {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: var(--sp-5);
    }
    .menu-item-card {
        background: var(--ivory);
        border: 1px solid var(--sand);
        border-radius: var(--r-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        transition: all var(--dur-fast) var(--ease);
    }
    .menu-item-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--beige);
    }
    .menu-item-card img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-bottom: 1px solid var(--sand);
    }
    .menu-item-body {
        padding: var(--sp-4);
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .menu-item-body h3 {
        margin: 0 0 4px 0;
        font-size: 1.05rem;
    }
    .menu-item-cat {
        font-size: var(--fs-xs);
        color: var(--taupe);
        margin-bottom: 8px;
    }
    .menu-item-price {
        font-family: var(--font-heading);
        font-weight: 700;
        color: var(--terracotta);
        font-size: 1.15rem;
        margin-bottom: 4px;
    }
    .menu-item-stock {
        font-size: var(--fs-xs);
        color: var(--mocha);
        margin-bottom: 14px;
    }
    .menu-size-list {
        display: grid;
        gap: 8px;
        margin: 4px 0 14px;
    }
    .menu-size-row {
        border: 1px solid var(--sand);
        border-radius: var(--r-sm);
        padding: 8px;
        font-size: var(--fs-xs);
    }
    .menu-size-row-main {
        display: flex;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .menu-size-row-actions {
        display: flex;
        gap: 6px;
    }
    .menu-item-actions {
        margin-top: auto;
        display: flex;
        gap: 6px;
    }
    .btn-menu-edit {
        flex: 1;
        background: var(--sand);
        color: var(--espresso);
        text-align: center;
        padding: 6px 10px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        border: 1px solid var(--beige);
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-menu-edit:hover {
        background: var(--beige);
        color: var(--espresso);
    }
    .btn-menu-delete {
        flex: 1;
        background: var(--brick);
        color: white;
        text-align: center;
        padding: 6px 10px;
        border-radius: var(--r-sm);
        text-decoration: none;
        font-size: var(--fs-xs);
        font-weight: 600;
        transition: all var(--dur-fast) var(--ease);
    }
    .btn-menu-delete:hover {
        filter: brightness(1.1);
        color: white;
    }
    </style>
</head>
<body>

<?= $this->include('layout/navbar_kasir') ?>

<div class="container reveal">

    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:var(--sp-5);">
        <div>
            <h1 style="margin:0;">Manajemen Menu</h1>
            <p class="text-muted" style="margin:4px 0 0 0;">Tambah dan perbarui katalog sajian <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?></p>
            <p class="text-muted" style="margin:4px 0 0;font-size:var(--fs-xs);">Unggah foto asli setiap menu melalui tombol <strong>Edit</strong>. Sebelum foto diunggah, gambar kategori hanya menjadi ilustrasi sementara. Untuk menu dengan ukuran, foto dari salah satu ukuran akan dipakai pada kartu menu.</p>
        </div>
    </div>
    <?php if (session()->getFlashdata('error')): ?>
        <div role="alert" style="padding:10px;margin-bottom:16px;border-radius:8px;background:var(--brick-light);color:var(--brick);"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- FORM TAMBAH PRODUK -->
    <div class="form-menu-card">
        <h3 style="margin:0 0 var(--sp-4) 0; font-size:1.15rem;">➕ Tambah Menu Baru</h3>

        <form method="post"
              action="<?= base_url('/kasir/menu/add') ?>"
              enctype="multipart/form-data"
              class="form-inline-menu">

            <div class="form-col">
                <label>Nama Menu</label>
                <input type="text" name="nama" placeholder="Contoh: Kopi Tubruk" required>
            </div>

            <div class="form-col">
                <label>Deskripsi singkat (wajib)</label>
                <textarea name="deskripsi" rows="2" maxlength="255" placeholder="Tuliskan deskripsi yang sesuai dengan produk" required></textarea>
            </div>

            <div class="form-col">
                <label>Kategori</label>
                <select name="kategori" id="newMenuCategory" required>
                    <?php foreach ($kategori_suggestions as $kat): ?>
                        <option value="<?= esc($kat) ?>"><?= esc($kat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-col">
                <label>Harga (Rp)</label>
                <input type="number" name="harga" min="0" step="any" placeholder="Contoh: 10000" required>
            </div>

            <div class="form-col">
                <label>Stok Awal</label>
                <input type="number" name="stok" min="0" step="1" placeholder="Contoh: 50" required>
            </div>

            <div class="form-col">
                <label>Foto Menu</label>
                <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp">
                <small class="text-muted">Opsional. JPG, PNG, atau WebP; maksimal 2 MB.</small>
            </div>

            <div class="form-col drink-only-field">
                <label>Pilihan Suhu</label>
                <label style="display:flex; gap:8px; align-items:center; text-transform:none; letter-spacing:0; color:var(--espresso);">
                    <input type="checkbox" name="varian_suhu" value="1" style="width:auto;">
                    Panas/Es
                </label>
            </div>

            <div class="form-col drink-only-field">
                <label>Suhu tetap</label>
                <select name="suhu_tetap">
                    <option value="">Tidak ditetapkan</option>
                    <option value="panas">Panas saja</option>
                    <option value="dingin">Dingin saja</option>
                </select>
            </div>

            <div class="form-col drink-only-field">
                <label>Tambahan harga Es (Rp)</label>
                <input type="number" name="tambahan_harga_es" value="0" min="0">
            </div>

            <div class="form-col">
                <button type="submit" class="btn-add-menu">+ Simpan Menu</button>
            </div>

        </form>
    </div>

    <!-- GRID CARD PRODUK -->
    <div class="menu-grid-admin">
        <?php foreach (group_catalog_products_by_size($products) as $menuGroup): ?>
        <?php
            $p = $menuGroup['product'];
            $menuName = $menuGroup['name'];
            $sizeVariants = $menuGroup['variants'];
        ?>
        <div class="menu-item-card">
            <img src="<?= product_image_url($menuGroup['image_product']['gambar'] ?? null, $menuGroup['image_product']['kategori_dataset'] ?? $menuGroup['image_product']['kategori'] ?? null) ?>"
                 alt="<?= esc($menuName) ?>"
                 loading="lazy"
                 onerror="this.src='<?= base_url('assets/no-image.svg') ?>'">

            <div class="menu-item-body">
                <h3><?= esc($menuName) ?></h3>
                <?php if (!empty($p['deskripsi'])): ?>
                    <p class="text-muted" style="font-size:var(--fs-xs);margin:0 0 8px;"><?= esc($p['deskripsi']) ?></p>
                <?php endif; ?>
                <?php if (!empty($p['varian_suhu'])): ?>
                    <div style="display:inline-block; align-self:flex-start; background:var(--sand); border:1px solid var(--beige); border-radius:var(--r-pill); padding:3px 9px; font-size:11px; font-weight:700; margin-bottom:6px;">Panas/Es</div>
                <?php endif; ?>
                <?php if (!empty($p['suhu_tetap'])): ?>
                    <div style="display:inline-block; align-self:flex-start; background:var(--sand); border:1px solid var(--beige); border-radius:var(--r-pill); padding:3px 9px; font-size:11px; font-weight:700; margin-bottom:6px;"><?= $p['suhu_tetap'] === 'panas' ? 'Panas tetap' : 'Es tetap' ?></div>
                <?php endif; ?>
                <?php if (!empty($p['punya_opsi'])): ?>
                    <div style="display:inline-block; align-self:flex-start; background:var(--sage-light); border:1px solid var(--sage); color:var(--sage); border-radius:var(--r-pill); padding:3px 9px; font-size:11px; font-weight:700; margin-bottom:6px;">Memiliki varian</div>
                <?php endif; ?>
                <div class="menu-item-cat">🏷️ <?= esc($p['kategori']) ?></div>
                <?php if ($menuGroup['is_size_group']): ?>
                <div class="menu-size-list" aria-label="Ukuran dan stok <?= esc($menuName) ?>">
                    <?php foreach ($sizeVariants as $sizeVariant):
                        $sizeProduct = $sizeVariant['product'];
                    ?>
                    <div class="menu-size-row">
                        <div class="menu-size-row-main">
                            <strong><?= esc($sizeVariant['label']) ?> · Rp <?= number_format($sizeProduct['harga'], 0, ',', '.') ?></strong>
                            <span>Stok: <?= (int) $sizeProduct['stok'] ?></span>
                        </div>
                        <div class="menu-size-row-actions">
                            <a class="btn-menu-edit" href="<?= base_url('/kasir/menu/edit/' . $sizeProduct['id_produk']) ?>">Edit</a>
                            <a class="btn-menu-delete"
                               href="<?= base_url('/kasir/menu/delete/' . $sizeProduct['id_produk']) ?>"
                               onclick="return confirm('Hapus ukuran <?= esc($sizeVariant['label']) ?> dari <?= esc($menuName) ?>?')">Hapus</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="menu-item-price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
                <?php if (!empty($p['varian_suhu']) && (float) ($p['tambahan_harga_es'] ?? 0) > 0): ?>
                    <div class="menu-item-stock">Es +Rp <?= number_format($p['tambahan_harga_es'], 0, ',', '.') ?></div>
                <?php endif; ?>
                <div class="menu-item-stock">
                    Stok: <?= $p['stok'] > 0 ? esc($p['stok']) . ' porsi' : '<span style="color:var(--brick);font-weight:bold;">Habis</span>' ?>
                </div>

                <div class="menu-item-actions">
                    <a class="btn-menu-edit" href="<?= base_url('/kasir/menu/edit/'.$p['id_produk']) ?>">
                        ✏️ Edit
                    </a>
                    <a class="btn-menu-delete"
                       href="<?= base_url('/kasir/menu/delete/'.$p['id_produk']) ?>"
                       onclick="return confirm('Hapus menu <?= esc($p['nama']) ?>?')">
                        🗑️ Hapus
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<footer style="text-align:center; padding:var(--sp-8); color:var(--taupe); font-size:var(--fs-sm);">
    © <?= date('Y') ?> <?= esc(setting('nama_warkop') ?? 'Lower Manhattan Coffee Shop') ?>
</footer>

<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
const drinkCategories = ['Coffee', 'Drinking Chocolate', 'Tea'];
const newMenuCategory = document.getElementById('newMenuCategory');
function updateDrinkFields(categorySelect) {
    const visible = drinkCategories.includes(categorySelect.value);
    categorySelect.form.querySelectorAll('.drink-only-field').forEach((field) => {
        field.hidden = !visible;
    });
}
newMenuCategory.addEventListener('change', () => updateDrinkFields(newMenuCategory));
updateDrinkFields(newMenuCategory);
</script>
</body>
</html>

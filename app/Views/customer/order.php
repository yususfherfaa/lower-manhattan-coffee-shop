<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Menu — <?= esc($nama_warkop) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/theme.css') ?>">
<style>
.container {
    padding-bottom: 112px;
}

/* Page-specific: product card image */
.product-card img {
    width: 100%; height: 180px; object-fit: cover;
    border-bottom: 1px solid var(--sand);
    transition: transform var(--dur) var(--ease);
}
.product-card:hover img { transform: scale(1.05); }
.product-card .card-body { padding: var(--sp-4); flex: 1; display: flex; flex-direction: column; }
.product-card { display: flex; flex-direction: column; overflow: hidden; }
.product-card .btn-add-wrap { margin-top: auto; }
.product-description {
    color: var(--taupe);
    font-size: var(--fs-xs);
    line-height: 1.5;
    margin: 4px 0 8px;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    overflow: hidden;
}
.rekomen-description {
    margin: 8px 0;
    padding: 10px 12px;
    border-left: 3px solid var(--terracotta);
    border-radius: 0 var(--r-sm) var(--r-sm) 0;
    background: var(--ivory);
    color: var(--espresso);
    font-size: var(--fs-xs);
    line-height: 1.6;
}
.rekomen-description-label {
    display: block;
    margin-bottom: 3px;
    color: var(--terracotta);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}

/* Out of stock overlay */
.product-card.out-of-stock img { filter: grayscale(.7); opacity: .6; }
.product-card.out-of-stock .oos-label {
    position: absolute; top: var(--sp-3); right: var(--sp-3);
    background: var(--brick); color: #fff; font-size: var(--fs-xs); font-weight: 700;
    padding: 3px 10px; border-radius: var(--r-pill);
}

/* Category chips */
.category-chips {
    display: flex; gap: var(--sp-2); overflow-x: auto; padding: var(--sp-2) 0;
    -webkit-overflow-scrolling: touch; scrollbar-width: none;
    position: sticky; top: 0; z-index: 50; background: var(--cream);
    margin-bottom: var(--sp-5);
}
.category-chips::-webkit-scrollbar { display: none; }

/* Search */
.search-box {
    margin-bottom: var(--sp-4);
}
.search-box input {
    background: var(--ivory); border: 1.5px solid var(--beige); border-radius: var(--r-pill);
    padding: var(--sp-3) var(--sp-5); width: 100%; font-size: var(--fs-sm);
}
.search-box input:focus { border-color: var(--terracotta); box-shadow: 0 0 0 3px rgba(196,127,94,.15); }

/* Floating cart bar */
.floating-cart-bar {
    position: fixed; bottom: 0; left: 0; right: 0;
    background: var(--espresso); color: #fff;
    padding: var(--sp-3) var(--sp-5);
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 -4px 20px rgba(74,52,38,.15);
    z-index: 100; transform: translateY(100%);
    transition: transform var(--dur) var(--ease);
    border-radius: var(--r-lg) var(--r-lg) 0 0;
}
.floating-cart-bar.show { transform: translateY(0); }
.floating-cart-bar .cart-info { display: flex; align-items: center; gap: var(--sp-3); }
.floating-cart-bar .cart-count-badge {
    background: var(--terracotta); color: #fff; border-radius: 50%;
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: var(--fs-xs);
}
.floating-cart-bar .cart-link {
    background: var(--grad-btn); color: #fff; padding: var(--sp-2) var(--sp-5);
    border-radius: var(--r-btn); text-decoration: none; font-weight: 600; font-size: var(--fs-sm);
    transition: all var(--dur-fast) var(--ease);
}
.floating-cart-bar .cart-link:hover { filter: brightness(1.08); }

/* Rank badge on rekomen card */
.rank-badge {
    position: absolute; top: 8px; left: 8px;
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--terracotta); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800; border: 2px solid #fff;
    box-shadow: var(--shadow-sm);
    z-index: 3;
}

/* Toast add */
.add-toast {
    position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%) translateY(20px);
    background: var(--espresso); color: #fff; padding: var(--sp-3) var(--sp-5);
    border-radius: var(--r-pill); font-size: var(--fs-sm); font-weight: 600;
    box-shadow: var(--shadow-lg); opacity: 0; transition: all var(--dur) var(--ease);
    z-index: 200; white-space: nowrap;
}
.add-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

.suhu-toggle {
    display: inline-grid;
    grid-template-columns: 1fr 1fr;
    gap: 2px;
    background: var(--sand);
    border: 1px solid var(--beige);
    border-radius: var(--r-pill);
    padding: 2px;
    margin: 6px 0 10px;
    width: 100%;
}
.suhu-toggle button {
    border: 0;
    border-radius: var(--r-pill);
    background: transparent;
    color: var(--mocha);
    font-size: 12px;
    font-weight: 700;
    padding: 6px 8px;
    cursor: pointer;
}
.suhu-toggle button.active {
    background: var(--ivory);
    color: var(--espresso);
    box-shadow: var(--shadow-sm);
}
.size-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(88px, 1fr));
    gap: 6px;
    margin: 8px 0;
}
.size-choice {
    border: 1px solid var(--beige);
    border-radius: var(--r-md);
    background: var(--ivory);
    color: var(--espresso);
    padding: 8px 5px;
    cursor: pointer;
    font: inherit;
    font-size: 11px;
    line-height: 1.4;
}
.size-choice.active {
    border-color: var(--terracotta);
    box-shadow: 0 0 0 2px rgba(196,127,94,.16);
    background: var(--sand);
}
.size-choice-price { display: block; font-weight: 700; }
.group-members { font-size: var(--fs-xs); color: var(--taupe); line-height: 1.4; margin-bottom: 10px; }
.sheet-backdrop {
    position: fixed; inset: 0; background: rgba(48,34,24,.32);
    display: none; align-items: flex-end; z-index: 300;
}
.sheet-backdrop.show { display: flex; }
.bottom-sheet {
    background: var(--ivory); border-radius: 16px 16px 0 0; padding: 18px;
    width: 100%; max-height: 78vh; overflow: auto; box-shadow: var(--shadow-lg);
    transform: translateY(100%); transition: transform var(--dur) var(--ease);
}
.sheet-backdrop.show .bottom-sheet { transform: translateY(0); }
.sheet-row {
    display: grid; grid-template-columns: 64px 1fr auto; gap: 12px; align-items: center;
    padding: 10px 0; border-bottom: 1px solid var(--sand);
}
.sheet-row img { width: 64px; height: 52px; object-fit: cover; border-radius: 8px; }
.option-choice {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    width: 100%; padding: 12px; margin: 8px 0; border: 1px solid var(--sand);
    border-radius: var(--r-md); background: #fff; color: var(--espresso);
    text-align: left; cursor: pointer;
}
.option-choice.selected { border-color: var(--terracotta); box-shadow: 0 0 0 2px rgba(196,127,94,.16); }

/* ── REKOMENDASI CARD ENHANCEMENTS (REVISI 2) ── */
.rekomendasi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: var(--sp-4);
    align-items: stretch;
}

.rekomen-card {
    background: #fff;
    border: 1.5px solid rgba(226,205,177,.4);
    border-radius: 16px;
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    box-shadow: var(--shadow-sm);
    transition: transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease), border-color var(--dur) var(--ease);
}
.rekomen-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 26px rgba(196,127,94,.18);
    border-color: var(--terracotta);
}

.rekomen-img-wrap {
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    position: relative;
    background: var(--sand);
}
.rekomen-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform var(--dur) var(--ease);
}
.rekomen-card:hover .rekomen-img-wrap img {
    transform: scale(1.06);
}

/* Out of stock on recommendation card */
.rekomen-card.rekomen-habis .rekomen-img-wrap img {
    filter: grayscale(0.85);
    opacity: 0.55;
}
.rekomen-oos-badge {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--brick);
    color: #fff;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: var(--r-pill);
    box-shadow: var(--shadow-md);
    letter-spacing: 0.05em;
    z-index: 2;
}

.rekomen-card-body {
    padding: 12px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.rekomen-kategori-label {
    align-self: flex-start;
    font-size: 10px;
    background: rgba(196,127,94,.1);
    color: var(--terracotta);
    border-radius: 4px;
    padding: 2px 7px;
    margin-bottom: 6px;
    font-weight: 600;
}

.rekomen-nama {
    font-size: var(--fs-sm);
    font-weight: 700;
    color: var(--espresso);
    margin: 0 0 4px 0;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 2.7em;
}
.rekomen-size-label {
    display: inline-flex;
    align-self: flex-start;
    margin: 0 0 6px;
    padding: 3px 8px;
    border-radius: var(--r-pill);
    background: var(--sand);
    color: var(--mocha);
    font-size: 11px;
    font-weight: 700;
}
.rekomen-pitch {
    color: var(--mocha);
    font-size: var(--fs-xs);
    line-height: 1.45;
    margin: 2px 0 10px;
}

.rekomen-harga {
    font-family: var(--font-heading);
    font-size: var(--fs-sm);
    font-weight: 700;
    color: var(--mocha);
    margin-bottom: 10px;
    font-variant-numeric: tabular-nums;
}

.rekomen-btn-wrap {
    margin-top: auto;
}
.rekomen-card .rekomen-btn {
    display: block;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
    background: var(--grad-btn);
    color: #fff;
    font-size: var(--fs-xs);
    font-weight: 700;
    padding: 8px 10px;
    border-radius: var(--r-btn);
    text-decoration: none;
    border: none;
    cursor: pointer;
    box-shadow: var(--shadow-btn);
    transition: all var(--dur-fast) var(--ease);
}
.rekomen-card .rekomen-btn:hover {
    filter: brightness(1.08);
    box-shadow: var(--shadow-btn-hover);
    transform: translateY(-1px);
}
.rekomen-card .rekomen-btn[disabled] {
    background: var(--sand);
    color: var(--taupe);
    box-shadow: none;
    cursor: not-allowed;
    transform: none;
}

/* Skeleton shimmer */
.rekomen-skeleton-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: var(--sp-4);
}
.rekomen-skeleton-card {
    background: #fff;
    border: 1px solid var(--sand);
    border-radius: 16px;
    overflow: hidden;
}
.skeleton-box {
    background: linear-gradient(90deg, #EFE3D3 25%, #FFFDF9 50%, #EFE3D3 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Clock pulse animation */
.clock-icon-live {
    display: inline-block;
    animation: clockBeat 2s infinite ease-in-out;
}
@keyframes clockBeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
}

/* Responsive carousel snap on small screens */
@media (max-width: 520px) {
    .rekomendasi-grid {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding-bottom: 12px;
        -webkit-overflow-scrolling: touch;
        gap: 12px;
    }
    .rekomen-card {
        flex: 0 0 175px;
        scroll-snap-align: start;
    }
}
</style>
</head>

<body>

<!-- ══ HERO HEADER ═════════════════════════════════════════════ -->
<div class="header-hero">
    <img src="<?= base_url($logo) ?>" class="logo" style="width:180px;height:110px;object-fit:contain;border-radius:8px;background:#fff;" alt="Lower Manhattan Coffee Shop" onerror="this.src='<?= base_url('uploads/manhattan-coffee-shop-logo.png') ?>'">
    <h1><?= esc($nama_warkop) ?></h1>
    <p id="greetingText" style="margin-bottom:4px;">Silakan pilih sajian favorit langsung dari meja Anda</p>
    <div style="display:flex; justify-content:center; align-items:center; gap:8px; margin-top:10px; flex-wrap:wrap;">
        <div class="badge-meja" style="margin:0;">
            🍽️ <?= esc($meja['nama'] ?? $id_meja) ?>
        </div>
        <div style="background:rgba(255,255,255,0.7); backdrop-filter:blur(4px); padding:6px 14px; border-radius:var(--r-pill); font-size:12px; font-weight:600; color:var(--espresso); border:1px solid var(--beige);">
            ✨ Pesanan langsung masuk ke sistem kasir
        </div>
    </div>
</div>

<div class="container" style="max-width:900px;">

    <?php if (session()->getFlashdata('error')): ?>
    <div role="alert" class="badge" style="display:block; margin:12px 0; padding:12px; background:var(--brick-light); color:var(--brick);">
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
    <?php endif; ?>

    <!-- ══ WIDGET REKOMENDASI AI ═══════════════════════════════ -->
    <div class="rekomendasi-section reveal" id="rekomendasiBox">
        <form id="recommendationLogCsrf" hidden><?= csrf_field() ?></form>
        <div class="rekomendasi-header">
            <div class="icon" id="rekomendasiEmoji">⏳</div>
            <div class="info">
                <h2>
                    Menu Rekomendasi
                    <span class="badge-ml">✨ AI · KNN</span>
                </h2>
                <p id="rekomendasiSubtitle">Memuat rekomendasi berdasarkan waktu sekarang…</p>
            </div>
        </div>

        <!-- Loading state with skeleton shimmer -->
        <div class="rekomen-loading" id="rekomendasiLoading" style="padding:var(--sp-4) 0;">
            <div class="rekomen-skeleton-grid">
                <?php for($i=0; $i<4; $i++): ?>
                <div class="rekomen-skeleton-card">
                    <div class="skeleton-box" style="width:100%; aspect-ratio:4/3;"></div>
                    <div style="padding:12px;">
                        <div class="skeleton-box" style="height:12px; width:45%; border-radius:4px; margin-bottom:8px;"></div>
                        <div class="skeleton-box" style="height:14px; width:80%; border-radius:4px; margin-bottom:6px;"></div>
                        <div class="skeleton-box" style="height:14px; width:60%; border-radius:4px; margin-bottom:12px;"></div>
                        <div class="skeleton-box" style="height:28px; width:100%; border-radius:8px;"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Grid hasil rekomendasi -->
        <div class="rekomendasi-grid" id="rekomendasiGrid" style="display:none;"></div>

        <div class="rekomendasi-footer" id="rekomendasiFooter" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-top:var(--sp-4);">
            <div id="liveClockChip" style="font-family:var(--font-body); font-variant-numeric:tabular-nums; font-weight:600; font-size:var(--fs-xs); color:var(--espresso); background:var(--sand); padding:4px 12px; border-radius:var(--r-pill); display:inline-flex; align-items:center; gap:6px;">
                <span class="clock-icon-live">⏱️</span>
                <span id="liveClockText">--:--:--</span>
            </div>
            <div id="methodBadgeText" style="font-size:var(--fs-xs); color:var(--taupe); font-weight:600;">
                Metode: Memuat...
            </div>
        </div>
    </div>
    <!-- ══ END WIDGET ══════════════════════════════════════════ -->

    <!-- ══ SEARCH ═════════════════════════════════════════════ -->
    <div class="search-box reveal">
        <input type="text" id="searchMenu" placeholder="🔍 Cari menu..." oninput="filterMenu()">
    </div>

    <!-- ══ CATEGORY CHIPS ═════════════════════════════════════ -->
    <?php
        $produk_by_kategori = [];
        $categoryOrder = ['Bakery', 'Branded', 'Coffee', 'Coffee beans', 'Drinking Chocolate', 'Flavours', 'Loose Tea', 'Packaged Chocolate', 'Tea'];
        $legacyCategoryMap = [
            'Kopi Panas' => 'Coffee',
            'Es Kopi' => 'Coffee',
            'Minuman Kopi' => 'Coffee',
            'Es Nutrisari' => 'Tea',
            'Minuman Non Kopi' => 'Tea',
            'Makanan Berat' => 'Bakery',
            'Cemilan' => 'Bakery',
        ];
        foreach ($products as $p) {
            $sourceCategory = $p['kategori_dataset'] ?? $p['kategori'] ?? '';
            $kategori = in_array($sourceCategory, $categoryOrder, true)
                ? $sourceCategory
                : ($legacyCategoryMap[$sourceCategory] ?? 'Bakery');
            if (!isset($produk_by_kategori[$kategori])) {
                $produk_by_kategori[$kategori] = [];
            }
            $produk_by_kategori[$kategori][] = $p;
        }
    ?>
    <div class="category-chips reveal" id="categoryChips">
        <button class="chip active" onclick="filterCategory('all', this)">Semua</button>
        <?php foreach ($categoryOrder as $kat): ?>
        <?php if (isset($produk_by_kategori[$kat])): ?>
        <button class="chip" onclick="filterCategory('<?= esc($kat) ?>', this)"><?= esc($kat) ?></button>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- ══ PRODUCT GRID PER KATEGORI ══════════════════════════ -->
    <?php foreach ($produk_by_kategori as $kategori => $items): ?>
    <div class="kategori-section" data-kategori="<?= esc($kategori) ?>">
        <h2 class="kategori-title reveal" data-judul-asli="<?= esc($kategori) ?>"><?= esc($kategori) ?></h2>
        <div class="grid">
            <?php foreach (group_catalog_products_by_size($items) as $menuGroup): ?>
            <?php
                $p = $menuGroup['product'];
                $menuName = $menuGroup['name'];
                $sizeVariants = $menuGroup['variants'];
                $selectedSizeLabel = ['Sm' => 'Small', 'Rg' => 'Regular', 'Lg' => 'Large'][$p['ukuran'] ?? ''] ?? 'Regular';
                $sourceCategory = $p['kategori_dataset'] ?? $p['kategori'] ?? '';
                $isDrink = in_array($sourceCategory, ['Coffee', 'Drinking Chocolate', 'Tea', 'Kopi Panas', 'Es Kopi', 'Es Nutrisari', 'Minuman Non Kopi', 'Minuman Kopi'], true);
                if (!$isDrink) {
                    $drinkTemperatures = '';
                } elseif (!empty($p['varian_suhu'])) {
                    $drinkTemperatures = 'panas,dingin';
                } elseif (($p['suhu_tetap'] ?? '') === 'panas') {
                    $drinkTemperatures = 'panas';
                } else {
                    $drinkTemperatures = 'dingin';
                }
            ?>
            <div class="card product-card reveal <?= $p['stok'] <= 0 ? 'out-of-stock' : '' ?>"
                 data-nama="<?= strtolower(esc($menuName . ' ' . implode(' ', array_column($sizeVariants, 'label')) . ' ' . ($p['kategori_dataset'] ?? $p['kategori']) . ' ' . ($p['tipe_dataset'] ?? ''))) ?>"
                 data-suhu-minuman="<?= esc($drinkTemperatures) ?>"
                 style="position:relative;">

                <span class="oos-label" style="<?= $p['stok'] > 0 ? 'display:none;' : '' ?>">Stok Habis</span>

                <img src="<?= product_image_url($menuGroup['image_product']['gambar'] ?? null, $menuGroup['image_product']['kategori_dataset'] ?? $menuGroup['image_product']['kategori'] ?? null) ?>"
                     alt="<?= esc($menuName) ?>"
                     onerror="this.src='<?= base_url('assets/no-image.svg') ?>'">

                <div class="card-body">
                    <h3 class="card-title"><?= esc($menuName) ?></h3>
                    <span class="badge" style="align-self:flex-start; margin:4px 0 6px; background:var(--sand); color:var(--mocha);">
                        <?= esc($p['kategori_dataset'] ?? $p['kategori']) ?>
                    </span>
                    <?php if (!empty($p['deskripsi'])): ?>
                    <p class="product-description"><?= esc($p['deskripsi']) ?></p>
                    <?php endif; ?>
                    <div class="card-price tabular-nums" data-price="<?= (float) $p['harga'] ?>" data-es="<?= (float) ($p['tambahan_harga_es'] ?? 0) ?>">
                        Rp <?= number_format($p['harga'], 0, ',', '.') ?>
                    </div>
                    <?php if ($menuGroup['is_size_group']): ?>
                    <div class="size-selector" role="group" aria-label="Pilih ukuran <?= esc($menuName) ?>">
                        <?php foreach ($sizeVariants as $sizeVariant):
                            $sizeProduct = $sizeVariant['product'];
                            $isSelectedSize = $sizeProduct['id_produk'] === $p['id_produk'];
                        ?>
                        <button type="button"
                                class="size-choice <?= $isSelectedSize ? 'active' : '' ?>"
                                aria-pressed="<?= $isSelectedSize ? 'true' : 'false' ?>"
                                data-product-id="<?= esc($sizeProduct['id_produk']) ?>"
                                data-price="<?= (float) $sizeProduct['harga'] ?>"
                                data-stock="<?= (int) $sizeProduct['stok'] ?>"
                                data-size-label="<?= esc($sizeVariant['label']) ?>"
                                onclick="selectProductSize(this)">
                            <?= esc($sizeVariant['label']) ?>
                            <span class="size-choice-price">Rp <?= number_format($sizeProduct['harga'], 0, ',', '.') ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($p['suhu_tetap'])): ?>
                    <span class="badge" style="align-self:flex-start; margin-top:6px; background:var(--sand); color:var(--espresso);">
                        <?= $p['suhu_tetap'] === 'panas' ? 'Panas' : 'Es' ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($p['varian_suhu'])):
                        $defaultSuhu = in_array($p['id_produk'], ['PRD0000016','PRD0000017','PRD0000019'], true) ? 'es' : 'panas';
                    ?>
                    <div class="suhu-toggle" data-id="<?= esc($p['id_produk']) ?>">
                        <button type="button" class="<?= $defaultSuhu === 'panas' ? 'active' : '' ?>" onclick="setSuhu(this,'panas')">Panas</button>
                        <button type="button" class="<?= $defaultSuhu === 'es' ? 'active' : '' ?>" onclick="setSuhu(this,'es')">Es<?= !empty($p['tambahan_harga_es']) ? ' +' . number_format($p['tambahan_harga_es'], 0, ',', '.') : '' ?></button>
                    </div>
                    <?php endif; ?>
                    <p style="margin:6px 0 12px;">
                        <?php if ($p['stok'] <= 0): ?>
                            <span class="badge-stok stok-habis size-stock-status">Habis</span>
                        <?php elseif ($p['stok'] <= 5): ?>
                            <span class="badge-stok stok-tipis size-stock-status"><?= (int) $p['stok'] ?> tersisa</span>
                        <?php else: ?>
                            <span class="badge-stok stok-aman size-stock-status"><?= (int) $p['stok'] ?></span>
                        <?php endif; ?>
                    </p>
                    <div class="btn-add-wrap">
                        <?php if ($menuGroup['is_size_group']): ?>
                        <button type="button"
                                class="btn btn-success btn-block btn-sm size-add-button"
                                data-product-id="<?= esc($p['id_produk']) ?>"
                                data-stock="<?= (int) $p['stok'] ?>"
                                data-size-label="<?= esc($selectedSizeLabel) ?>"
                                onclick="addSelectedSize(this)"
                                <?= $p['stok'] <= 0 ? 'disabled' : '' ?>>
                            <?= $p['stok'] > 0 ? 'Tambah ' . esc($selectedSizeLabel) : 'Stok Habis' ?>
                        </button>
                        <?php elseif ($p['stok'] > 0): ?>
                        <?php if (!empty($p['punya_opsi'])): ?>
                        <button type="button" class="btn btn-block btn-sm" onclick="openOptionSheet('<?= esc($p['id_produk']) ?>')">
                            <?= esc($p['label_opsi'] ?: 'Pilih varian') ?>
                        </button>
                        <?php else: ?>
                        <a href="<?= base_url('/cart/add/'.$p['id_produk'].'/'.$id_meja) ?><?= !empty($p['varian_suhu']) ? '?suhu='.$defaultSuhu : '' ?>"
                           class="btn btn-success btn-block btn-sm"
                           data-add-id="<?= esc($p['id_produk']) ?>"
                           onclick="showAddToast('<?= esc($p['nama']) ?>')">
                            + Tambah
                        </a>
                        <?php endif; ?>
                        <?php else: ?>
                        <button class="btn btn-block btn-sm" disabled>Stok Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

</div><!-- /container -->

<!-- ══ FLOATING CART BAR ══════════════════════════════════════ -->
<div class="floating-cart-bar" id="cartBar">
    <div class="cart-info">
        <div class="cart-count-badge" id="cartCountBadge">0</div>
        <span style="font-size:var(--fs-sm);">item di keranjang</span>
    </div>
    <a href="<?= base_url('/cart/'.$id_meja) ?>" class="cart-link">
        Lihat Keranjang →
    </a>
</div>

<!-- Toast for add-to-cart -->
<div class="add-toast" id="addToast"></div>
<div class="sheet-backdrop" id="groupSheet" onclick="closeGroupSheet(event)">
    <div class="bottom-sheet" onclick="event.stopPropagation()">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px;">
            <h3 id="groupSheetTitle" style="margin:0;">Pilih Menu</h3>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeGroupSheet()">Tutup</button>
        </div>
        <div id="groupSheetBody" style="margin-top:12px;"></div>
    </div>
</div>

<!-- ══ SCRIPTS ════════════════════════════════════════════════ -->
<script src="<?= base_url('assets/theme.js') ?>"></script>
<script>
window.variantProductData = <?= json_encode(
    array_column($products, null, 'id_produk'),
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
) ?>;
// ── LIVE REAL-TIME CLOCK (WIB Asia/Jakarta) & SEGMENT MANAGER ──
var liveClockInterval = null;
var lastActiveSegment = null;

var NAMA_HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
var NAMA_BULAN = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

/**
 * Format date in Asia/Jakarta (WIB) timezone:
 * Format exact: [hari]-[tanggal]-[bulan]-[tahun], [jam]:[menit].[detik]
 * Contoh output: "Selasa-13-Oktober-2026, 08:09.32"
 */
function formatWaktuWIB(date) {
    var parts = {};
    try {
        var dtf = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        dtf.formatToParts(date).forEach(function (p) {
            parts[p.type] = p.value;
        });
        
        // Capitalize hari & bulan
        var hari = parts.weekday ? parts.weekday.charAt(0).toUpperCase() + parts.weekday.slice(1) : '';
        var tgl = (parts.day || '').padStart(2, '0');
        var bln = parts.month ? parts.month.charAt(0).toUpperCase() + parts.month.slice(1) : '';
        var thn = parts.year || '';
        var jam = (parts.hour || '').padStart(2, '0');
        var mnt = (parts.minute || '').padStart(2, '0');
        var dtk = (parts.second || '').padStart(2, '0');

        return hari + '-' + tgl + '-' + bln + '-' + thn + ', ' + jam + ':' + mnt + '.' + dtk;
    } catch (e) {
        // Fallback manual UTC+7 calculation
        var utc = date.getTime() + (date.getTimezoneOffset() * 60000);
        var wib = new Date(utc + (7 * 3600000));
        var d = wib.getDay();
        var dd = String(wib.getDate()).padStart(2, '0');
        var mm = wib.getMonth();
        var yyyy = wib.getFullYear();
        var hh = String(wib.getHours()).padStart(2, '0');
        var ii = String(wib.getMinutes()).padStart(2, '0');
        var ss = String(wib.getSeconds()).padStart(2, '0');
        return NAMA_HARI[d] + '-' + dd + '-' + NAMA_BULAN[mm] + '-' + yyyy + ', ' + hh + ':' + ii + '.' + ss;
    }
}

/**
 * Dapatkan jam sekarang dalam zona WIB (0-23)
 */
function getJamWIB() {
    try {
        var dtf = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: 'numeric',
            hour12: false
        });
        return parseInt(dtf.format(new Date()), 10);
    } catch (e) {
        var utc = new Date().getTime() + (new Date().getTimezoneOffset() * 60000);
        var wib = new Date(utc + (7 * 3600000));
        return wib.getHours();
    }
}

function getMenitWIB() {
    try {
        return parseInt(new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            minute: 'numeric'
        }).format(new Date()), 10);
    } catch (e) {
        return new Date(Date.now() + (new Date().getTimezoneOffset() * 60000) + (7 * 3600000)).getMinutes();
    }
}

/**
 * Dapatkan kode segmen berdasarkan jam WIB
 */
function getSegmenByHour(h) {
    if (h >= 6 && h <= 10) return 'pagi';
    if (h >= 11 && h <= 14) return 'siang';
    if (h >= 15 && h <= 17) return 'sore';
    if (h >= 18 && h <= 20) return 'malam';
    return 'di_luar_operasional';
}

(function () {
    var ID_MEJA   = '<?= esc($id_meja) ?>';
    var BASE_URL  = '<?= base_url() ?>';
    var API_PATH  = '<?= rtrim((string) parse_url(base_url(), PHP_URL_PATH), '/') ?>/api/rekomendasi';
    var API_URL   = window.location.origin + API_PATH;

    var RENTANG_SEGMEN = {
        pagi:  'Pagi · 06.00–10.59',
        siang: 'Siang · 11.00–14.59',
        sore:  'Sore · 15.00–17.59',
        malam: 'Malam · 18.00–20.59',
        di_luar_operasional: 'Di luar jam operasional · 21.00–05.59'
    };

    var EMOJI_SEGMEN = {
        pagi: '🌅', siang: '☀️', sore: '🌤️', malam: '🌙', di_luar_operasional: '💤'
    };
    var lastRequestedMinute = null;

    function updateGreetingAndHeader(segmen) {
        var greeting = segmen === 'di_luar_operasional' ? 'Di luar jam operasional' : 'Selamat ' + segmen;
        var text = greeting;
        var emoji = EMOJI_SEGMEN[segmen] || '☕';
        var greetEl = document.getElementById('greetingText');
        if (greetEl) {
            greetEl.textContent = text + ' ' + emoji + ' — Silahkan pilih menu';
        }
    }

    function startLiveClock() {
        var clockEl = document.getElementById('liveClockText');
        if (!clockEl) return;

        function tick() {
            var now = new Date();
            clockEl.textContent = formatWaktuWIB(now);

            var currentSeg = getSegmenByHour(getJamWIB());
            var currentMinute = getJamWIB() + ':' + getMenitWIB();
            if (lastRequestedMinute !== null && currentMinute !== lastRequestedMinute) {
                loadRekomendasi();
            }
            lastRequestedMinute = currentMinute;
            if (lastActiveSegment !== null && currentSeg !== lastActiveSegment) {
                lastActiveSegment = currentSeg;
                var box = document.getElementById('rekomendasiBox');
                if (box) {
                    box.style.opacity = '0.5';
                    box.style.transition = 'opacity 0.3s ease';
                }
                loadRekomendasi();
            }
        }

        tick();
        liveClockInterval = setInterval(tick, 1000);
    }

    window.addEventListener('beforeunload', function () {
        if (liveClockInterval) {
            clearInterval(liveClockInterval);
            liveClockInterval = null;
        }
    });

    function formatRupiah(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    window.formatRupiah = formatRupiah;

    function getRecommendationPitch(item, segmen, suhu) {
        var segment = segmen || 'pagi';
        var productPitches = {
            PRD0000001: 'Aroma bumbu nasi goreng yang akrab langsung bikin waktu makan terasa lebih istimewa.',
            PRD0000002: 'Mie goreng berbumbu dengan telur, pilihan praktis saat ingin sajian gurih tanpa menunggu lama.',
            PRD0000003: 'Kuah hangat dan telur menjadikannya pilihan nyaman saat ingin santapan yang berkuah.',
            PRD0000004: 'Kacang hijau yang lembut dan rasa manisnya pas untuk jeda dari menu gurih.',
            PRD0000005: 'Porsi mungil yang seru dinikmati satu per satu, apalagi ditemani sate pilihan.',
            PRD0000006: 'Pancong hangat bertabur keju menghadirkan gurih yang pas untuk teman ngopi.',
            PRD0000007: 'Roti panggang dengan cokelat manis, camilan sederhana yang selalu menggoda saat baru disajikan.',
            PRD0000008: 'Kentang goreng renyah cocok jadi teman ngobrol atau camilan untuk dinikmati bersama.',
            PRD0000009: 'Bakwan gurih dengan tekstur renyah, paling nikmat disantap selagi hangat.',
            PRD0000010: 'Tempe goreng dengan rasa gurih yang akrab, pas untuk tambahan camilan atau lauk.',
            PRD0000011: 'Tahu goreng renyah di luar dan lembut di dalam, pilihan ringan untuk menemani rehat.',
            PRD0000012: 'Dimsum lembut berbalut saus mentai yang creamy untuk kamu yang ingin camilan lebih spesial.',
            PRD0000013: 'Sate telur dengan gigitan praktis dan rasa gurih, enak dipilih sebagai teman camilan.',
            PRD0000014: 'Sate usus berbumbu menawarkan rasa gurih khas yang bikin pilihan sate makin beragam.',
            PRD0000015: 'Sate ati ampela berbumbu kuat, pas untuk pencinta lauk dengan rasa yang lebih mantap.',
            PRD0000016: 'Cokelat malt Milo memberi rasa familiar yang nikmat, baik untuk teman rehat maupun makan.',
            PRD0000017: 'Rasa jeruknya memberi pilihan berbeda saat ingin minuman dengan sentuhan buah.',
            PRD0000018: 'Teh manis klasik dengan rasa yang akrab, mudah dipadukan dengan camilan atau makanan berat.',
            PRD0000019: 'Perpaduan teh dan lemon menghadirkan rasa segar dengan sentuhan asam yang ringan.',
            PRD0000021: 'Susu putih berkarakter lembut, pilihan sederhana saat ingin minuman yang tidak terlalu kuat rasanya.',
            PRD0000022: 'Air mineral jadi pendamping netral yang pas untuk melengkapi menu apa pun.',
            PRD0000023: 'Cappuccino memadukan kopi dan susu dalam rasa creamy untuk jeda ngopi yang lebih lembut.',
            PRD0000024: 'Kopi susu menyatukan pahit kopi dan lembut susu dalam satu gelas yang seimbang.',
            PRD0000025: 'Kopi hitam cocok untuk yang ingin menikmati karakter kopi yang tegas tanpa campuran.',
            PRD0000026: 'Nasi kuning berbumbu gurih memberi pilihan sarapan yang terasa lengkap dan memuaskan.',
            PRD0000027: 'Aroma nasi uduk yang gurih membuat menu ini pas untuk mengisi waktu makan dengan nikmat.',
            PRD0000028: 'Lontong sayur dengan kuah berbumbu menghadirkan kombinasi lembut dan gurih dalam satu sajian.',
            PRD0000029: 'Bubur ayam yang hangat dan gurih cocok saat ingin makan dengan tekstur lebih lembut.',
            PRD0000030: 'Ayam geprek dan nasi menyatukan sensasi pedas serta porsi mengenyangkan dalam satu pilihan.',
            PRD0000031: 'ABC Susu menawarkan racikan kopi instan yang praktis dengan rasa kopi-susu yang akrab.',
            PRD0000032: 'Indocafe punya rasa kopi yang lembut, cocok untuk teman jeda tanpa rasa yang terlalu kuat.',
            PRD0000033: 'Kapal Api Special Mix memadukan kopi dan gula untuk rasa klasik yang langsung siap dinikmati.',
            PRD0000034: 'Good Day Biasa memberi pilihan rasa kopi yang bisa disesuaikan dengan varian favoritmu.',
            PRD0000035: 'Luwak White Coffee berkarakter creamy dan lembut, pas untuk penikmat kopi yang ringan.',
            PRD0000036: 'Torabika Creamy Latte memadukan kopi dan krimer untuk sensasi latte yang halus.',
            PRD0000037: 'Good Day Freeze hadir dengan karakter kopi dingin dan pilihan rasa yang menarik untuk dicoba.',
            PRD0000038: 'Nescafe Ice Roast menyuguhkan karakter kopi yang terasa mantap dalam sajian dingin.',
            PRD0000039: 'ABC Klepon membawa sentuhan rasa klepon ke dalam kopi dingin yang unik dan manis.',
            PRD0000040: 'Good Day Biasa versi es memberi cara berbeda menikmati rasa favorit dalam sajian dingin.',
            PRD0000041: 'Tora Cafe dingin cocok untuk yang ingin kopi praktis dengan sensasi lebih menyegarkan.',
            PRD0000042: 'Es Nutrisari dengan pilihan rasa buah memudahkanmu memilih kesegaran sesuai selera.'
        };
        var groupPitches = {
            'minuman panas': 'Pilih racikan kopi atau non-kopi sesuai selera, semuanya tersaji hangat untuk menemani jeda.',
            'minuman dingin': 'Bandingkan kopi dingin dan minuman buah, lalu pilih kesegaran yang paling kamu inginkan.',
            'gorengan': 'Pilih camilan goreng favorit untuk menambah teman di meja tanpa perlu pesan satu jenis saja.',
            'nasi kucing & sate-satean': 'Padukan porsi nasi kecil dengan sate pilihan untuk menikmati beberapa rasa sekaligus.',
            'es segar': 'Ada pilihan teh, jeruk, dan minuman dingin lain untuk disesuaikan dengan selera.',
            'kopi & teh panas': 'Temukan racikan kopi atau teh hangat yang paling pas dengan suasana jedamu.'
        };
        var groupName = String(item.nama_kelompok || '').toLowerCase();
        var pitch = item.tipe === 'kelompok'
            ? groupPitches[groupName]
            : productPitches[item.id_produk];

        if (!pitch) {
            var productName = String(item.nama_kelompok || getProductDisplayName(item) || 'menu ini');
            pitch = 'Kenali karakter ' + productName + ' dari detail rasa dan sajian yang tertera, lalu pilih saat sesuai seleramu.';
        }

        var timeHooks = {
            pagi: [
                'Nikmati sebagai pembuka sebelum aktivitas mulai ramai.',
                'Pas dipilih saat suasana pagi masih santai.',
                'Jadikan jeda pagi lebih berkesan dengan pilihan ini.',
                'Teman yang pas sebelum melanjutkan rutinitas hari ini.'
            ],
            siang: [
                'Ambil jeda sejenak dan nikmati pilihan ini di tengah kesibukan.',
                'Cocok menemani waktu istirahat sebelum kembali beraktivitas.',
                'Beri warna pada waktu makan siang dengan sajian ini.',
                'Pilihan menarik untuk mengisi jeda di tengah hari.'
            ],
            sore: [
                'Nikmati perlahan saat waktunya mengambil rehat sore.',
                'Pas untuk menemani obrolan santai menjelang malam.',
                'Jadikan waktu santai sore punya rasa yang lebih istimewa.',
                'Teman yang cocok saat ingin berhenti sejenak dari aktivitas.'
            ],
            malam: [
                'Pilih untuk menemani waktu santai setelah aktivitas hari ini.',
                'Cocok dinikmati sambil menghabiskan waktu bersama teman.',
                'Lengkapi suasana malam dengan menu yang menggugah selera.',
                'Pas untuk menutup hari dengan jeda yang menyenangkan.'
            ]
        };
        var options = timeHooks[segment] || timeHooks.pagi;
        var idNumber = Number(String(item.id_produk || '').slice(-2));
        if (!idNumber) {
            idNumber = groupName.split('').reduce(function(total, character) {
                return total + character.charCodeAt(0);
            }, 0);
        }

        return pitch + ' ' + options[idNumber % options.length];
    }

    function getProductDisplayName(item) {
        return String(item.nama_kelompok || item.nama || '').replace(/\s*\((?:Sm|Rg|Lg)\)\s*$/i, '');
    }

    function getProductSizeLabel(item) {
        if (['Small', 'Regular', 'Large'].indexOf(item.ukuran_label) !== -1) return item.ukuran_label;
        var sizes = { Sm: 'Small', Rg: 'Regular', Lg: 'Large' };
        if (sizes[item.ukuran]) return sizes[item.ukuran];
        var match = String(item.nama || '').match(/\((Sm|Rg|Lg)\)\s*$/i);
        return match ? sizes[match[1].charAt(0).toUpperCase() + match[1].slice(1).toLowerCase()] : '';
    }

    window.renderSuhuToggle = function(id, suhu, tambahan, scope) {
        var tambahanText = Number(tambahan) > 0 ? ' + ' + Number(tambahan).toLocaleString('id-ID') : '';
        return '<div class="suhu-toggle" data-id="' + id + '">' +
            '<button type="button" class="' + (suhu === "panas" ? "active" : "") + '" onclick="setSuhu(this,\'panas\')">Panas</button>' +
            '<button type="button" class="' + (suhu === "es" ? "active" : "") + '" onclick="setSuhu(this,\'es\')">Es' + tambahanText + '</button>' +
            '</div>';
    };

    function renderRekomendasi(data) {
        window.recommendationLogId = data && data.log_id ? data.log_id : null;
        var grid = document.getElementById('rekomendasiGrid');
        var loading = document.getElementById('rekomendasiLoading');
        var emoji = document.getElementById('rekomendasiEmoji');
        var subtitle = document.getElementById('rekomendasiSubtitle');
        var methodBadge = document.getElementById('methodBadgeText');
        var box = document.getElementById('rekomendasiBox');

        if (!grid || !loading || !subtitle || !emoji) {
            return;
        }

        if (box) { box.style.opacity = '1'; }

        lastActiveSegment = data && data.segmen ? data.segmen : getSegmenByHour(getJamWIB());
        updateGreetingAndHeader(lastActiveSegment);

        emoji.textContent = EMOJI_SEGMEN[lastActiveSegment] || (data && data.emoji) || '🍽️';

        var crowdLabel = (data && data.label_keramaian) ? data.label_keramaian.toUpperCase() : 'SEDANG';
        var crowdEmoji = crowdLabel === 'RAMAI' ? '🔥 Lagi Ramai' : (crowdLabel === 'SEPI' ? '🍃 Lagi Santai' : '☕ Normal');
        var rentangText = (data && data.rentang_segmen) || RENTANG_SEGMEN[lastActiveSegment] || '';
        subtitle.textContent = (rentangText ? rentangText + ' · ' : '') + 'Rekomendasi menu pilihan · ' + crowdEmoji;

        var isKnn = (data && data.source && data.source.toLowerCase().indexOf('knn') !== -1);
        var isDataset = data && data.sumber_model === 'public_dataset';
        var srcLabel = isKnn ? 'KNN Waktu' : (isDataset ? 'Popularitas Dataset' : 'Rule-based');

        var badgeMl = document.querySelector('.badge-ml');
        if (badgeMl) {
            badgeMl.textContent = isKnn ? '✨ AI · KNN' : (isDataset ? '📊 DATASET' : '📋 RULE-BASED');
        }

        if (methodBadge) {
            var modelSrc = isDataset ? ' · Lower Manhattan 2023' : '';
            methodBadge.textContent = 'Metode: ' + srcLabel + modelSrc;
        }

        if (!data || !data.rekomendasi || data.rekomendasi.length === 0) {
            loading.innerHTML = '<p style="color:var(--taupe); text-align:center; padding:16px;">Tidak ada rekomendasi khusus untuk saat ini.</p>';
            loading.style.display = 'block';
            grid.style.display = 'none';
            return;
        }

        window.rekomendasiGroups = {};
        var html = '';
        data.rekomendasi.forEach(function (item, idx) {
            var suhuDefault = item.suhu_default || data.suhu_default || '';
            var addUrl = BASE_URL + 'cart/add/' + item.id_produk + '/' + ID_MEJA + (suhuDefault ? '?suhu=' + suhuDefault : '');
            var isHabis = (item.stok !== null && item.stok !== undefined && item.stok <= 0);
            var cardClass = 'rekomen-card reveal' + (isHabis ? ' rekomen-habis' : '');
            var imgUrl = item.gambar || (BASE_URL + 'assets/no-image.svg');
            var displayName = item.tipe === 'kelompok' ? (item.nama_kelompok || item.nama) : getProductDisplayName(item);
            var sizeLabel = item.tipe === 'kelompok' ? '' : getProductSizeLabel(item);

            html += '<div class="' + cardClass + '" style="--delay:' + (idx * 60) + 'ms;">';
            html += '  <div class="rank-badge">#' + (idx + 1) + '</div>';
            html += '  <div class="rekomen-img-wrap">';
            html += '    <img src="' + escapeMenuHtml(imgUrl) + '" alt="' + escapeMenuHtml(displayName) + '" loading="lazy" onerror="this.onerror=null;this.src=\'' + BASE_URL + 'assets/no-image.svg\';">';
            if (isHabis) {
                html += '    <span class="rekomen-oos-badge">Habis</span>';
            }
            html += '  </div>';
            html += '  <div class="rekomen-card-body">';
            html += '    <div class="rekomen-kategori-label">' + escapeMenuHtml(item.tipe === 'kelompok' ? 'Kelompok Menu' : (item.kategori_rekomendasi || item.kategori)) + '</div>';
            html += '    <h3 class="rekomen-nama" title="' + escapeMenuHtml(displayName) + '">' + escapeMenuHtml(displayName) + '</h3>';
            if (sizeLabel) html += '    <span class="rekomen-size-label">Ukuran: ' + escapeMenuHtml(sizeLabel) + '</span>';
            if (item.tipe !== 'kelompok' && item.deskripsi) {
                html += '    <p class="rekomen-description"><span class="rekomen-description-label">Kenali menunya</span>' + escapeMenuHtml(item.deskripsi) + '</p>';
            }
            html += '    <p class="rekomen-pitch">' + escapeMenuHtml(getRecommendationPitch(item, lastActiveSegment, suhuDefault)) + '</p>';
            if (item.tipe === 'kelompok') {
                var anggota = item.anggota || [];
                window.rekomendasiGroups['g' + idx] = item;
                if (anggota.some(function(a) { return a.subkategori; })) {
                    ['Kopi', 'Non Kopi'].forEach(function(subkategori) {
                        var pilihan = anggota.filter(function(a) { return a.subkategori === subkategori; });
                        if (!pilihan.length) return;
                        html += '    <div class="group-members"><strong>' + subkategori + ':</strong> ' + pilihan.map(function(a) {
                            return escapeMenuHtml(getProductDisplayName(a)) + (getProductSizeLabel(a) ? ' · ' + escapeMenuHtml(getProductSizeLabel(a)) : '') + ' (' + formatRupiah(Number(a.harga || 0)) + ')';
                        }).join(', ') + '</div>';
                    });
                } else {
                    html += '    <div class="group-members">' + anggota.map(function(a) {
                        return escapeMenuHtml(getProductDisplayName(a)) + (getProductSizeLabel(a) ? ' · ' + escapeMenuHtml(getProductSizeLabel(a)) : '') + ' (' + formatRupiah(Number(a.harga || 0)) + ')';
                    }).join(', ') + '</div>';
                }
            } else {
                window.variantProductData[item.id_produk] = item;
                html += '    <div class="rekomen-harga">' + formatRupiah(Number(item.harga || 0) + (suhuDefault === 'es' ? Number(item.tambahan_harga_es || 0) : 0)) + '</div>';
                if (Number(item.varian_suhu || 0) === 1) {
                    html += renderSuhuToggle(item.id_produk, suhuDefault || 'panas', item.tambahan_harga_es || 0, 'rek');
                }
                if (Number(item.punya_opsi || 0) === 1) {
                    html += '    <div class="group-members">' + getOptionLabel(item) + ' tersedia</div>';
                }
            }
            html += '    <div class="rekomen-btn-wrap">';
            if (item.tipe === 'kelompok') {
                html += '      <button class="rekomen-btn" type="button" onclick="openGroupSheet(\'g' + idx + '\')">Pilih Menu</button>';
            } else if (isHabis) {
                html += '      <button class="rekomen-btn" disabled>Habis</button>';
            } else if (Number(item.punya_opsi || 0) === 1) {
                html += '      <button class="rekomen-btn" type="button" onclick="openOptionSheet(\'' + item.id_produk + '\')">Pilih Varian</button>';
            } else {
                html += '      <a href="' + addUrl + '" class="rekomen-btn rekomen-btn-success" data-add-id="' + item.id_produk + '" onclick="showAddToast(\'' + item.nama.replace(/'/g, "\\'") + '\')">+ Tambah</a>';
            }
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
        });

        loading.style.display = 'none';
        grid.innerHTML = html;
        grid.style.display = (window.innerWidth <= 520) ? 'flex' : 'grid';

        setTimeout(function() {
            grid.querySelectorAll('.reveal').forEach(function(el, i) {
                setTimeout(function() {
                    el.classList.add('is-visible');
                }, i * 60);
            });
        }, 80);
    }

    function loadRekomendasi() {
        var requestUrl = API_URL + '?jam=' + getJamWIB() + '&menit=' + getMenitWIB();
        fetch(requestUrl, { cache: 'no-store' })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('Recommendation request failed with status ' + res.status);
                }
                return res.json();
            })
            .then(function (json) {
                if (json && json.status === 'success') {
                    renderRekomendasi(json.data);
                } else {
                    var loading = document.getElementById('rekomendasiLoading');
                    if (loading) {
                        loading.innerHTML = '<p style="color:var(--taupe);font-size:var(--fs-sm); text-align:center;">Rekomendasi tidak tersedia saat ini.</p>';
                    }
                }
            })
            .catch(function (error) {
                console.error('Gagal memuat rekomendasi Lower Manhattan:', error);
                var loading = document.getElementById('rekomendasiLoading');
                if (loading) {
                    loading.innerHTML = '<div style="text-align:center;padding:16px;"><p style="color:var(--taupe);font-size:var(--fs-sm);">Rekomendasi Lower Manhattan belum dapat dimuat.</p><button type="button" class="btn btn-secondary btn-sm" onclick="loadRekomendasi()">Coba lagi</button></div>';
                }
            });
    }

    window.renderRekomendasi = renderRekomendasi;
    window.loadRekomendasi = loadRekomendasi;

    function initRekomendasiWidget() {
        startLiveClock();
        loadRekomendasi();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRekomendasiWidget);
    } else {
        initRekomendasiWidget();
    }

})();

// ── Category Filter ──
var activeMenuCategory = 'all';

function filterCategory(kat, btn) {
    document.querySelectorAll('#categoryChips .chip').forEach(function(c) { c.classList.remove('active'); });
    btn.classList.add('active');
    activeMenuCategory = kat;
    applyMenuFilters();
}

function applyMenuFilters() {
    var query = document.getElementById('searchMenu').value.toLowerCase();

    document.querySelectorAll('.kategori-section').forEach(function(section) {
        var visibleCount = 0;
        section.querySelectorAll('.product-card').forEach(function(card) {
            var matchesCategory = activeMenuCategory === 'all' || section.dataset.kategori === activeMenuCategory;
            var matchesQuery = (card.dataset.nama || '').includes(query);
            var visible = matchesCategory && matchesQuery;
            card.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        section.style.display = visibleCount ? '' : 'none';
    });
}

// ── Search Filter ──
function filterMenu() {
    applyMenuFilters();
}

function selectProductSize(button) {
    var card = button.closest('.product-card');
    if (!card) return;

    card.querySelectorAll('.size-choice').forEach(function(choice) {
        var selected = choice === button;
        choice.classList.toggle('active', selected);
        choice.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });

    var price = Number(button.dataset.price || 0);
    var stock = Number(button.dataset.stock || 0);
    var productId = button.dataset.productId;
    var sizeLabel = button.dataset.sizeLabel || 'Regular';
    var priceElement = card.querySelector('.card-price');
    var stockElement = card.querySelector('.size-stock-status');
    var addButton = card.querySelector('.size-add-button');
    var outOfStockLabel = card.querySelector('.oos-label');

    if (priceElement) priceElement.textContent = 'Rp ' + price.toLocaleString('id-ID');
    if (stockElement) {
        stockElement.classList.remove('stok-habis', 'stok-tipis', 'stok-aman');
        if (stock <= 0) {
            stockElement.textContent = 'Habis';
            stockElement.classList.add('stok-habis');
        } else if (stock <= 5) {
            stockElement.textContent = stock + ' tersisa';
            stockElement.classList.add('stok-tipis');
        } else {
            stockElement.textContent = String(stock);
            stockElement.classList.add('stok-aman');
        }
    }

    card.classList.toggle('out-of-stock', stock <= 0);
    if (outOfStockLabel) outOfStockLabel.style.display = stock <= 0 ? '' : 'none';
    if (addButton) {
        addButton.dataset.productId = productId;
        addButton.dataset.stock = String(stock);
        addButton.dataset.sizeLabel = sizeLabel;
        addButton.disabled = stock <= 0;
        addButton.textContent = stock > 0 ? 'Tambah ' + sizeLabel : 'Stok Habis';
    }
}

function addSelectedSize(button) {
    var productId = button.dataset.productId;
    if (!productId || Number(button.dataset.stock || 0) <= 0) return;

    window.location.href = '<?= base_url('/cart/add/') ?>' +
        encodeURIComponent(productId) + '/<?= esc($id_meja) ?>';
}

function setSuhu(btn, suhu) {
    var wrap = btn.closest('.suhu-toggle');
    if (!wrap) return;
    wrap.querySelectorAll('button').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    var id = wrap.dataset.id;
    document.querySelectorAll('[data-add-id="' + id + '"]').forEach(function(link) {
        var base = link.href.split('?')[0];
        link.href = base + '?suhu=' + suhu;
    });
    var card = wrap.closest('.product-card');
    if (card) {
        var priceEl = card.querySelector('.card-price');
        if (priceEl) {
            var basePrice = Number(priceEl.dataset.price || 0);
            var es = Number(priceEl.dataset.es || 0);
            priceEl.textContent = 'Rp ' + (basePrice + (suhu === 'es' ? es : 0)).toLocaleString('id-ID');
        }
    }
}

function setCatalogSuhu(card, suhu) {
    var wrap = card.querySelector('.suhu-toggle');
    if (!wrap) return;
    var buttons = wrap.querySelectorAll('button');
    buttons.forEach(function(button, index) {
        button.classList.toggle('active', index === (suhu === 'panas' ? 0 : 1));
    });
    var link = card.querySelector('[data-add-id]');
    if (link) link.href = link.href.split('?')[0] + '?suhu=' + suhu;
    var priceEl = card.querySelector('.card-price');
    if (priceEl) {
        var basePrice = Number(priceEl.dataset.price || 0);
        var es = Number(priceEl.dataset.es || 0);
        priceEl.textContent = 'Rp ' + (basePrice + (suhu === 'es' ? es : 0)).toLocaleString('id-ID');
    }
}

function openGroupSheet(key) {
    var data = window.rekomendasiGroups && window.rekomendasiGroups[key];
    if (!data) return;
    document.getElementById('groupSheetTitle').textContent = data.nama_kelompok || 'Pilih Menu';
    var body = document.getElementById('groupSheetBody');
    var html = '';
    var anggota = data.anggota || [];
    var subkategoriAktif = '';
    anggota.forEach(function(item) {
        if (item.subkategori && item.subkategori !== subkategoriAktif) {
            subkategoriAktif = item.subkategori;
            html += '<h4 style="margin:16px 0 4px;color:var(--espresso);">' + escapeMenuHtml(subkategoriAktif) + '</h4>';
        }
        window.variantProductData[item.id_produk] = item;
        var suhu = data.suhu_default || item.suhu_default || '';
        var addUrl = '<?= base_url() ?>cart/add/' + item.id_produk + '/<?= esc($id_meja) ?>' + (suhu ? '?suhu=' + suhu : '');
        var displayName = getProductDisplayName(item);
        var sizeLabel = getProductSizeLabel(item);
        html += '<div class="sheet-row">';
        html += '<img src="' + escapeMenuHtml(item.gambar || '<?= base_url('assets/no-image.svg') ?>') + '" onerror="this.src=&quot;<?= base_url('assets/no-image.svg') ?>&quot;">';
        html += '<div><strong>' + escapeMenuHtml(displayName) + '</strong>' + (sizeLabel ? '<div style="font-size:12px;color:var(--mocha);font-weight:700;">Ukuran: ' + escapeMenuHtml(sizeLabel) + '</div>' : '') + '<div style="font-size:12px;color:var(--taupe);">' + escapeMenuHtml(item.kategori) + ' - ' + window.formatRupiah(Number(item.harga || 0)) + '</div>';
        if (item.deskripsi) html += '<div style="font-size:12px;color:var(--taupe);margin-top:4px;">' + escapeMenuHtml(item.deskripsi) + '</div>';
        if (Number(item.varian_suhu || 0) === 1) html += renderSuhuToggle(item.id_produk, suhu || 'panas', item.tambahan_harga_es || 0, 'sheet');
        html += '</div>';
        if (item.stok <= 0) {
            html += '<button class="btn btn-sm" disabled>Habis</button>';
        } else if (Number(item.punya_opsi || 0) === 1) {
            html += '<button class="btn btn-sm" type="button" onclick="openOptionSheet(\'' + item.id_produk + '\')">Pilih Varian</button>';
        } else {
            html += '<a class="btn btn-success btn-sm" data-add-id="' + item.id_produk + '" href="' + addUrl + '" onclick="showAddToast(' + escapeMenuHtml(JSON.stringify(String(item.nama))) + ')">+ Tambah</a>';
        }
        html += '</div>';
    });
    body.innerHTML = html;
    document.getElementById('groupSheet').classList.add('show');
}

function openOptionSheet(productId) {
    var product = window.variantProductData && window.variantProductData[productId];
    if (!product || !Array.isArray(product.options) || product.options.length === 0) {
        return;
    }

    var title = document.getElementById('groupSheetTitle');
    var body = document.getElementById('groupSheetBody');
    var optionLabel = getOptionLabel(product);
    title.textContent = optionLabel;

    var selectedOption = null;
    var options = product.options.filter(function(option) {
        return Number(option.aktif === undefined ? 1 : option.aktif) === 1;
    });
    var html = '<p style="margin:4px 0 12px;color:var(--taupe);">' + escapeMenuHtml(getProductDisplayName(product)) +
        (getProductSizeLabel(product) ? ' — Ukuran: ' + escapeMenuHtml(getProductSizeLabel(product)) : '') +
        ' — ' + (product.suhu_tetap === 'dingin' ? 'Es' : 'Panas') + '</p>';
    options.forEach(function(option) {
        html += '<button type="button" class="option-choice" data-option-id="' + option.id_opsi + '">' +
            '<span><strong>' + escapeMenuHtml(option.nama_opsi) + '</strong></span>' +
            '<span>' + (Number(option.tambahan_harga || 0) > 0 ? '+ ' + window.formatRupiah(Number(option.tambahan_harga)) : '') + '</span>' +
            '</button>';
    });
    html += '<label style="display:block;margin-top:16px;font-weight:600;">Jumlah ' +
        '<input id="variantQty" type="number" min="1" max="' + Math.max(1, Number(product.stok || 1)) + '" value="1" style="width:80px;margin-left:8px;padding:6px;border:1px solid var(--sand);border-radius:8px;"></label>';
    html += '<button type="button" id="addVariantButton" class="btn btn-block" disabled style="margin-top:14px;opacity:.55;">' + optionLabel + ' terlebih dahulu</button>';
    body.innerHTML = html;

    body.querySelectorAll('.option-choice').forEach(function(button) {
        button.addEventListener('click', function() {
            body.querySelectorAll('.option-choice').forEach(function(choice) {
                choice.classList.remove('selected');
            });
            button.classList.add('selected');
            selectedOption = button.dataset.optionId;
            var submit = document.getElementById('addVariantButton');
            submit.disabled = false;
            submit.style.opacity = '1';
            submit.textContent = '+ Tambah ' + product.nama;
        });
    });

    document.getElementById('addVariantButton').addEventListener('click', function() {
        if (!selectedOption) return;
        var quantity = Math.max(1, Number(document.getElementById('variantQty').value || 1));
        quantity = Math.min(quantity, Number(product.stok || 1), 50);
        var url = '<?= base_url() ?>cart/add/' + encodeURIComponent(productId) + '/<?= esc($id_meja) ?>?opsi=' +
            encodeURIComponent(selectedOption) + '&qty=' + quantity;
        window.location.href = url;
    });

    document.getElementById('groupSheet').classList.add('show');
}

function getOptionLabel(product) {
    if (['Bakery', 'Branded', 'Coffee beans', 'Flavours', 'Loose Tea', 'Packaged Chocolate'].indexOf(product.kategori_dataset || product.kategori) !== -1) {
        return 'Pilih varian';
    }
    return product.label_opsi || 'Pilih varian';
}

function escapeMenuHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function(character) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character];
    });
}

function closeGroupSheet(event) {
    if (event && event.target && event.target.id !== 'groupSheet') return;
    document.getElementById('groupSheet').classList.remove('show');
}

// ── Add to cart toast ──
function showAddToast(name) {
    var toast = document.getElementById('addToast');
    if (!toast) return;
    toast.textContent = '✓ ' + name + ' ditambahkan ke keranjang';
    toast.classList.add('show');
    setTimeout(function() { toast.classList.remove('show'); }, 2000);
}

// ── Show cart bar ──
document.addEventListener('DOMContentLoaded', function() {
    var bar = document.getElementById('cartBar');
    if (bar) {
        setTimeout(function() { bar.classList.add('show'); }, 500);
    }
});
</script>
<script>
(function() {
    var renderRecommendations = window.renderRekomendasi;
    if (typeof renderRecommendations === 'function') {
        window.renderRekomendasi = function(data) {
            window.recommendationLogId = data && data.log_id ? data.log_id : null;
            return renderRecommendations(data);
        };
    }

    var activeRecommendationProductId = null;
    var activeRecommendationLogId = null;
    function sendRecommendationSelection(productId, logId) {
        if (!logId || !productId) return;
        var token = document.querySelector('#recommendationLogCsrf input[type="hidden"]');
        if (!token) return;
        var body = new URLSearchParams();
        body.set('log_id', String(logId));
        body.set('id_produk', String(productId));
        body.set(token.name, token.value);
        fetch('<?= base_url('recommendation-log/select') ?>', {
            method: 'POST', credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body.toString(), keepalive: true
        }).catch(function() {});
    }

    document.addEventListener('click', function(event) {
        var picker = event.target.closest && event.target.closest('#rekomendasiGrid button[onclick^="openOptionSheet("], #groupSheetBody button[onclick^="openOptionSheet("]');
        if (picker) {
            var match = (picker.getAttribute('onclick') || '').match(/openOptionSheet\('([A-Za-z0-9_-]{1,32})'\)/);
            if (match) {
                activeRecommendationProductId = match[1];
                activeRecommendationLogId = window.recommendationLogId;
            }
        }

        var link = event.target.closest && event.target.closest('#rekomendasiGrid a[data-add-id], #groupSheetBody a[data-add-id]');
        if (link) sendRecommendationSelection(link.dataset.addId, window.recommendationLogId);
        if (event.target.closest && event.target.closest('#addVariantButton')) {
            sendRecommendationSelection(activeRecommendationProductId, activeRecommendationLogId);
        }
    }, true);
})();
</script>

</body>
</html>

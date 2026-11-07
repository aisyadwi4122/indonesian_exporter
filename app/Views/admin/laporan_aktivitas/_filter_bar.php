<?php
/**
 * Partial: _filter_bar.php
 * Parameter yang diterima:
 * - $pageTitle (string)
 * - $pageSubtitle (string)
 * - $filters (array)
 * - $exportUrl (string)
 * - $showMode (bool, default false)
 * - $showSales (bool, default false)
 * - $showProduct (bool, default false)
 * - $showJenis (bool, default false)
 * - $salesList (array, optional)
 * - $productList (array, optional)
 */

$showMode    = $showMode ?? false;
$showSales   = $showSales ?? false;
$showProduct = $showProduct ?? false;
$showJenis   = $showJenis ?? false;
$salesList   = $salesList ?? [];
$productList = $productList ?? [];

// Hitung query export (mempertahankan semua filter tanpa parameter page)
$exportParams = [
    'start_date' => $filters['start_date'],
    'end_date'   => $filters['end_date'],
];
if ($showMode && !empty($filters['mode'])) {
    $exportParams['mode'] = $filters['mode'];
}
if ($showSales && !empty($filters['sales_id'])) {
    $exportParams['sales_id'] = $filters['sales_id'];
}
if ($showProduct && !empty($filters['product_id'])) {
    $exportParams['product_id'] = $filters['product_id'];
}
if ($showJenis && !empty($filters['jenis'])) {
    $exportParams['jenis'] = $filters['jenis'];
}
$exportQueryString = http_build_query($exportParams);

// Cek apakah ada filter yang aktif untuk tombol Reset
$hasActiveFilter = (!empty($filters['mode']) && $filters['mode'] !== 'semua')
    || (!empty($filters['sales_id']) && $filters['sales_id'] !== 'semua')
    || (!empty($filters['product_id']) && $filters['product_id'] !== 'semua')
    || (!empty($filters['jenis']) && $filters['jenis'] !== 'semua');
?>

<!-- Header: Judul & Rentang Tanggal -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="app-page-title mb-1 fw-bold fs-4 text-dark"><?= esc($pageTitle) ?></h1>
        <p class="text-muted small mb-0"><?= esc($pageSubtitle) ?></p>
    </div>
    <div>
        <form method="GET" action="<?= current_url() ?>" class="d-flex align-items-center gap-2">
            <?php if ($showMode && !empty($filters['mode'])): ?>
                <input type="hidden" name="mode" value="<?= esc($filters['mode']) ?>">
            <?php endif; ?>
            <?php if ($showSales && !empty($filters['sales_id'])): ?>
                <input type="hidden" name="sales_id" value="<?= esc($filters['sales_id']) ?>">
            <?php endif; ?>
            <?php if ($showProduct && !empty($filters['product_id'])): ?>
                <input type="hidden" name="product_id" value="<?= esc($filters['product_id']) ?>">
            <?php endif; ?>
            <?php if ($showJenis && !empty($filters['jenis'])): ?>
                <input type="hidden" name="jenis" value="<?= esc($filters['jenis']) ?>">
            <?php endif; ?>
            <div class="input-group input-group-sm shadow-sm">
                <span class="input-group-text bg-white text-muted"><i class="fa-regular fa-calendar"></i></span>
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($filters['start_date']) ?>">
                <span class="input-group-text bg-white text-muted">-</span>
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($filters['end_date']) ?>">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Terapkan</button>
            </div>
        </form>
    </div>
</div>

<!-- Filter Bar Card -->
<div class="app-card app-card-settings shadow-sm p-3 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3">
        <form method="GET" action="<?= current_url() ?>" class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 flex-grow-1 flex-wrap">
            <input type="hidden" name="start_date" value="<?= esc($filters['start_date']) ?>">
            <input type="hidden" name="end_date" value="<?= esc($filters['end_date']) ?>">

            <!-- Filter Mode -->
            <?php if ($showMode): ?>
                <div style="min-width: 170px;">
                    <select name="mode" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" <?= empty($filters['mode']) ? 'selected' : '' ?>>Semua Mode</option>
                        <option value="internal" <?= ($filters['mode'] === 'internal') ? 'selected' : '' ?>>Mode: Internal</option>
                        <option value="eksternal" <?= ($filters['mode'] === 'eksternal') ? 'selected' : '' ?>>Pratinjau Eksternal</option>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Filter Sales -->
            <?php if ($showSales): ?>
                <div style="min-width: 180px;">
                    <select name="sales_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" <?= empty($filters['sales_id']) ? 'selected' : '' ?>>Semua Sales</option>
                        <?php foreach ($salesList as $s): ?>
                            <option value="<?= esc($s['user_id']) ?>" <?= ((string)$filters['sales_id'] === (string)$s['user_id']) ? 'selected' : '' ?>>
                                <?= esc($s['nama_sales']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Filter Produk -->
            <?php if ($showProduct): ?>
                <div style="min-width: 220px; max-width: 320px;">
                    <select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" <?= empty($filters['product_id']) ? 'selected' : '' ?>>Semua Produk</option>
                        <?php foreach ($productList as $p): ?>
                            <option value="<?= esc($p['product_id']) ?>" <?= ((string)$filters['product_id'] === (string)$p['product_id']) ? 'selected' : '' ?>>
                                <?= esc($p['nama_produk']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- Filter Jenis Aktivitas -->
            <?php if ($showJenis): ?>
                <div style="min-width: 170px;">
                    <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" <?= empty($filters['jenis']) ? 'selected' : '' ?>>Semua Aktivitas</option>
                        <option value="login" <?= ($filters['jenis'] === 'login') ? 'selected' : '' ?>>Login</option>
                        <option value="pencarian" <?= ($filters['jenis'] === 'pencarian') ? 'selected' : '' ?>>Pencarian</option>
                        <option value="detail" <?= ($filters['jenis'] === 'detail') ? 'selected' : '' ?>>Klik Detail</option>
                        <option value="komisi" <?= ($filters['jenis'] === 'komisi') ? 'selected' : '' ?>>Klik Info Komisi</option>
                        <option value="pdf" <?= ($filters['jenis'] === 'pdf') ? 'selected' : '' ?>>Cetak PDF</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-secondary text-white">Filter</button>
                <?php if ($hasActiveFilter): ?>
                    <a href="<?= current_url() . '?start_date=' . urlencode($filters['start_date']) . '&end_date=' . urlencode($filters['end_date']) ?>" class="btn btn-sm btn-light border">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Tombol Export CSV -->
        <div>
            <a href="<?= esc($exportUrl) . '?' . $exportQueryString ?>" class="btn btn-sm btn-primary text-white d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-download"></i> Export
            </a>
        </div>
    </div>
</div>

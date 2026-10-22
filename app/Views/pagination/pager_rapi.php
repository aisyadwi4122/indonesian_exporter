<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */

// Tampilkan maksimal sekitar 5 nomor di sekitar halaman aktif
$pager->setSurroundCount(2);

$currentPage = $pager->getCurrentPageNumber();
$pageCount   = $pager->getPageCount();
$isFirst     = ($currentPage <= 1);
$isLast      = ($currentPage >= $pageCount);
?>

<style>
.pager-rapi-nav {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    margin: 1.25rem 0 0.5rem;
}
.pager-rapi {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 6px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.pager-rapi .pager-item {
    display: inline-flex;
}
.pager-rapi .pager-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 10px;
    font-size: 13.5px;
    font-weight: 500;
    line-height: 1;
    text-decoration: none;
    color: #334155;
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    transition: all 0.18s ease-in-out;
    cursor: pointer;
    user-select: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.pager-rapi .pager-link:hover {
    color: #15803d;
    background-color: #f0fdf4;
    border-color: #86efac;
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(22, 163, 74, 0.15);
}
.pager-rapi .pager-item.active .pager-link {
    color: #ffffff !important;
    background-color: #16a34a !important; /* Tema hijau sales */
    border-color: #16a34a !important;
    font-weight: 600;
    cursor: default;
    pointer-events: none;
    box-shadow: 0 2px 6px rgba(22, 163, 74, 0.35);
}
.pager-rapi .pager-item.disabled .pager-link {
    color: #94a3b8 !important;
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
    cursor: not-allowed;
    pointer-events: none;
    box-shadow: none;
    opacity: 0.6;
}
.pager-rapi i {
    font-style: normal;
    font-size: 12px;
}
@media (max-width: 575.98px) {
    .pager-rapi {
        gap: 4px;
    }
    .pager-rapi .pager-link {
        min-width: 34px;
        height: 34px;
        font-size: 12px;
        padding: 0 8px;
    }
}
</style>

<nav aria-label="Navigasi halaman" class="pager-rapi-nav">
    <ul class="pager-rapi">
        <!-- 1. Halaman Pertama («) -->
        <?php if (! $isFirst) : ?>
            <li class="pager-item">
                <a href="<?= $pager->getFirst() ?>" class="pager-link" aria-label="Halaman pertama" title="Halaman pertama">
                    <i class="fa-solid fa-angles-left" aria-hidden="true">&laquo;</i>
                </a>
            </li>
        <?php else : ?>
            <li class="pager-item disabled" aria-disabled="true">
                <span class="pager-link" aria-label="Halaman pertama (nonaktif)" title="Halaman pertama (nonaktif)">
                    <i class="fa-solid fa-angles-left" aria-hidden="true">&laquo;</i>
                </span>
            </li>
        <?php endif ?>

        <!-- 2. Halaman Sebelumnya (‹) -->
        <?php if (! $isFirst) : ?>
            <li class="pager-item">
                <a href="<?= $pager->getPreviousPage() ?? $pager->getFirst() ?>" class="pager-link" aria-label="Halaman sebelumnya" title="Halaman sebelumnya">
                    <i class="fa-solid fa-angle-left" aria-hidden="true">&lsaquo;</i>
                </a>
            </li>
        <?php else : ?>
            <li class="pager-item disabled" aria-disabled="true">
                <span class="pager-link" aria-label="Halaman sebelumnya (nonaktif)" title="Halaman sebelumnya (nonaktif)">
                    <i class="fa-solid fa-angle-left" aria-hidden="true">&lsaquo;</i>
                </span>
            </li>
        <?php endif ?>

        <!-- 3. Nomor Halaman (Sekitar Halaman Aktif) -->
        <?php foreach ($pager->links() as $link) : ?>
            <?php if ($link['active']) : ?>
                <li class="pager-item active" aria-current="page">
                    <span class="pager-link" aria-label="Halaman <?= $link['title'] ?>" title="Halaman <?= $link['title'] ?> (aktif)">
                        <?= $link['title'] ?>
                    </span>
                </li>
            <?php else : ?>
                <li class="pager-item">
                    <a href="<?= $link['uri'] ?>" class="pager-link" aria-label="Halaman <?= $link['title'] ?>" title="Halaman <?= $link['title'] ?>">
                        <?= $link['title'] ?>
                    </a>
                </li>
            <?php endif ?>
        <?php endforeach ?>

        <!-- 4. Halaman Berikutnya (›) -->
        <?php if (! $isLast) : ?>
            <li class="pager-item">
                <a href="<?= $pager->getNextPage() ?? $pager->getLast() ?>" class="pager-link" aria-label="Halaman berikutnya" title="Halaman berikutnya">
                    <i class="fa-solid fa-angle-right" aria-hidden="true">&rsaquo;</i>
                </a>
            </li>
        <?php else : ?>
            <li class="pager-item disabled" aria-disabled="true">
                <span class="pager-link" aria-label="Halaman berikutnya (nonaktif)" title="Halaman berikutnya (nonaktif)">
                    <i class="fa-solid fa-angle-right" aria-hidden="true">&rsaquo;</i>
                </span>
            </li>
        <?php endif ?>

        <!-- 5. Halaman Terakhir (») -->
        <?php if (! $isLast) : ?>
            <li class="pager-item">
                <a href="<?= $pager->getLast() ?>" class="pager-link" aria-label="Halaman terakhir" title="Halaman terakhir">
                    <i class="fa-solid fa-angles-right" aria-hidden="true">&raquo;</i>
                </a>
            </li>
        <?php else : ?>
            <li class="pager-item disabled" aria-disabled="true">
                <span class="pager-link" aria-label="Halaman terakhir (nonaktif)" title="Halaman terakhir (nonaktif)">
                    <i class="fa-solid fa-angles-right" aria-hidden="true">&raquo;</i>
                </span>
            </li>
        <?php endif ?>
    </ul>
</nav>

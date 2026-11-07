<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-0">Manajemen Akun</h1>
                <p class="text-muted small mb-0">Kelola akun Sales dan Supplier dalam satu halaman terpadu.</p>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/akun/tambah' . ($tipe_filter !== 'all' ? '?tipe=' . esc($tipe_filter) : '')) ?>" class="btn btn-primary text-white">
                    <i class="fa-solid fa-user-plus me-1"></i> Tambah Akun
                </a>
            </div>
        </div><!--//row-->

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?= esc(session()->getFlashdata('success')) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php $createdAccount = session()->getFlashdata('created_account'); ?>
        <?php if (!empty($createdAccount)) : ?>
            <div class="card border-success shadow-sm mb-4">
                <div class="card-header bg-success text-white d-flex align-items-center justify-content-between py-2">
                    <div>
                        <i class="fa-solid fa-key me-2"></i><strong>Akun Baru Berhasil Dibuat</strong>
                    </div>
                    <span class="badge bg-light text-dark fw-bold"><?= esc($createdAccount['tipe_label']) ?></span>
                </div>
                <div class="card-body">
                    <p class="card-text mb-2">
                        Akun <strong><?= esc($createdAccount['username']) ?></strong> telah berhasil dibuat. Silakan salin password berikut untuk diberikan kepada pengguna:
                    </p>
                    <div class="alert alert-warning py-2 px-3 mb-3 small">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        <strong>Perhatian:</strong> Password ini hanya ditampilkan sekali. Simpan sekarang karena tidak dapat dilihat kembali setelah halaman ini dimuat ulang.
                    </div>
                    <div class="input-group" style="max-width: 480px;">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="text" id="createdPasswordInput" class="form-control font-monospace fw-bold fs-6 text-primary bg-light" value="<?= esc($createdAccount['password']) ?>" readonly>
                        <button class="btn btn-outline-success" type="button" id="btnCopyPassword" onclick="copyCreatedPassword()">
                            <i class="fa-solid fa-copy me-1"></i> <span id="copyBtnText">Salin</span>
                        </button>
                    </div>
                </div>
            </div>
            <script>
                function copyCreatedPassword() {
                    var input = document.getElementById('createdPasswordInput');
                    if (!input) return;
                    navigator.clipboard.writeText(input.value).then(function() {
                        var btnText = document.getElementById('copyBtnText');
                        if (btnText) {
                            btnText.innerText = 'Tersalin!';
                            setTimeout(function() {
                                btnText.innerText = 'Salin';
                            }, 2500);
                        }
                    }).catch(function() {
                        input.select();
                        document.execCommand('copy');
                        var btnText = document.getElementById('copyBtnText');
                        if (btnText) {
                            btnText.innerText = 'Tersalin!';
                            setTimeout(function() {
                                btnText.innerText = 'Salin';
                            }, 2500);
                        }
                    });
                }
            </script>
        <?php endif; ?>

        <!-- Nav Pills Filter Tipe & Search Bar -->
        <div class="row g-3 mb-3 align-items-center justify-content-between">
            <div class="col-12 col-md-auto">
                <ul class="nav nav-pills">
                    <li class="nav-item">
                        <a class="nav-link <?= $tipe_filter === 'all' ? 'active' : '' ?>" 
                           href="<?= base_url('admin/akun/index?tipe=all' . ($status_filter !== 'all' ? '&status=' . esc($status_filter) : '') . (($akses_filter ?? 'all') !== 'all' ? '&akses=' . esc($akses_filter) : '') . ($search_query !== '' ? '&q=' . urlencode($search_query) : '')) ?>">
                            <i class="fa-solid fa-users me-1"></i> Semua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tipe_filter === 'sales' ? 'active' : '' ?>" 
                           href="<?= base_url('admin/akun/index?tipe=sales' . ($status_filter !== 'all' ? '&status=' . esc($status_filter) : '') . (($akses_filter ?? 'all') !== 'all' ? '&akses=' . esc($akses_filter) : '') . ($search_query !== '' ? '&q=' . urlencode($search_query) : '')) ?>">
                            <i class="fa-solid fa-user-tie me-1"></i> Sales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $tipe_filter === 'supplier' ? 'active' : '' ?>" 
                           href="<?= base_url('admin/akun/index?tipe=supplier' . ($status_filter !== 'all' ? '&status=' . esc($status_filter) : '') . (($akses_filter ?? 'all') !== 'all' ? '&akses=' . esc($akses_filter) : '') . ($search_query !== '' ? '&q=' . urlencode($search_query) : '')) ?>">
                            <i class="fa-solid fa-truck-field me-1"></i> Supplier
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-12 col-md-auto">
                <form action="<?= base_url('admin/akun/index') ?>" method="GET" class="row g-2 align-items-center">
                    <input type="hidden" name="tipe" value="<?= esc($tipe_filter) ?>">
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="aktif" <?= $status_filter === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $status_filter === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="akses" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all" <?= ($akses_filter ?? 'all') === 'all' ? 'selected' : '' ?>>Semua Akses</option>
                            <option value="internal" <?= ($akses_filter ?? '') === 'internal' ? 'selected' : '' ?>>Akses Internal</option>
                            <option value="eksternal" <?= ($akses_filter ?? '') === 'eksternal' ? 'selected' : '' ?>>Akses Eksternal</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <input type="text" 
                                   name="q" 
                                   class="form-control" 
                                   placeholder="Cari user, nama, perusahaan..." 
                                   value="<?= esc($search_query) ?>">
                            <button class="btn btn-primary text-white" type="submit" title="Cari">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>
                            <?php if ($search_query !== '' || $status_filter !== 'all' || ($akses_filter ?? 'all') !== 'all') : ?>
                                <a href="<?= base_url('admin/akun/index?tipe=' . esc($tipe_filter)) ?>" class="btn btn-outline-secondary" title="Reset Filter">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="app-card app-card-orders-table shadow-sm mb-4">
            <div class="app-card-body">
                <div class="table-responsive">
                    <table class="table app-table-hover mb-0 text-left">
                        <thead>
                            <tr>
                                <th class="cell" style="width: 50px;">No</th>
                                <th class="cell">Username</th>
                                <th class="cell">Nama</th>
                                <th class="cell text-center" style="width: 90px;">Tipe</th>
                                <th class="cell text-center" style="width: 110px;">Tipe Akses</th>
                                <th class="cell">Perusahaan</th>
                                <th class="cell text-center" style="width: 90px;">Status</th>
                                <th class="cell">Dibuat</th>
                                <th class="cell text-center" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($akun_list)) : ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> Tidak ada data akun yang ditemukan.
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php 
                                    $no = ($current_page - 1) * $per_page + 1; 
                                    foreach ($akun_list as $row) : 
                                ?>
                                    <tr>
                                        <td class="cell align-middle"><?= $no++ ?></td>
                                        <td class="cell align-middle">
                                            <code><?= esc($row['username']) ?></code>
                                        </td>
                                        <td class="cell align-middle fw-bold">
                                            <?= esc($row['nama']) ?>
                                        </td>
                                        <td class="cell align-middle text-center">
                                            <?php if ($row['tipe'] === 'sales') : ?>
                                                <span class="badge bg-success"><i class="fa-solid fa-user-tie me-1"></i>Sales</span>
                                            <?php else : ?>
                                                <span class="badge bg-primary"><i class="fa-solid fa-truck-field me-1"></i>Supplier</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell align-middle text-center">
                                            <?php if ($row['tipe'] === 'sales') : ?>
                                                <?php if (($row['tipe_akses'] ?? '') === 'eksternal') : ?>
                                                    <span class="badge" style="background-color: #ea580c; color: #fff;">Eksternal</span>
                                                <?php else : ?>
                                                    <span class="badge bg-success">Internal</span>
                                                <?php endif; ?>
                                            <?php else : ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell align-middle">
                                            <?= esc($row['perusahaan']) ?>
                                        </td>
                                        <td class="cell align-middle text-center">
                                            <?php if ($row['is_active'] === 1) : ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell align-middle small text-muted">
                                            <?= esc(!empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-') ?>
                                        </td>
                                        <td class="cell align-middle text-center">
                                            <div class="d-inline-flex gap-2">
                                                <a href="<?= base_url('admin/akun/edit/' . esc($row['tipe']) . '/' . esc($row['id'])) ?>" 
                                                    class="btn btn-sm btn-primary text-white" 
                                                    title="Edit Akun">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <form action="<?= base_url('admin/akun/toggle/' . esc($row['tipe']) . '/' . esc($row['id'])) ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin <?= $row['is_active'] === 1 ? 'menonaktifkan' : 'mengaktifkan' ?> akun <?= esc($row['username']) ?>?');">
                                                    <?= csrf_field(); ?>
                                                    <?php if ($row['is_active'] === 1) : ?>
                                                        <button type="submit" class="btn btn-sm btn-warning text-dark" title="Nonaktifkan Akun">
                                                            <i class="fa-solid fa-ban"></i> Nonaktifkan
                                                        </button>
                                                    <?php else : ?>
                                                        <button type="submit" class="btn btn-sm btn-success text-white" title="Aktifkan Akun">
                                                            <i class="fa-solid fa-check"></i> Aktifkan
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger text-white" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteModal<?= esc($row['tipe']) ?>_<?= esc($row['id']) ?>" 
                                                        title="Hapus Akun">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </button>
                                            </div>

                                            <!-- Modal Konfirmasi Hapus Akun -->
                                            <div class="modal fade" id="deleteModal<?= esc($row['tipe']) ?>_<?= esc($row['id']) ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= esc($row['tipe']) ?>_<?= esc($row['id']) ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content text-start">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-danger" id="deleteModalLabel<?= esc($row['tipe']) ?>_<?= esc($row['id']) ?>">
                                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>Konfirmasi Hapus Akun
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="mb-0 text-dark">
                                                                Hapus akun <strong><?= esc($row['nama']) ?></strong> (<code><?= esc($row['username']) ?></code>)? Tindakan ini tidak bisa dibatalkan.
                                                            </p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Batal</button>
                                                            <form action="<?= base_url('admin/akun/delete/' . esc($row['tipe']) . '/' . esc($row['id'])) ?>" method="POST" class="d-inline">
                                                                <?= csrf_field(); ?>
                                                                <input type="hidden" name="tipe" value="<?= esc($tipe_filter) ?>">
                                                                <input type="hidden" name="status" value="<?= esc($status_filter) ?>">
                                                                <input type="hidden" name="akses" value="<?= esc($akses_filter ?? 'all') ?>">
                                                                <input type="hidden" name="q" value="<?= esc($search_query) ?>">
                                                                <input type="hidden" name="page" value="<?= esc($current_page) ?>">
                                                                <button type="submit" class="btn btn-danger text-white">
                                                                    <i class="fa-solid fa-trash me-1"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div><!--//table-responsive-->
            </div><!--//app-card-body-->

            <?php if ($total_pages > 1) : ?>
                <div class="app-card-footer p-3 border-top d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Menampilkan <?= ($current_page - 1) * $per_page + 1 ?> - <?= min($current_page * $per_page, $total_rows) ?> dari <?= $total_rows ?> akun
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                                $buildUrl = function($pageNum) use ($tipe_filter, $status_filter, $akses_filter, $search_query) {
                                    $p = ['page' => $pageNum];
                                    if ($tipe_filter !== 'all') $p['tipe'] = $tipe_filter;
                                    if ($status_filter !== 'all') $p['status'] = $status_filter;
                                    if (!empty($akses_filter) && $akses_filter !== 'all') $p['akses'] = $akses_filter;
                                    if ($search_query !== '') $p['q'] = $search_query;
                                    return base_url('admin/akun/index') . '?' . http_build_query($p);
                                };
                            ?>
                            <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl($current_page - 1) ?>" aria-label="Sebelumnya">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>

                            <?php for ($p = 1; $p <= $total_pages; $p++) : ?>
                                <li class="page-item <?= $p === $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $buildUrl($p) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= $buildUrl($current_page + 1) ?>" aria-label="Selanjutnya">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div><!--//app-card-->
    </div><!--//container-xl-->
</div><!--//app-content-->

<?= $this->endSection(); ?>

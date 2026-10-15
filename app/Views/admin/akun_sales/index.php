<?= $this->extend('admin/template/template'); ?>
<?= $this->section('content'); ?>

<div class="app-content pt-3 p-md-3 p-lg-4">
    <div class="container-xl">
        <div class="row g-3 mb-4 align-items-center justify-content-between">
            <div class="col-auto">
                <h1 class="app-page-title mb-0">Manajemen Akun Sales</h1>
            </div>
            <div class="col-auto">
                <a href="<?= base_url('admin/akun_sales/tambah') ?>" class="btn btn-primary text-white">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Akun Sales
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

        <div class="app-card app-card-orders-table shadow-sm mb-5">
            <div class="app-card-body">
                <div class="table-responsive">
                    <table class="table app-table-hover mb-0 text-left">
                        <thead>
                            <tr>
                                <th class="cell" style="width: 50px;">No</th>
                                <th class="cell">Nama Lengkap</th>
                                <th class="cell">Username</th>
                                <th class="cell text-center">Status</th>
                                <th class="cell">Tanggal Dibuat</th>
                                <th class="cell text-center" style="width: 180px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($akun_sales)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-circle-info me-1"></i> Belum ada data akun sales.
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php $no = 1; foreach ($akun_sales as $row) : ?>
                                    <tr>
                                        <td class="cell align-middle"><?= $no++ ?></td>
                                        <td class="cell align-middle fw-bold"><?= esc($row['nama_sales']) ?></td>
                                        <td class="cell align-middle"><code><?= esc($row['username']) ?></code></td>
                                        <td class="cell align-middle text-center">
                                            <?php if ($row['status'] === 'aktif') : ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else : ?>
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell align-middle">
                                            <?= esc(!empty($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-') ?>
                                        </td>
                                        <td class="cell align-middle text-center">
                                            <div class="d-inline-flex gap-2">
                                                <a href="<?= base_url('admin/akun_sales/edit/' . esc($row['id_sales'])) ?>" 
                                                   class="btn btn-sm btn-primary text-white" 
                                                   title="Edit Akun">
                                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                                </a>
                                                <form action="<?= base_url('admin/akun_sales/delete/' . esc($row['id_sales'])) ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun sales <?= esc($row['username']) ?>? Tindakan ini tidak dapat dibatalkan.');">
                                                    <?= csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger text-white" title="Hapus Akun">
                                                        <i class="fa-solid fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div><!--//table-responsive-->
            </div><!--//app-card-body-->
        </div><!--//app-card-->
    </div><!--//container-xl-->
</div><!--//app-content-->

<?= $this->endSection(); ?>

<?= $this->extend('supplier/layout') ?>

<?= $this->section('content') ?>

<!-- Flash Alerts -->
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-check fs-5"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Validation Errors Box -->
<?php $validationErrors = session('errors'); ?>
<?php if (!empty($validationErrors) && is_array($validationErrors)) : ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Mohon perbaiki kesalahan berikut:</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($validationErrors as $err) : ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error') && empty($validationErrors)) : ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="fa-solid fa-circle-exclamation fs-5"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <h5 class="card-title fw-bold mb-0 text-dark">
                <i class="fa-solid fa-clock-rotate-left text-success me-2"></i>Catatan History Ekspor Perusahaan
            </h5>
            <span class="badge bg-light text-dark border">Total: <?= count($histories) ?> Catatan</span>
        </div>
        <div>
            <button type="button" class="btn btn-success btn-sm px-3 py-2 d-inline-flex align-items-center gap-2 rounded-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahHistory">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah History</span>
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Tahun</th>
                        <th>Produk</th>
                        <th>Negara Tujuan</th>
                        <th>Jenis Ekspor</th>
                        <th>Kuantitas</th>
                        <th>Nilai Ekspor (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($histories)) : ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-plane-departure fs-2 mb-2 d-block text-secondary opacity-50"></i>
                                Belum ada catatan history ekspor untuk perusahaan Anda.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php $no = 1; foreach ($histories as $row) : ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="fw-bold"><?= esc($row['tahun']) ?></td>
                                <td><?= esc($row['produk'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= esc($row['negara_tujuan'] ?? '-') ?></span></td>
                                <td><?= esc($row['jenis_ekspor'] ?? '-') ?></td>
                                <td><?= esc($row['kuantitas'] ?? '-') ?></td>
                                <td class="fw-semibold text-success"><?= !empty($row['nilai_ekspor_usd']) ? '$ ' . number_format($row['nilai_ekspor_usd'], 2) : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah History Ekspor -->
<div class="modal fade" id="modalTambahHistory" tabindex="-1" aria-labelledby="modalTambahHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="<?= base_url('supplier/history/tambah') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahHistoryLabel">
                        <i class="fa-solid fa-circle-plus text-success me-2"></i>Tambah History Ekspor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="tambah_tahun" class="form-label fw-semibold">Tahun Ekspor <span class="text-danger">*</span></label>
                        <input type="number" 
                               name="tahun" 
                               id="tambah_tahun" 
                               class="form-control" 
                               min="1900" 
                               max="2099" 
                               value="<?= old('tahun', date('Y')) ?>" 
                               placeholder="Contoh: 2024" 
                               required>
                        <small class="text-muted">Format 4 digit angka.</small>
                    </div>

                    <div class="mb-3">
                        <label for="tambah_produk" class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="produk" 
                               id="tambah_produk" 
                               class="form-control" 
                               value="<?= old('produk') ?>" 
                               placeholder="Nama komoditas / produk yang diekspor" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="tambah_negara_tujuan" class="form-label fw-semibold">Negara Tujuan <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="negara_tujuan" 
                               id="tambah_negara_tujuan" 
                               class="form-control" 
                               value="<?= old('negara_tujuan') ?>" 
                               placeholder="Contoh: Malaysia, Singapura, Jepang" 
                               required>
                        <small class="text-muted">Boleh lebih dari satu negara tujuan, dipisahkan tanda koma.</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="tambah_jenis_ekspor" class="form-label fw-semibold">Jenis Ekspor</label>
                            <input type="text" 
                                   name="jenis_ekspor" 
                                   id="tambah_jenis_ekspor" 
                                   class="form-control" 
                                   value="<?= old('jenis_ekspor') ?>" 
                                   placeholder="Contoh: Langsung, Maklon">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tambah_kuantitas" class="form-label fw-semibold">Kuantitas</label>
                            <input type="text" 
                                   name="kuantitas" 
                                   id="tambah_kuantitas" 
                                   class="form-control" 
                                   value="<?= old('kuantitas') ?>" 
                                   placeholder="Contoh: 800 pcs / 20 MT">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tambah_nilai_ekspor_usd" class="form-label fw-semibold">Nilai Ekspor (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-success">$</span>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   name="nilai_ekspor_usd" 
                                   id="tambah_nilai_ekspor_usd" 
                                   class="form-control" 
                                   value="<?= old('nilai_ekspor_usd') ?>" 
                                   placeholder="Contoh: 25000.00">
                        </div>
                        <small class="text-muted">Angka &ge; 0 (opsional, kosongkan jika tidak ada).</small>
                    </div>

                    <div class="mb-2">
                        <label for="tambah_catatan" class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" 
                                  id="tambah_catatan" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Catatan tambahan (opsional)"><?= old('catatan') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan History
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (!empty(session('errors'))) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalTambahEl = document.getElementById('modalTambahHistory');
    if (modalTambahEl) {
        var modal = new bootstrap.Modal(modalTambahEl);
        modal.show();
    }
});
</script>
<?php endif; ?>

<?= $this->endSection() ?>

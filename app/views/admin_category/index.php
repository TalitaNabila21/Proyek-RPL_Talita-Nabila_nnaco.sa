<!-- Header Section -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">MANAJEMEN KATEGORI</h6>
        <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2 mb-0">
            <span class="icon-pod icon-pod-md icon-pod-amber shadow-xs"><i class="bi bi-tags-fill"></i></span>
            <span>Kategori Produk</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola klasifikasi etalase perhiasan, aksesoris handmade, dan pengelompokan produk.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/AdminCategoryController/create" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-circle-fill fs-5"></i>
            <span class="fw-semibold">Tambah Kategori Baru</span>
        </a>
    </div>
</div>

<!-- Categories Table Card -->
<div class="card shadow-sm rounded-4 border overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-xs icon-pod-amber"><i class="bi bi-table"></i></span>
            <span>Daftar Klasifikasi Kategori</span>
        </h5>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-1">
            Total <?= count($data['data']['categories']); ?> Kategori
        </span>
    </div>

    <div class="card-body p-0">
        <?php if(empty($data['data']['categories'])): ?>
            <div class="text-center py-5">
                <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-amber mb-3 shadow-xs">
                    <i class="bi bi-tags fs-1"></i>
                </div>
                <h5 class="fw-bold">Belum ada kategori produk</h5>
                <p class="text-muted small mb-3">Buat kategori pertama untuk mulai mengelompokkan produk Anda.</p>
                <a href="<?= BASEURL; ?>/AdminCategoryController/create" class="btn btn-sm btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4" style="width: 60px;">#</th>
                            <th>Nama Kategori</th>
                            <th>Slug URL</th>
                            <th>Status Etalase</th>
                            <th class="text-end pe-4" style="min-width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($data['data']['categories'] as $cat) : ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= $i++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="icon-pod icon-pod-xs icon-pod-amber shadow-xs"><i class="bi bi-tag-fill"></i></span>
                                        <span class="fw-bold text-cute-dark"><?= htmlspecialchars($cat['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <code class="px-2 py-1 rounded bg-light border text-muted" style="font-size: 0.8rem;"><?= htmlspecialchars($cat['slug']); ?></code>
                                </td>
                                <td>
                                    <?php if($cat['status'] == 'active'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1 shadow-xs">
                                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3 py-1">
                                            <i class="bi bi-x-circle me-1"></i> Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a href="<?= BASEURL; ?>/AdminCategoryController/edit/<?= $cat['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Edit Kategori">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#deleteCatModal<?= $cat['id']; ?>" title="Hapus Kategori">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteCatModal<?= $cat['id']; ?>" tabindex="-1" aria-labelledby="deleteCatLabel<?= $cat['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title d-flex align-items-center gap-2" id="deleteCatLabel<?= $cat['id']; ?>">
                                                        <span class="icon-pod icon-pod-xs icon-pod-rose"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                                        <span>Konfirmasi Hapus Kategori</span>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-4">
                                                    <p class="mb-2">Apakah Anda yakin ingin menghapus kategori <strong><?= htmlspecialchars($cat['name']); ?></strong>?</p>
                                                    <small class="text-danger d-block"><i class="bi bi-info-circle me-1"></i> Menghapus kategori dapat mempengaruhi produk yang saat ini berada di bawah kategori ini.</small>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <form action="<?= BASEURL; ?>/AdminCategoryController/delete/<?= $cat['id']; ?>" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?= $data['data']['csrf_token']; ?>">
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">Ya, Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

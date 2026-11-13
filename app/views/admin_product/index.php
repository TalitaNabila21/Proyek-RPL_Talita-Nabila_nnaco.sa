<!-- Header Section -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-3 mb-3 border-bottom">
    <div>
        <h6 class="card-category text-muted mb-1">MANAJEMEN KATALOG</h6>
        <h1 class="h2 fw-bold text-cute-dark d-flex align-items-center gap-2 mb-0">
            <span class="icon-pod icon-pod-md icon-pod-pink shadow-xs"><i class="bi bi-bag-fill"></i></span>
            <span>Daftar Produk</span>
        </h1>
        <p class="text-muted small mb-0 mt-1">Kelola inventaris perhiasan, aksesoris handmade, harga, dan ketersediaan stok toko.</p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
        <a href="<?= BASEURL; ?>/AdminProductController/create" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-circle-fill fs-5"></i>
            <span class="fw-semibold">Tambah Produk Baru</span>
        </a>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card shadow-sm rounded-4 mb-4 border">
    <div class="card-body p-3">
        <form class="row g-2 align-items-center" action="<?= BASEURL; ?>/AdminProductController" method="GET">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama produk aksesoris..." value="<?= htmlspecialchars($data['data']['search'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 w-100 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-funnel"></i> Cari
                </button>
                <?php if(!empty($data['data']['search'])): ?>
                    <a href="<?= BASEURL; ?>/AdminProductController" class="btn btn-sm btn-outline-secondary rounded-pill px-2 d-inline-flex align-items-center justify-content-center" title="Reset Pencarian">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Products Table Card -->
<div class="card shadow-sm rounded-4 border overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <span class="icon-pod icon-pod-xs icon-pod-pink"><i class="bi bi-table"></i></span>
            <span>Tabel Katalog Produk</span>
        </h5>
        <span class="badge rounded-pill bg-light text-dark border px-3 py-1">
            Total <?= count($data['data']['products']); ?> Item
        </span>
    </div>

    <div class="card-body p-0">
        <?php if(empty($data['data']['products'])): ?>
            <div class="text-center py-5">
                <div class="icon-pod icon-pod-xl icon-pod-circle icon-pod-pink mb-3 shadow-xs">
                    <i class="bi bi-bag-x fs-1"></i>
                </div>
                <h5 class="fw-bold">Tidak ada produk ditemukan</h5>
                <p class="text-muted small mb-3">Belum ada produk yang sesuai dengan filter atau kata kunci pencarian Anda.</p>
                <a href="<?= BASEURL; ?>/AdminProductController" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th style="width: 80px;">Foto</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga Satuan</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th class="text-end pe-4" style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($data['data']['products'] as $prod) : ?>
                            <tr>
                                <td class="ps-4 fw-bold text-muted"><?= $i++; ?></td>
                                <td>
                                    <?php if($prod['image']) : ?>
                                        <img src="<?= BASEURL; ?>/assets/images/products/<?= $prod['image']; ?>" width="52" height="52" class="object-fit-cover rounded-3 border shadow-xs">
                                    <?php else : ?>
                                        <div class="icon-pod icon-pod-md icon-pod-pink rounded-3 text-muted">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-bold text-cute-dark"><?= htmlspecialchars($prod['name']); ?></div>
                                    <small class="text-muted" style="font-size: 0.76rem;">ID #PRD-<?= $prod['id']; ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill border px-2 py-1">
                                        <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($prod['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary" style="font-size: 0.95rem;">Rp <?= number_format($prod['price'], 0, ',', '.'); ?></div>
                                </td>
                                <td>
                                    <?php if($prod['stock'] > 10): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 fw-semibold">
                                            <?= $prod['stock']; ?> pcs
                                        </span>
                                    <?php elseif($prod['stock'] > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-1 fw-semibold">
                                            <?= $prod['stock']; ?> pcs (Menipis)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 fw-semibold">
                                            Habis (0)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($prod['status'] == 'active'): ?>
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
                                        <a href="<?= BASEURL; ?>/AdminProductController/edit/<?= $prod['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Edit Produk">
                                            <i class="bi bi-pencil-square"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $prod['id']; ?>" title="Hapus Produk">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?= $prod['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $prod['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h5 class="modal-title d-flex align-items-center gap-2" id="deleteModalLabel<?= $prod['id']; ?>">
                                                        <span class="icon-pod icon-pod-xs icon-pod-rose"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                                        <span>Konfirmasi Hapus Produk</span>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-4">
                                                    <p class="mb-2">Apakah Anda yakin ingin menghapus produk ini dari katalog?</p>
                                                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                                                        <?php if($prod['image']) : ?>
                                                            <img src="<?= BASEURL; ?>/assets/images/products/<?= $prod['image']; ?>" width="45" height="45" class="rounded object-fit-cover">
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong class="d-block text-cute-dark"><?= htmlspecialchars($prod['name']); ?></strong>
                                                            <small class="text-muted">Kategori: <?= htmlspecialchars($prod['category_name']); ?> | Rp <?= number_format($prod['price'], 0, ',', '.'); ?></small>
                                                        </div>
                                                    </div>
                                                    <small class="text-danger mt-2 d-block"><i class="bi bi-info-circle me-1"></i> Data produk yang sudah memiliki transaksi akan diamankan oleh integritas database.</small>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                                                    <form action="<?= BASEURL; ?>/AdminProductController/delete/<?= $prod['id']; ?>" method="POST" class="d-inline">
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

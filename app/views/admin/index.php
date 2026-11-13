<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="card-category text-muted mb-1">OVERVIEW & STATISTIK</h6>
                        <h3 class="fw-bold mb-0">Dashboard Admin</h3>
                        <p class="text-muted small mb-0">Selamat datang kembali, <strong><?= htmlspecialchars($data['data']['user_name']); ?></strong>.</p>
                    </div>
                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-semibold">
                        <i class="bi bi-shield-check text-success me-1"></i> Administrator Access
                    </span>
                </div>
                
                <hr class="opacity-10 my-3">
                
                <div class="row g-3">
                    <div class="col-md-6 col-lg-5 mb-2">
                        <div class="card card-stat-gradient-pink text-white shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="card-category text-white-50 text-uppercase mb-1" style="font-size: 0.72rem;">Total Pengguna</h6>
                                    <h2 class="fw-bold mb-1"><?= $data['data']['total_users'] ?? 0; ?></h2>
                                    <small class="text-white-50" style="font-size: 0.8rem;">
                                        Customer: <?= $data['data']['user_stats']['by_role']['customer'] ?? 0; ?> | 
                                        Admin: <?= $data['data']['user_stats']['by_role']['admin'] ?? 0; ?> | 
                                        Operator: <?= $data['data']['user_stats']['by_role']['operator'] ?? 0; ?> | 
                                        Owner: <?= $data['data']['user_stats']['by_role']['owner'] ?? 0; ?>
                                    </small>
                                    <div class="mt-2 pt-2 border-top border-white border-opacity-25">
                                        <a href="<?= BASEURL; ?>/AdminUserController" class="text-white text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1">
                                            <span>Kelola Data Pengguna</span> <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="icon-pod icon-pod-lg icon-pod-circle bg-white bg-opacity-25 text-white shadow-sm">
                                    <i class="bi bi-people-fill fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5 mb-2">
                        <div class="card card-stat-gradient-orange text-white shadow-sm rounded-4 overflow-hidden card-hover-lift" style="border: none !important;">
                            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="card-category text-white-50 text-uppercase mb-1" style="font-size: 0.72rem;">Pending Order</h6>
                                    <h2 class="fw-bold mb-1"><?= $data['data']['pending_orders'] ?? 0; ?></h2>
                                    <small class="text-white-50" style="font-size: 0.8rem;">Pesanan yang menunggu diproses</small>
                                    <div class="mt-2 pt-2 border-top border-white border-opacity-25">
                                        <a href="<?= BASEURL; ?>/AdminOrderController?status=pending" class="text-white text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1">
                                            <span>Lihat Pesanan Masuk</span> <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="icon-pod icon-pod-lg icon-pod-circle bg-white bg-opacity-25 text-white shadow-sm">
                                    <i class="bi bi-clock-history fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h5 class="fw-bold mb-3 text-secondary d-flex align-items-center gap-2">
                        <span class="icon-pod icon-pod-xs icon-pod-blue"><i class="bi bi-grid"></i></span>
                        <span>Menu Cepat Admin</span>
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= BASEURL; ?>/AdminUserController" class="btn btn-outline-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs">
                            <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-people-fill"></i></span>
                            <span class="fw-semibold">Manajemen Pengguna</span>
                        </a>
                        <a href="<?= BASEURL; ?>/AdminProductController" class="btn btn-outline-danger rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs">
                            <span class="icon-pod icon-pod-xs icon-pod-pink"><i class="bi bi-bag-fill"></i></span>
                            <span class="fw-semibold">Manajemen Produk</span>
                        </a>
                        <a href="<?= BASEURL; ?>/AdminCategoryController" class="btn btn-outline-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs">
                            <span class="icon-pod icon-pod-xs icon-pod-blue"><i class="bi bi-tags-fill"></i></span>
                            <span class="fw-semibold">Kategori Produk</span>
                        </a>
                        <a href="<?= BASEURL; ?>/AdminOrderController" class="btn btn-outline-dark rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs">
                            <span class="icon-pod icon-pod-xs icon-pod-indigo"><i class="bi bi-receipt"></i></span>
                            <span class="fw-semibold">Semua Pesanan</span>
                        </a>
                        <a href="<?= BASEURL; ?>/adminSettings" class="btn btn-outline-success rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs">
                            <span class="icon-pod icon-pod-xs icon-pod-emerald"><i class="bi bi-sliders2-vertical"></i></span>
                            <span class="fw-semibold">Pengaturan Sistem</span>
                        </a>
                        <a href="<?= BASEURL; ?>/settings" class="btn btn-outline-secondary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs">
                            <span class="icon-pod icon-pod-xs icon-pod-purple"><i class="bi bi-palette-fill"></i></span>
                            <span class="fw-semibold">Pengaturan Tema</span>
                        </a>
                        <a href="<?= BASEURL; ?>/auth/logout" class="btn btn-danger rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="fw-semibold">Logout (Keluar)</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
